<?php

namespace Yuhzel\Xaseco\Services\Arrays;

class ArrayManipulator
{
    /**
     * Inserts an element into the array at the specified position.
     *
     * @param array $array The array to modify.
     * @param mixed $value The value to insert.
     * @param int $pos The position to insert the value at.
     * @return bool True if the insertion was successful, false otherwise.
     */
    public function insertArrayElement(array &$array, mixed $value, int $pos): bool
    {
        $size = count($array);
        $pos = min($pos, $size); // Adjust $pos to be within bounds

        // Insert the new element at the given position
        array_splice($array, $pos, 0, [$value]);

        return true; // Insertion was successful
    }

    /**
     * Removes an element from the array at the specified position.
     *
     * @param array $array The array to modify.
     * @param int $pos The position of the element to remove.
     * @return bool True if the removal was successful, false otherwise.
     */
    public function removeArrayElement(array &$array, int $pos): bool
    {
        // Check if the position is valid
        if (!$this->isValidPosition($array, $pos)) {
            return false; // Position is out of range
        }

        // Remove the element at the given position
        array_splice($array, $pos, 1);

        return true; // Removal was successful
    }

    /**
     * Moves an element from one position to another within the array.
     *
     * @param array $array The array to modify.
     * @param int $from The position of the element to move.
     * @param int $to The target position for the element.
     * @return bool True if the move was successful, false otherwise.
     */
    public function moveArrayElement(array &$array, int $from, int $to): bool
    {
        $size = count($array);

        // Validate that the $from and $to are within array bounds
        if ($from < 0 || $from >= $size || $to < 0) {
            return false; // Invalid indices or no movement needed
        }

        if ($from === $to) {
            return true;
        }

        $to = min($to, $size);

        // Backup the element to be moved
        $moving_element = $array[$from];

        // Remove the element from its original position
        array_splice($array, $from, 1);

        // Reinsert the element at the new position
        array_splice($array, $to, 0, [$moving_element]);

        return true; // Movement was successful
    }

    /**
     * Checks if the given position is valid for the array.
     *
     * @param array $array The array to check.
     * @param int $pos The position to validate.
     * @return bool True if the position is valid, false otherwise.
     */
    private function isValidPosition(array $array, int $pos): bool
    {
        $size = count($array);
        return $pos >= 0 && $pos < $size; // Returns true if $pos is within range
    }
}
