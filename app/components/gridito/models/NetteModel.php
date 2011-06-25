<?php

namespace Gridito;

use DibiFluent;
use Nette\Database\Connection;

/**
 * DibiFluent model
 *
 * @author Jan Marek
 * @license MIT
 */
class NetteModel extends AbstractModel
{
	/** @var string */
    private $_table;

    /** @var Connection */
    private $_connection;



	/**
	 * Constructor
	 * @param Connection $connection to db
	 * @param string     $table name
	 */
	public function __construct(Connection $connection, $table)
	{
        $this->_table      = $table;
        $this->_connection = $connection;
	}



	public function getItemByUniqueId($uniqueId)
	{

        return $this->_connection->table($this->_table)
            ->select('*')->where('?=?', $this->getPrimaryKey(), $uniqueId)
            ->fetch();
	}



	public function getItems()
	{
        $query = $this->_connection->table($this->_table)->select('*');

		list($sortColumn, $sortType) = $this->getSorting();
		if ($sortColumn) {
            $query->order("$sortColumn $sortType");
		}
        return $query->limit($this->getLimit(), $this->getOffset())->fetchPairs($this->getPrimaryKey());
	}



	/**
	 * Item count
	 * @return int
	 */
	protected function _count()
	{
		return $this->_connection->table($this->_table)->count('*');
	}

}
