<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Services;

use Exception;
use RuntimeException;
use InvalidArgumentException;
use Yuhzel\Xaseco\Services\Log;

/**
 * Class SocketConnection
 *
 * Handles socket communication with features such as blocking mode, stream timeout, and data transmission.
 *
 * @package Yuhzel\Xaseco\Services
 * @author Yuhzel
 */
class SocketConnection
{
    public function __construct(
        public string $hostname = '127.0.0.1',
        public int $port = 5009,
        public float $timeout = 180.0,
        public $socket = null,
        private int $errorCode = 0,
        private string $errorMessage = ''
    ) {
        $this->socket = stream_socket_client(
            "tcp://{$hostname}:{$port}",
            $this->errorCode,
            $this->errorMessage,
            $timeout
        );

        if (!$this->socket) {
            $this->logError("SocketConnection failed");
            Basic::console("SocketConnection failed: {$this->errorMessage} ({$this->errorCode})");
            throw new Exception("SocketConnection failed: {$this->errorMessage} ({$this->errorCode})");
        }
    }

    /**
     * Sets the blocking mode of the socket.
     *
     * @param bool $blocking True to set the socket to blocking mode, false for non-blocking mode.
     *
     * @throws RuntimeException if setting the blocking mode fails.
     */
    public function setBlockingMode(bool $blocking): void
    {
        if (stream_set_blocking($this->socket, $blocking) === false) {
            $this->logError("Failed to set blocking mode");
            throw new RuntimeException("Failed to set blocking mode");
        }
    }

    /**
     * Sets the timeout for the socket stream.
     *
     * @param float $timeout The timeout in seconds. Must be a non-negative number.
     *
     * @throws InvalidArgumentException if the timeout is negative.
     * @throws RuntimeException if setting the timeout fails.
     */
    public function setStreamTimeout(float $timeout): void
    {
        // Validate that the timeout is non-negative
        if ($timeout < 0) {
            throw new InvalidArgumentException("Timeout must be a non-negative number");
        }

        $seconds = (int) $timeout;
        $microseconds = (int) round(($timeout - $seconds) * 1000000);

        if (!stream_set_timeout($this->socket, $seconds, $microseconds)) {
            throw new RuntimeException("Failed to set stream timeout to {$timeout} seconds");
        }
    }

    /**
     * Writes data to the socket.
     *
     * @param string $data The data to write to the socket.
     *
     * @throws Exception if writing to the socket fails.
     */
    public function fwrite(string $data): int
    {
        $writtenBytes = 0;
        $totalBytes = strlen($data);

        while ($writtenBytes < $totalBytes) {
            $result = @fwrite($this->socket, substr($data, $writtenBytes));
            if ($result === false) {
                $this->logError("Failed to write to socket");
                throw new RuntimeException("Failed to write to socket");
            }
            $writtenBytes += $result;
        }

        return $writtenBytes;
    }

    /**
     * Reads a line from the socket.
     *
     * @param int $length The maximum number of bytes to read. Defaults to 1024.
     *
     * @return string|false The line read from the socket, or false on failure.
     *
     * @throws Exception if reading from the socket fails.
     */
    public function fgets(int $length = 1024): string|false
    {
        $data = fgets($this->socket, $length);

        if ($data === false) {
            // Log the error if reading fails
            Log::error("Failed to read line from socket");
            throw new Exception("Failed to read line from socket");
        }

        return $data;
    }

    /**
     * Reads data from the socket.
     *
     * @param int $length The maximum number of bytes to read. Defaults to 1024.
     *
     * @return string|false The data read from the socket, or false on failure.
     *
     * @throws Exception if reading from the socket fails.
     */
    public function fread(int $length = 1024): string|false
    {
        $response = @fread($this->socket, $length);
        if ($response === false || $response === '') {
            $this->logError("Failed to read from socket");
            throw new RuntimeException("Failed to read from socket");
        }
        return $response;
    }

    /**
     * Checks if the socket is still connected.
     */
    public function isConnected(): bool
    {
        if (!$this->socket) {
            return false;
        }

        // Check stream meta-data to verify if the stream is still alive
        $meta = stream_get_meta_data($this->socket);
        if ($meta['eof']) {
            return false;
        }

        // Use stream_select to check for connection status without reading or writing
        $read = [$this->socket];
        $write = $except = null;

        $status = stream_select($read, $write, $except, 0, 500000); // Non-blocking check
        return $status !== false;
    }

    /**
     * Closes the socket connection.
     */
    public function close(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Sends a simple ping command to the socket and checks for a response.
     *
     * @return bool True if the ping was successful, false otherwise.
     *
     * @throws Exception if sending the ping or receiving a response fails.
     */
    public function ping(): bool
    {
        try {
            $this->fwrite("PING\r\n");
            $response = $this->fread(1024);
            return strpos($response, "PONG") !== false;
        } catch (Exception $e) {
            $this->logError("Ping failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Destructor to ensure the socket is closed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Logs an error message.
     */
    private function logError(string $message): void
    {
        $fullMessage = "{$message}. Error: {$this->errorMessage} ({$this->errorCode})";
        Log::error($fullMessage);
    }
}
