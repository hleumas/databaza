<?php

use Nette\Database\Table\Selection;

/**
 * Nette\Database model
 *
 * @author Samuel Hapak
 * @license MIT
 */
class RiesiteliaSeriaModel extends Gridito\AbstractModel
{
    /** @var Nette\Database\Table\Selection */
    private $seriaID;

    private $database;

	/**
	 * Constructor
	 * @param Selection $selection
	 */
	public function __construct($database, $seriaID)
	{
        $this->seriaID = $seriaID;
        $this->database = $database;
        //\Nette\Diagnostics\Debugger::dump($this->getPriklady());
        //\Nette\Diagnostics\Debugger::dump($this->getRiesitelia());
	}

    private function getRiesitelWhere($riesitelId = null)
    {
        return is_null($riesitelId)
            ? array('riesitel_seria.seria_id = ?', $this->seriaID)
            : array('riesitel.id = ?', $riesitelId);
    }

    private function getPriklady($riesitelId = null)
    {
        list($where, $id) = $this->getRiesitelWhere($riesitelId);
        $sql = <<<SQL
SELECT riesitel_priklady.riesitel_id, riesitel_priklady.body,
       riesitel_priklady.priklad_id, priklad.cislo
FROM riesitel_priklady LEFT JOIN riesitel_seria
ON riesitel_priklady.riesitel_id = riesitel_seria.riesitel_id
LEFT JOIN priklad
ON riesitel_priklady.priklad_id = priklad.id
WHERE $where
AND priklad.seria_id = ?
SQL;
        return $this->database->fetchAll($sql, $id, $this->seriaID);
    }

    public function getPrikladyCount()
    {
        return $this->database
            ->table('priklad')
            ->where('seria_id', $this->seriaID)
            ->count('*');
    }

    public function getKody()
    {
        return $this->database->fetchAll(
            'SELECT priklad.cislo, priklad.kod FROM priklad '
          . 'WHERE priklad.seria_id = ? ORDER BY priklad.cislo',
            $this->seriaID);
    }

    private function getRiesitelia($riesitelId = null)
    {
        list($where, $id) = $this->getRiesitelWhere($riesitelId);
        $sql = <<<SQL
SELECT zoznamy_riesitel_view.*, riesitel_seria.meskanie, riesitel_seria.bonus, riesitel_seria.obalky
FROM zoznamy_riesitel_view LEFT JOIN riesitel_seria
ON zoznamy_riesitel_view.id = riesitel_seria.riesitel_id
WHERE $where
SQL;
        return $this->database->fetchAll($sql, $id);
    }

    private function buildRows($riesitelia, $priklady, $prikladyCount)
    {
        $rows = array();
        foreach ($riesitelia as $riesitel) {
            $rows[$riesitel['id']] = $riesitel;
        }
        foreach ($priklady as $priklad) {
            $rows[$priklad['riesitel_id']]->$priklad['cislo'] = 
                is_null($priklad['body']) ? -1 : $priklad['body'];
        }
        return $rows;
    }

	public function getItemByUniqueId($uniqueId)
	{
        return $this->buildRows(
            $this->getRiesitelia($uniqueId),
            $this->getPriklady($uniqueId),
            $this->getPrikladyCount()
        );
	}

	public function getItems()
	{
        return array_slice($this->buildRows(
            $this->getRiesitelia(),
            $this->getPriklady(),
            $this->getPrikladyCount()
        ), $this->getOffset(), $this->getLimit());
	}


	/**
	 * Item count
	 * @return int
	 */
	protected function _count()
	{
        return $this->database
            ->table('riesitel_seria')
            ->where('seria_id', $this->seriaID)
            ->count('*');
	}

}
