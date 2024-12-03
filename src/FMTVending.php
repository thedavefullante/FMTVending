<?php

namespace Etap\FmtVending;

use Etap\FmtVending\lib\PhpSerial;

class FMTVending
{
    protected $serial;
    protected $config;
    protected $command;
    protected $constant;

    /**
     * Constructor for the FMTVending class.
     *
     * Initializes the FMTVending object and sets up necessary configurations.
     * Add any specific setup details or actions here.
     */
    public function __construct()
    {
        $this->config = include "./config/app.php";
        $this->command = include "./config/command.php";
        $this->constant = include "./config/constant.php";
    }

    /**
     * Initializes the serial device for communication.
     *
     * This method configures the serial device (e.g., /dev/ttyS0) with the necessary
     * settings including baud rate, parity, character length, stop bits, and flow control.
     * It then attempts to open the device in write mode ('w+'). Any errors encountered
     * during the initialization process are caught and logged.
     *
     * @throws \Exception If the serial device cannot be set or opened.
     */
    protected function initialize()
    {
        try {
            $this->serial = new PhpSerial();

            // First we must specify the device. This works on both linux and windows (if
            // your linux serial device is /dev/ttyS0 for COM1, etc)
            $this->serial->deviceSet($this->config['fmt']['device']);

            // We can change the baud rate, parity, length, stop bits, flow control
            $this->serial->confBaudRate($this->config['fmt']['baudrate']);
            $this->serial->confParity($this->config['fmt']['parity']);
            $this->serial->confCharacterLength($this->config['fmt']['character_length']);
            $this->serial->confStopBits($this->config['fmt']['stop_bits']);
            $this->serial->confFlowControl($this->config['fmt']['flow_control']);

            // Then we need to open it
            $this->serial->deviceOpen('w+');
        } catch (\Exception $err) {
            $this->log($err);
        }
    }

    /**
     * Closes the serial device connection.
     *
     * This method attempts to close the open serial device. If the operation is
     * successful, a success message is logged. If the device is closed successfully,
     * a message indicating that the device is closed will be logged.
     *
     * @return void
     */
    protected function close()
    {
        try {
            if ($this->serial->deviceClose()) {
                $this->log("Device is closed");
                return;
            }

            $this->log("Device is not closed");
        } catch (\Exception $err) {
            $this->log($err);
        }
    }

    /**
     * Sends a command request to the serial device.
     *
     * This method takes a hexadecimal command string, removes any spaces, and then
     * converts it into binary format using the `pack()` function. The resulting
     * binary message is then sent to the serial device using the `sendMessage()` method.
     *
     * @param string $cmd The hexadecimal command to be sent to the device.
     *                    Defaults to an empty string if no command is provided.
     *
     * @return void
     */
    protected function sendRequest($cmd = '')
    {
        try {
            $this->initialize();

            $message = preg_replace('/ /', "", $cmd);
            $message = pack("H*", $message);
            $this->serial->sendMessage($message);

            if (empty($this->serial->readPort())) {
                $log = "No data recieved";
                $this->log($log);

                return [
                    'status' => false,
                    'error' => $log
                ];
            }

            $log = "Data has been recieved.";
            $this->log($log);
            $hex = (string) join("", unpack("H*", $this->serial->readPort()));
            $this->log($hex);

            $this->close();

            return [
                'status' => true,
                'data' => [
                    'log' => $log,
                    'hex' => $hex
                ]
            ];
        } catch (\Exception $err) {
            error_log($err->getMessage());
        }
    }

    /**
     * Dispenses an item based on the given row number.
     *
     * This method processes the dispensing request. If the provided row number is invalid
     * (i.e., it equals 0), it logs an error message and returns `false`. Otherwise, it proceeds
     * with the dispensing process. The dispensing action is assumed to be based on the row number
     * passed as an argument (further implementation details would depend on the system).
     *
     * @param int $row_num The row number representing the item to be dispensed. Defaults to 0.
     *
     * @return bool Returns `false` if the row number is invalid, otherwise proceeds with dispensing.
     */
    public function dispense($row_num = 0)
    {
        try {
            if ($row_num === 0) {
                $this->log("Invalid row number");
                return false;
            }

            $res = $this->sendRequest($this->command['dispense']['row_'.$row_num]);
            return $res;
        } catch (\Exception $err) {
            $this->log($err->getMessage());
        }
    }

    /**
     * Logs a message with a timestamp to a log file.
     *
     * This method accepts a message (either as a string or an array) and appends it to
     * a log file with a timestamp. The log file is named with the current date and
     * saved in the root directory of the project. If the message is an array, it will
     * be converted to a string by joining the array elements with a space. The message is
     * written to the log file in append mode, and the file is locked during writing to prevent
     * concurrent access issues.
     *
     * @param string|array $msg The message or array of messages to log.
     *
     * @return void
     */
    public function log($msg)
    {
        $msg = (is_array($msg)) ? implode(' ', $msg) : $msg;
        $message = date('Y-m-d H:i:s') . ': ' . $msg;
        $rootDir = $_SERVER['DOCUMENT_ROOT'];
        $logFilePath = $rootDir . '/fmt-vending-' . date('Y-m-d') . '.log';
        file_put_contents($logFilePath, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
