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
class AdresaSource implements ISource
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
        return new AdresaRecord($this->_dbConnection
            ->fetch('SELECT * FROM adresa WHERE id=?', $id));
    }

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
        $this->_dbConnection->exec('INSERT INTO adresa', $record->data);
        return $this->_dbConnection->lastInsertId();
    }

    /**
     * Update record in the database
     *
     * @param IRecord $record
     */
    public function update($record)
    {
        $record->validate();
        $this->_dbConnection
             ->exec('UPDATE adresa SET ? WHERE id=?', $record->data['id']);
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
