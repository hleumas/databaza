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
abstract class VysledkovkaModel extends AbstractModel
{

    private $pCount;

    private $results;

	/**
	 * Constructor
     *
     * @param int   $seriaId id of main seria
     * @param array $serie   ids of all series to take into account
     * @param int   $pCount  number of priklady
	 */
	public function __construct($dbConection, $serie, $pCount)
	{
        $this->pCount     = $pCount;
        $raw = $this->getRawData(
            $this->getRiesitelia($dbConection, $serie),
            $this->getPriklady($dbConection, $serie),
            $serie
        );
        $results = $this->sortPoradie(end($raw));
        $results = $this->enumPoradie($results);
        $results = $this->filterRiesitelia($results);
        $this->results = $this->enumPoradie($results);
	}

    private function getPriklady($dbConection, $serie)
    {
        $sql = <<<SQL
SELECT riesitel_priklady.riesitel_id,
riesitel_priklady.body, 
priklad.id, priklad.seria_id, priklad.kod, priklad.nazov, priklad.cislo
FROM riesitel_priklady LEFT JOIN priklad ON
priklad.id = riesitel_priklady.priklad_id
INNER JOIN riesitel_seria ON
riesitel_seria.riesitel_id=riesitel_priklady.riesitel_id
WHERE priklad.seria_id IN (?)
SQL;
        return $dbConection->fetchAll($sql, $serie);
    }

    public function getRawData($riesitelia, $priklady, $serie)
    {
        $data = array();
        foreach ($serie as $seria) {
            $data[$seria] = array();
        }

        $this->addRiesitelia($data, $riesitelia, $serie);
        $this->addPriklady($data, $priklady);
        $this->sumSerie($data);

        return $data;
    }

    private function addRiesitelia(&$data, $riesitelia, $serie)
    {
        foreach ($riesitelia as $riesitel) {
            $data[$riesitel['seria_id']][$riesitel['id']]
                = \FlatArray::toArray($riesitel);
            foreach ($serie as $seria) {
                if (!isset($data[$seria][$riesitel['id']])) {
                    $data[$seria][$riesitel['id']] = $this->getEmptyRiesitel($riesitel);
                }
            }
        }
    }

    private function addPriklady(&$data, $priklady)
    {
        foreach ($data as &$seria) {
            foreach ($seria as &$riesitel) {
                for ($i = 1; $i <= $this->pCount; $i++) {
                    $riesitel[$i] = null;
                }
            }
        }

        foreach ($priklady as $priklad) {
            $data[$priklad['seria_id']][$priklad['riesitel_id']][$priklad['cislo']] = 
                is_null($priklad['body']) ? '*' : $priklad['body'];
            foreach ($data as $sid => $seria) {
                $data[$sid][$priklad['riesitel_id']]["sent{$priklad['cislo']}"] = true;
            }
        }

    }

    private function sumSerie(&$data)
    {
        $previousSeria = false;
        foreach ($data as $sid => &$seria) {
            foreach ($seria as $id => &$riesitel) {
                $riesitel['bonus'] = $this->getBonus($riesitel);
                $riesitel['sum']   = $this->getSum($riesitel);
                $riesitel['total'] = $riesitel['sum'];
                $riesitel['serie'] = array();
                if ($previousSeria !== false) {
                    $riesitel['total'] += $data[$previousSeria][$id]['total'];
                }
            }
            $previousSeria = $sid;
        }
    }

    private function sortPoradie($poradie)
    {
        uasort($poradie, function($a, $b) {
            if ($a['total'] === $b['total']) return 0;
            return $a['total'] > $b['total'] ? -1 : 1;
        });
        return $poradie;
    }

    private function enumPoradie($poradie)
    {
        $last = null;
        $i = 0;
        foreach ($poradie as &$item) {
            $i++;
            if (is_null($last)) {
                $item['poradie'] = 1;
                $last = &$item;
                continue;
            }
            if ($item['total'] === $last['total']) {
                $item['poradie'] = $last['poradie'];
            } else {
                $item['poradie'] = $i;
                $last = &$item;
            }
        }
        return $poradie;
    }
	public function getItems()
	{
        $results = array_slice(
            $this->results,
            $this->getOffset(),
            $this->getLimit(),
            true
            );
        return \Nette\ArrayHash::from($results);
	}

	public function getItemByUniqueId($uniqueId)
	{
        return $this->results[$uniqueId];
	}

    protected abstract function getRiesitelia($dbConection, $serie);

    public abstract function getSum($riesitel);

    protected abstract function getEmptyRiesitel($riesitel);

    protected abstract function getBonus($riesitel);

    protected abstract function filterRiesitelia($results);


	/**
	 * Item count
	 * @return int
	 */
	protected function _count()
	{
        return count($this->results);
	}

}
