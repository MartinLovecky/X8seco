<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Gbx;

/**
 * Class IxrError
 *
 * Handles error information by storing an error code and message. This class also
 * supports dynamic property assignment via magic methods and can load error data
 * from an array.
 *
 * @package Yuhzel\X8seco\Core\Gbx
 * @author Yuhzel
 */
class IxrError
{
    /**
     * @var int Error code.
     */
    public int $code;
    /**
     * @var string Error message.
     */
    public string $message;
    /**
     * @var array Holds dynamic properties set via magic methods.
     */
    private array $properties = [];

    /**
     * IxrError constructor.
     *
     * Initializes the error with a given code and message.
     *
     * @param int $code The error code, defaults to 0.
     * @param string $message The error message, defaults to an empty string.
     */
    public function __construct(int $code = 0, string $message = '')
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * Magic setter method to dynamically set properties.
     *
     * @param string $name The property name.
     * @param mixed $value The value to assign to the property.
     */
    public function __set($name, $value): void
    {
        $this->properties[$name] = $value;
    }

    /**
     * Magic getter method to dynamically retrieve properties.
     *
     * @param string $name The property name.
     * @return mixed|null The value of the property if set, or null.
     */
    public function __get($name): mixed
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * Populates error data from an associative array.
     *
     * The array is expected to contain 'name' and 'value' pairs, representing
     * the fault code or string and their respective values.
     *
     * @param array $members An array of error data.
     */
    public function fromArray(array $members): void
    {
        foreach ($members as $member) {
            // 'faultCode' or 'faultString'
            $name = $member['name'];
            // Extract the value (int or string)
            $value = reset($member['value']);
            // Dynamically set the property
            $this->__set($name, $value);
        }
    }

    /**
     * Retrieves the error code.
     *
     * @return int The error code.
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Retrieves the error message.
     *
     * @return string The error message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Sets the error code.
     *
     * @param int $code The error code.
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * Sets the error message.
     *
     * @param string $message The error message.
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
