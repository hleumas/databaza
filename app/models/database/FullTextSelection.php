<?php

namespace Gridito;

use Nette\Database\Table\Selection;
use \Nette\Utils\Strings;

/**
 * FullTextModel
 *
 * @author Samuel Hapak
 * @license MIT
 */
class FullTextSelection extends FullTextModel
{
    /** @var Nette\Database\Table\Selection */
    private $selection;

    private $data     = null;
    private $lastSort = null;
    private $filter   = null;

	/**
	 * Constructor
	 * @param Selection $selection
	 */
	public function __construct(Selection $selection)
	{
        $this->selection = $selection;
	}

	public function getItemByUniqueId($uniqueId)
	{
        $select = clone $this->selection;
        return $select->where($this->getPrimaryKey(), $uniqueId)
            ->fetch();
	}

    protected function fetchData($limit = null, $offset = 0)
    {
        $select = clone $this->selection;
        $this->lastSort = $this->getSorting();
		list($sortColumn, $sortType) = $this->lastSort;
		if ($sortColumn) {
            $select->order("$sortColumn $sortType");
		}
        return $select->limit($limit, $offset)
            ->fetchPairs($this->getPrimaryKey());
    }

	/**
	 * Item count
	 * @return int
	 */
	protected function countData()
	{
        return $this->selection->count('*');
	}

}
