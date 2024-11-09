<?php

declare(strict_types=1);

/*
 IXR - The Incutio XML-RPC Library
    Original Version: 1.61 - Simon Willison, 11th July 2003 (htmlentities -> htmlspecialchars)
    Site:   http://scripts.incutio.com/xmlrpc/
    Manual: http://scripts.incutio.com/xmlrpc/manual.php
    Errors: http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php
    License: Artistic License (http://www.opensource.org/licenses/artistic-license.php)

    Modifications and Updates:
    - Modified for protocol support: 'GbxRemote 2' and 'GbxRemote 1'
    - Determined correct protocol version to use

    Release History:
    - 2007-09-22: Slig
    - 2008-01-20: Slig / Xymph / Assembler Maniac
    - 2008-02-05: Slig
    - 2008-05-20: Xymph
    - 2009-04-08: Gou1
    - 2009-06-03: Xymph
    - 2011-04-09: Xymph / La beuze
    - 2011-05-22: Xymph
    - 2011-12-04: Xymph
    - 2013-02-18: Xymph
    - 2024-09-19: Yuhzel
        - Upgraded to PHP 8.3 and modern standards (https://www.php.net/releases/8.3/en.php)
        - Utilized Constructor Property Promotion (https://stitcher.io/blog/constructor-promotion-in-php-8)
        - Implemented League Container with Autowiring (https://container.thephpleague.com/4.x/)
        - Removed IXR_Base64, IXR_Date, IXR_Request, IXR_Value
        - Replaced by XmlRpcService and XmlArrayObject
        - Introduced SocketConnection class with TCP support and custom functions

    Note: Much of the code is still based on Xymph's version. Thanks for the reference.
*/

namespace Yuhzel\X8seco\Core\Gbx;

use RuntimeException;
use Exception;

use Yuhzel\X8seco\Core\Gbx\ErrorHandlingTrait;
use Yuhzel\X8seco\Core\Xml\{XmlArrayObject, XmlRpcService};
use Yuhzel\X8seco\Services\{SocketConnection, Basic, Log};

/**
 * IXR_Client_Gbx class for interacting with GBX remote services.
 *
 * This class manages the connection, communication, and data exchange with GBX
 * remote services using the XML-RPC protocol. It supports different protocol versions
 * and handles request and response operations, including error management.
 *
 * @package Yuhzel\X8seco\Core\Gbx
 * @license MIT
 *
 */
class GbxClient
{
    use ErrorHandlingTrait;
    /**
     * Maximum size for an XML-RPC request, in bytes.
     *
     * This limit helps prevent excessive memory usage or potential denial-of-service attacks
     * by restricting the size of requests that can be processed.
     */
    private const MAX_REQUEST_SIZE = 512 * 1024 - 8;
    /**
     * Maximum size for an XML-RPC response, in bytes.
     *
     * This limit ensures that the client can handle the response within reasonable
     * memory constraints and time limits.
     */
    private const MAX_RESPONSE_SIZE = 4096 * 1024;
    /**
     * Timeout duration for socket operations, in seconds.
     *
     * This timeout value is used to determine how long the client will wait for
     * socket operations to complete before considering them as failed.
     */
    private const TIMEOUT = 20.0;

    public int $reqHandle = 0x80000000;
    public int $protocol = 0;
    public bool $multi = false;
    public string $methodName = '';
    public array $calls = [];

    /**
     * Constructor for IXR_Client_Gbx.
     *
     * @param SocketConnection $socketConnection The socket connection for communication.
     * @param XmlRpcService $xmlRpcService The parser for XML-RPC data.
     */
    public function __construct(
        private SocketConnection $socketConnection,
        private XmlRpcService $xmlRpcService,
        private IxrError $ixrError,
    ) {
        $this->endian();
    }

    /**
     * Retrieves the error code from the IxrError instance.
     *
     * @return int The error code.
     */
    public function getErrorCode(): int
    {
        return $this->ixrError->getCode();
    }

    public function addCall(string $methodName, array $args): int
    {
        $this->methodName = $methodName;
        $this->calls = [
            'params' => $args
        ];

        return count($this->calls) - 1;
    }

    //TODO (yuhzel) pain
    public function multiQuery()
    {
        $this->multi = true;
        $result = $this->query($this->methodName, $this->calls);
        if ($result instanceof XmlArrayObject) {
            dd($result, 'todo');
        } else {
            dd('even more todo');
        }
    }

    public function readCB(int $timeout = 2000): bool
    {
        if (!$this->socketConnection->socket) {
            throw new RuntimeException("transport error - client not initialized");
        }

        $somethingReceived = false;
        $contents = '';

        $this->socketConnection->setStreamTimeout($timeout / 1000);  // Convert to seconds

        $read = [$this->socketConnection->socket];
        $write = $except = null;
        $nb = @stream_select($read, $write, $except, 0, $timeout);
        if ($nb !== false) {
            $nb = count($read);
        }

        while ($nb !== false && $nb > 0) {
            $timeout = 0;

            // Read and unpack size and handle from the socket
            $contents = $this->readContents(8);
            if (!$contents || strlen($contents) < 8) {
                throw new RuntimeException("transport error - cannot read size/handle");
            }

            $size = unpack('Nsize/Nhandle', $contents)['size'];
            $recvHandle = unpack('Nsize/Nhandle', $contents)['handle'];

            if ($recvHandle == 0 || $size == 0) {
                throw new RuntimeException("transport error - connection interrupted");
            }
            if ($size > 4096 * 1024) {
                throw new RuntimeException("transport error - response too large");
            }

            $contents = $this->readContents($size);
            if (!$contents || strlen($contents) < $size) {
                throw new RuntimeException("transport error - failed to read full response");
            }

            if (($recvHandle & 0x80000000) == 0) {
                $somethingReceived = true;
            }

            $read = [$this->socketConnection->socket];
            $nb = @stream_select($read, $write, $except, 0, $timeout);
        }

        return $somethingReceived;
    }

    /**
     * Retrieves the error message from the IXR_Error instance.
     *
     * @return string The error message.
     */
    public function getErrorMessage(): string
    {
        return $this->ixrError->faultString ?? '';
    }

    /**
     * Initializes the connection by reading the protocol header and setting the protocol version.
     *
     * @return bool True if initialization is successful, false otherwise.
     */
    public function init(): bool
    {
        $header = $this->socketConnection->fread(4);
        $size = $this->protocol == 2
            ? unpack('Vsize', $header)['size']
            : $this->bigEndianUnpack('Vsize', $header)['size'];

        if ($size > 64) {
            $this->throwError(-32300, 'Transport error - wrong low-level protocol header');
        }

        $handshake = $this->socketConnection->fread($size);

        if ($handshake === 'GBXRemote 1') {
            $this->protocol = 1;
        } elseif ($handshake === 'GBXRemote 2') {
            $this->protocol = 2;
        } else {
            $this->throwError(-32300, 'Transport error - wrong low-level protocol header');
        }
        return true;
    }

    /**
     * Terminates the connection by closing the socket.
     *
     * @return void
     */
    public function terminate(): void
    {
        $this->socketConnection->close();
    }

    /**
     * Sends a query request to the GBX remote service and retrieves the result.
     *
     *
<<<<<<< HEAD
     * @return mixed parsed values
     */
    public function query(string $method, mixed ...$params): mixed
=======
     * @return XmlArrayObject parsed values
     */
    public function query(string $method, mixed ...$params): XmlArrayObject
>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8
    {
        // generate xml request string for params
        if ($this->multi) {
            $xmlString = $this->xmlRpcService->createMultiRequest($method, $params);
        } else {
            $xmlString = $this->xmlRpcService->createRequest($method, $params);
<<<<<<< HEAD
            // DEBUG dump([$method => [
            //     $xmlString,
            //     $params
            // ]]);
=======
>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8
        }
        if (($size = strlen($xmlString)) > self::MAX_REQUEST_SIZE) {
            $this->throwError(-32300, "Transport error - request too large");
        }

        if (!$this->sendRequest($xmlString)) {
            $this->throwError(-32300, "Transport error - connection interrupted");
        }

        $result = $this->getResult();

        if ($result->array_key_exists('parsed')) {
            return $result['parsed'];
        } elseif ($result->array_key_exists('faultString')) {
            $this->throwError($result->faultCode, "Fault string: {$result->faultString} from {$result->methodName}");
        }
        return $result['result'];
    }

    /**
     * Sends a request to the GBX remote service.
     *
     * @param string $xmlRPC The request to be sent.
     * @return bool True if the request is sent successfully, false otherwise.
     */
    protected function sendRequest(string $xmlRPC): bool
    {
        $this->socketConnection->setStreamTimeout(self::TIMEOUT);

        $this->reqHandle++;
        $bytes = $this->protocol == 2
            ? pack('VVa*', strlen($xmlRPC), $this->reqHandle, $xmlRPC)
            : pack('Va*', strlen($xmlRPC), $xmlRPC);

        return $this->socketConnection->fwrite($bytes) !== 0;
    }

    /**
     * Retrieves the result from the GBX remote service.
     *
     * @return XmlArrayObject The parsed response from the GBX remote service.
     */
    protected function getResult(): XmlArrayObject
    {
        $contents = '';

        do {
            $size = 0;
            $recvHandle = 0;
            $this->socketConnection->setStreamTimeout(self::TIMEOUT);

            if ($this->protocol === 1) {
                $contents = $this->socketConnection->fread(4);
                if (strlen($contents) === 0) {
                    $this->throwError(-32300, 'Transport error - cannot read size');
                }
                $size = $this->bigEndianUnpack('Vsize', $contents)['size'];
                $recvHandle = $this->reqHandle;
            } elseif ($this->protocol === 2) {
                $contents = $this->socketConnection->fread(8);
                if (strlen($contents) === 0) {
                    $this->throwError(-32300, 'Transport error - cannot read size/handle');
                }
                $result = unpack('Vsize/Vhandle', $contents);
                $size = $result['size'];
                $recvHandle = $this->convertHandle($result['handle']);
            }

            if ($recvHandle === 0 || $size === 0) {
                $this->throwError(-32300, 'Transport error - connection interrupted');
            }

            if ($size > self::MAX_RESPONSE_SIZE) {
                $this->throwError(-32300, "Transport error - response too large");
            }

            $contents = $this->readContents($size);
        } while ($recvHandle !== $this->reqHandle);

<<<<<<< HEAD
=======
        if ($this->hasError()) {
            Log::error($this->displayError());
            Basic::console($this->displayError());
            return new XmlArrayObject();
        }

>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8
        $parsedResponse = $this->xmlRpcService->parseResponse($contents);

        return $parsedResponse;
    }

    /**
     * Determines the system's endian-ness and sets the protocol version.
     *
     * @return void
     */
    private function endian(): void
    {
        $littleEndian = pack('V', 1);
        $nativeEndian = unpack('L', $littleEndian)[1];
        $this->protocol = ($nativeEndian === 1) ? 2 : 1;
    }

    /**
     * Reads the contents from the socket.
     *
     * @param int $size The number of bytes to read.
     * @return string The contents read throw an error occurred.
     */
    private function readContents(int $size): string
    {
        $contents = '';
        $this->socketConnection->setStreamTimeout(0.10);
        while (strlen($contents) < $size) {
            $chunk = $this->socketConnection->fread($size - strlen($contents));
            if ($chunk === false || $chunk === '') {
                $this->throwError(-32300, 'Transport error - reading contents');
            }
            $contents .= $chunk;
        }
        return $contents;
    }

    /**
     * Converts a 64-bit handle to a 32-bit value if necessary.
     *
     * @param int $handle The handle to convert.
     * @return int The converted handle.
     */
    private function convertHandle(int $handle): int
    {
        $bits = sprintf('%b', $handle);
        return (strlen($bits) === 64) ? bindec(substr($bits, 32)) : $handle;
    }

    /**
     * Unpacks data from a big-endian format.
     *
     * @param string $format The format string.
     * @param string $data The data to unpack.
     * @return array The unpacked data.
     */
    private function bigEndianUnpack(string $format, string $data): array
    {
        $ar = unpack($format, $data);
        $vals = array_values($ar);
        $formats = explode('/', $format);
        $i = 0;

        foreach ($formats as $formatPart) {
            $repeater = (int) substr($formatPart, 1) ?: 1;
            if (isset($formatPart[1]) && $formatPart[1] === '*') {
                $repeater = count($ar) - $i;
            }
            if ($formatPart[0] !== 'd') {
                $i += $repeater;
                continue;
            }
            for ($a = $i; $a < $i + $repeater; ++$a) {
                $p = strrev(pack('d', $vals[$i]));
                $vals[$i] = unpack('d1d', $p)['d'];
                ++$i;
            }
        }

        return array_combine(array_keys($ar), array_values($vals));
    }

    /**
     * Handles errors by creating an IxrError instance and setting it.
     *
     * @param int $code The error code.
     * @param string $message The error message.
     * @return void
     */
    private function throwError(int $code, string $message): void
    {
        $error = new IxrError($code, $message);
        $this->setError($error);
        Basic::console($this->displayError());
        throw new Exception($this->error->getMessage(), $this->error->getCode());
    }

    private function logError(string $message): void
    {
        $meta = stream_get_meta_data($this->socketConnection->socket);
        Log::error("{$message}. Socket meta-data: " . print_r($meta, true));
    }
}
