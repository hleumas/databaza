<?php

namespace Gridito;

use \Nette\Utils\Strings;

/**
 * FullTextModel
 *
 * @author Samuel Hapak
 * @license MIT
 */
abstract class FullTextModel extends AbstractModel
{

    private $data     = null;
    private $lastSort = null;
    private $filter   = null;

    public function filter($phrase, $collumns) {
        $phrase = strtolower(Strings::toAscii($phrase));
        $this->filter = array(
            Strings::split($phrase, '/\s+/'),
            $collumns
        );
        $this->data = null;
        return $this;
    }

    protected abstract function fetchData($limit = null, $offset = 0);

    protected abstract function countData();

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
            $this->data = $this->fetchData();
            $this->lastSort = $this->getSorting();
            $this->filterData();
        }
        return array_slice($this->data, $offset, $limit, true);
    }

	public function getItems()
	{
        if (is_null($this->filter)) {
            return $this->fetchData($this->getLimit(), $this->getOffset());
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
            return $this->countData();
        } else {
            return count($this->getFiltered());
        }
	}

}
