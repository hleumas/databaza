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
abstract class CommonSource implements ISource
{
    private $_dbConnection;
    /**
     * Source constructor
     *
     * @param \Nette\Database\Connection $dbConnection
     */
    public function __construct($dbConnection)
    {
        $this->_dbConnection = $dbConnection;
    }

    public function getConnection()
    {
        return $this->_dbConnection;
    }

    protected function whereId($id)
    {
        return $this->_dbConnection->table($this->getTable())
            ->where('id = ?', $id);
    }

    protected abstract function getTable();

    /**
     * Get the record by id
     *
     * @param string $id
     *
     * @return RiesitelRecord
     */
    public abstract function getById($id);

    /**
     * Insert record into db
     *
     * @param IRecord $record
     *
     * @return int last id
     */
    public function insert($record)
    {
        $record->validate();
        $record->normalize();
        return $this->insertDb($record);
    }

    /**
     * Update record in the database
     *
     * @param IRecord $record
     */
    public function update($record)
    {
        $record->validate();
        $record->normalize();
        return $this->updateDb($record);
    }

    protected abstract function insertDb($record);
    protected abstract function updateDb($record);

    /**
     * Delete record from the database
     *
     * @param string  $id
     * @param bool    $force
     */
    public abstract function delete($id, $force);
}
