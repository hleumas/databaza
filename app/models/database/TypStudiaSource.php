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
class TypStudiaSource extends \Nette\Object
{
    private $_dbConnection;
    const TABLE = 'typ_studia';
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
        $fetch = $this->_dbConnection
            ->table(self::TABLE)
            ->where('id', $id)
            ->fetch();
        if ($fetch === false) {
            throw new InvalidIdException("Id $id was not found in database");
        }
        return FlatArray::toArray($fetch);
    }

    public function getAll()
    {
        $pairs = $this->_dbConnection
            ->table(self::TABLE)
            ->fetchPairs('id');
        return FlatArray::toArray($pairs);
    }


}
