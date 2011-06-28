<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * RiesitelSource
 *
 * @authore Samuel
 * @package database
 */

use \Nette\Database\Connection;
class SkolaSource extends CommonSource
{

    protected function getTable()
    {
        return 'skola';
    }
    /**
     * Get the record by id
     *
     * @param string $id
     *
     * @return RiesitelRecord
     */
    public function getById($id)
    {
        return new SkolaRecord($this->whereId($id)->fetch());
    }

    /**
     * Insert record into db
     *
     * @param IRecord $record
     *
     * @return int last id
     */
    protected function insertDb($record)
    {
        $conn = $this->getConnection();
        $conn->table($this->getTable())->insert($record->data);
        return $conn->lastInsertId();
    }

    /**
     * Update record in the database
     *
     * @param IRecord $record
     */
    public function updateDb($record)
    {
        $this->whereId($record->data['id'])->update($record->data);
    }

    /**
     * Delete record from the database
     *
     * @param string  $id
     * @param bool    $force
     */
    public function delete($id, $force = false)
    {
        $this->whereId($id)->delete();
    }
}
