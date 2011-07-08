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

    private $cachedAll = false;
    private $cache = array();
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
        if (!isset($this->cache[$id])) {
            $fetch = $this->_dbConnection
                ->table(self::TABLE)
                ->where('id', $id)
                ->fetch();
            if ($fetch === false) {
                throw new InvalidIdException("Id $id was not found in database");
            }
            $this->cache[$id] = FlatArray::toArray($fetch);
        }
        return $this->cache[$id];
    }

    public function getAll()
    {
        if (!$this->cachedAll) {
            $pairs = $this->_dbConnection
                ->table(self::TABLE)
                ->fetchPairs('id');
            $this->cache = FlatArray::toArray($pairs);
            $this->cachedAll = true;
        }
        return $this->cache;
    }
}
