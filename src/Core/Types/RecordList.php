<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Core\Types;

use OutOfBoundsException;
use Yuhzel\Xaseco\Core\Types\RaspType;
use Yuhzel\Xaseco\Core\Types\Record;
use Yuhzel\Xaseco\Services\Arrays\ArrayManipulator;

class RecordList
{
    public function __construct(
        public RaspType $rasp,
        public ArrayManipulator $am,
        public int $limit = 0,
        public array $records = [],
    ) {
    }

    /**
     * Set the limit for the number of records.
     *
     * @param int|null $limit
     */
    public function setLimit(?int $limit = null): void
    {
        $this->limit = $limit ?? $this->rasp->maxrecs;
    }

    /**
     * Get the record at a specific rank.
     *
     * @param int $rank
     * @return Record|null
     */
    public function getRecord($rank): ?Record
    {
        return $this->records[$rank] ?? null;
    }

    /**
     * Set a record at a specific rank.
     *
     * @param int $rank
     * @param Record $record
     * @throws OutOfBoundsException
     */
    public function setRecord($rank, $record): void
    {
        if ($rank < 0 || $rank >= count($this->records)) {
            throw new OutOfBoundsException("Invalid rank: $rank");
        }

        $this->records[$rank] = $record;
    }

    /**
     * Move a record from one rank to another.
     *
     * @param int $from
     * @param int $to
     * @throws OutOfBoundsException
     */
    public function moveRecord(int $from, int $to): void
    {
        if ($from < 0 || $from >= count($this->records)) {
            throw new OutOfBoundsException("Invalid from rank: $from");
        }
        if ($to < 0) {
            throw new OutOfBoundsException("Invalid to rank: $to");
        }

        $this->am->moveArrayElement($this->records, $from, $to);
    }

    /**
     * Add a new record at a specific rank, or at the end if the rank is negative.
     *
     * @param Record $record
     * @param int $rank
     */
    public function addRecord(Record $record, int $rank = -1): void
    {
        if ($record->score <= 0) {
            return;
        }

        $rank = ($rank < 0) ? count($this->records) : min($rank, $this->limit - 1);

        if (count($this->records) >= $this->limit) {
            array_pop($this->records);
        }

        $this->am->insertArrayElement($this->records, $record, $rank);
    }

    /**
     * Delete a record at a specific rank, or the last record if the rank is negative.
     *
     * @param int $rank
     * @throws OutOfBoundsException
     */
    public function delRecord(int $rank = -1): void
    {
        if ($rank < 0) {
            $rank = count($this->records) - 1; // Default to last record if $rank is -1
        }

        if ($rank < 0 || $rank >= count($this->records)) {
            throw new OutOfBoundsException("Invalid rank: $rank");
        }

        $this->am->removeArrayElement($this->records, $rank);
    }

    public function clear(): void
    {
        $this->records = [];
    }
}
