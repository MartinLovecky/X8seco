<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Exceptions;

use Exception;

/**
 * Custom exception class for XmlParser-related errors.
 *
 * This allows distinguishing exceptions from XmlParser
 * from other generic exceptions in the application.
 *
 * @package Yuhzel\X8seco\Exceptions
 */
class XmlParserException extends Exception
{
    /**
     * Constructor for XmlParserException.
     *
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Exception|null $previous Previous exception for chaining.
     */
    public function __construct(string $message, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}