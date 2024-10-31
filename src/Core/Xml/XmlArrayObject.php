<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Xml;

use ArrayObject;
use BadMethodCallException;
use InvalidArgumentException;

/**
 * XmlArrayObject class extends ArrayObject to handle XML data and attributes.
 *
 * This class provides an object-oriented interface for accessing XML elements and their attributes,
 * and supports recursive conversion of the object to an array.
 *
 * @package Yuhzel\X8seco\Core
 * @method bool array_key_exists(string $key) Check if the key exists in the array
 * These properites created with __set
 * @property null|object $config
 * @property mixed $manialinkId
 * @property mixed $retryTime
 * @property mixed $retryWait
 * @property mixed $login
 * @property mixed $nation
 * @property mixed $urls
 * @property mixed $templates
 * @property mixed $currentMap
 * @property mixed $vote_cancel
 * @property mixed $music_widget
 * @property mixed $dedimania_records
 * @property mixed $live_rankings
 * @property mixed $local_records
 * @property mixed $checkpointcount_widget
 * @property mixed $challenge_widget
 * @property mixed $style
 * @property mixed $clock_widget
 * @property mixed $masterserver_account
 * @property mixed $database
 * @property mixed $nicemode
 * @property mixed $messages
 * @property null|int $faultCode
 * @property null|string $IPAddress
 * @property null|string $Path
 * @property null|object $LadderStats
 * @property null|int $OnlineRights
 * @property null|string $faultString
 * @property null|object $aseco
 * @property null|string $adminops_file
 * @property null|string $bannedips_file
 * @property null|object $adminops
 * @property null|object $bannedips
 * @property null|string $Login
 * @author Yuhzel
 */
class XmlArrayObject extends ArrayObject
{
    public array $result = [];
    public array $parsed = [];
    private array $data = [];
    private int $flags = 0;
    private string $iterator_class = "ArrayIterator";

    public function __construct()
    {
        parent::__construct($this->data, $this->flags, $this->iterator_class);
    }

    /**
     * Magic method to get the value of a key as if it were an object property.
     *
     * @param string $name The key name to retrieve the value for.
     *
     * @return mixed The value associated with the provided key, or null if the key does not exist.
     */
    public function __get(string $name): mixed
    {
        return $this->offsetExists($name) ? $this->offsetGet($name) : null;
    }

    /**
     * Magic method to set the value of a key as if it were an object property.
     *
     * @param string $name The key name to set the value for.
     * @param mixed $value The value to set for the provided key.
     *
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->offsetSet($name, $value);
    }

    /**
     * Magic method to call array_ functions as methods on the XmlArrayObject.
     * //NOTE - in_array needs additional handling
     * Special handling is provided for `array_key_exists`
     *
     * @param string $name The name of the method to call.
     * @param array $arguments The arguments to pass to the method.
     *
     * @return mixed The result of the array function.
     *
     * @throws BadMethodCallException If the method is not an array function.
     * @throws InvalidArgumentException If `array_key_exists` is called with insufficient arguments.
     */
    public function __call(string $name, mixed $arguments): mixed
    {
        if (!is_callable($name) || substr($name, 0, 6) !== 'array_') {
            throw new BadMethodCallException(__CLASS__ . '->' . $name);
        }

        // Special handling for array_key_exists, since it requires a key and an array
        if ($name === 'array_key_exists') {
            if (empty($arguments) || count($arguments) < 1) {
                throw new InvalidArgumentException('array_key_exists requires at least one argument (the key).');
            }
            // First argument is the key, so pass it along with the internal array
            return array_key_exists($arguments[0], $this->getArrayCopy());
        }

        return call_user_func_array($name, array_merge([$this->getArrayCopy()], $arguments));
    }

    /**
     * Add data from XmlParser
     *
     * @param string $name The name of the attribute.
     * @param mixed $value The value of the attribute.
     *
     * @return void
     */
    public function addAttribute(string $name, mixed $value): void
    {
        $this['@attributes'][$name] = $value;
    }

    public function add(string $key, mixed $value): void
    {
        $this[$key] = $value;
    }

    /**
     * Retrieves the attributes from XmlParser as XmlArrayObject
     *
     * @return mixed
     */
    public function getAttributes(): mixed
    {
        if ($this->offsetExists('@attributes')) {
            if ($this['@attributes']['result'] instanceof self) {
                return $this['@attributes']['result'];
            }
        }
        return null;
    }

    /**
     * Recursively converts the XmlArrayObject and its nested elements to an array.
     *
     * This method provides a way to get the array representation of the XML data.
     *
     * @return array The converted array representation of the XML data.
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->getArrayCopy() as $key => $value) {
            if ($value instanceof self) {
                $result[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $result[$key] = array_map(fn ($item) => $item instanceof self ? $item->toArray() : $item, $value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
