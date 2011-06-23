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
class RiesitelSource implements ISource
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

    /**
     * Get the record by id
     *
     * @param string $id
     *
     * @return RiesitelRecord
     */
    public function getById($id)
    {
        $query = 'SELECT ';
        $record = new Riesitel();
    }

    /**
     * Insert record into db
     *
     * @param IRecord $record
     */
    public function insert($record)
    {
    }

    /**
     * Update record in the database
     *
     * @param IRecord $record
     */
    public function update($record)
    {
    }

    /**
     * Delete record from the database
     *
     * @param string  $id
     * @param bool    $force
     */
    public function delete($id, $force)
    {
    }
}
