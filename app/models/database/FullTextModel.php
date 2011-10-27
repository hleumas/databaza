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
class FullTextModel extends AbstractModel
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

    public function filter($phrase, $collumns) {
        $phrase = strtolower(Strings::toAscii($phrase));
        $this->filter = array(
            Strings::split($phrase, '/\s+/'),
            $collumns
        );
        $this->data = null;
        return $this;
    }

	public function getItemByUniqueId($uniqueId)
	{
        $select = clone $this->selection;
        return $select->where($this->getPrimaryKey(), $uniqueId)
            ->fetch();
	}

    private function fetchDB($limit = null, $offset = 0)
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

    private function filterData()
    {
        foreach ($this->data as $id => $item) {
            $value = array();
            $match = true;
            foreach ($this->filter[1] as $collumn) {
                $value[$collumn] = strtolower(Strings::toAscii($item[$collumn]));
            }
            foreach ($this->filter[0] as $word) {
                $found = false;
                foreach ($value as $val) {
                    if (strstr($val, $word) !== false) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $match = false;
                    break;
                }
            }
            if (!$match) {
                unset($this->data[$id]);
            }
        }
    }

    private function getFiltered($limit = null, $offset = 0)
    {
        if ($this->lastSort !== $this->getSorting() || is_null($this->data)) {
            $this->data = $this->fetchDB();
            $this->lastSort = $this->getSorting();
            $this->filterData();
        }
        return array_slice($this->data, $offset, $limit, true);
    }

	public function getItems()
	{
        if (is_null($this->filter)) {
            return $this->fetchDB($this->getLimit(), $this->getOffset());
        } else {
            return $this->getFiltered($this->getLimit(), $this->getOffset());
        }
	}


	/**
	 * Item count
	 * @return int
	 */
	protected function _count()
	{
        if (is_null($this->filter)) {
            return $this->selection->count('*');
        } else {
            return count($this->getFiltered());
        }
	}

}
