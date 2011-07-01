<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * Source Interface
 *
 * @authore Samuel
 * @package database
 */

interface ISource
{
    /**
     * Source constructor
     *
     * @param \Nette\Database\Connection $dbConnection
     */
    public function __construct($dbConnection);

    /**
     * Get the record by id
     *
     * @param string $id
     */
    public function getById($id); 

    /**
     * Insert record into db
     *
     * @param IRecord $record
     */
    public function insert($record);

    /**
     * Update record in the database
     *
     * @param IRecord $record
     */
    public function update($record);

    /**
     * Delete record from the database
     *
     * @param string  $id
     * @param bool    $force
     */
    public function delete($id, $force);
}

class InvalidIdException extends Exception
{
}
