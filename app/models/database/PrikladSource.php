<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * PrikladSource
 *
 * @authore Samuel
 * @package database
 */

class PrikladSource extends CommonSource
{

    private $seriaSrc;
    public function setSeriaSource($src)
    {
        $this->seriaSrc = $src;
    }
    protected function getTable()
    {
        return 'priklad';
    }

    /**
     * Get the record by id
     *
     * @param string $id
     *
     * @return SeriaRecord
     */
    public function getById($id)
    {
        $priklad = parent::getById($id);
        $priklad['seria'] = $priklad['seria_id'];
        return new PrikladRecord($priklad);
    }

    public function getAll($seriaId)
    {
        $fetch = $this->getConnection()
            ->table($this->getTable())
            ->where('seria_id', $seriaId)
            ->order('cislo')
            ->fetchPairs('id');
        $result = array();
        foreach ($fetch as $id => $priklad) {
            $result[$id] = new PrikladRecord($priklad);
            $result[$id]['seria'] = $seriaId;
        }
        return $result;
    }

    public function raiseNumber($id)
    {
        $priklad = $this->getById($id);
        $last = $this->getLastNumber($priklad['seria']);
        if ($priklad['cislo'] == $last) {
            throw new InvalidIdException("Priklad with id $id has biggest possible number");
        }

        $this->swapNumbers($priklad['cislo'], $priklad['cislo'] + 1, $priklad['seria']);

    }

    private function swapNumbers($old, $new, $seriaId)
    {
        $kody = $this->getConnection()->fetchAll(
            'SELECT priklad.id, priklad.cislo, priklad.kod FROM priklad '
          . 'WHERE seria_id = ? AND cislo IN (?, ?)',
            $seriaId, $old, $new);
        for ($i = 0; $i < 2; $i++) {
            $this->getConnection()->exec(
                "UPDATE priklad SET priklad.cislo=?, priklad.kod=? WHERE priklad.id=?",
                $kody[$i]['cislo'], $kody[$i]['kod'], $kody[($i + 1) % 2]['id']);
        }
    }

    public function lowerNumber($id)
    {
        $priklad = $this->getById($id);
        if ($priklad['cislo'] == 1) {
            throw new InvalidIdException("Priklad with id $id has lowest possible number");
        }

        $this->swapNumbers($priklad['cislo'], $priklad['cislo'] - 1, $priklad['seria']);
    }

    public function getLastNumber($seriaId)
    {
        $fetch = $this->getConnection()
            ->table($this->getTable())
            ->select('cislo')
            ->where('seria_id', $seriaId)
            ->order('cislo DESC')
            ->limit(1)
            ->fetch();
        if ($fetch === false) {
            return 0;
        } else {
            return $fetch['cislo'];
        }
    }

    public function insertDb($record)
    {
        $record['seria_id'] = $record['seria'];
        $record['cislo']    = $this->getLastNumber($record['seria']) + 1;
        unset($record['seria']);
        return parent::insertDb($record);
    }
    public function updateDb($record)
    {
        $record['seria_id'] = $record['seria'];
        unset($record['seria']);
        unset($record['cislo']);
        return parent::updateDb($record);
    }

    public function delete($id, $force=false)
    {
        try {
        $this->getConnection()->beginTransaction();
        $this->getConnection()->exec(<<<SQL
UPDATE priklad, (SELECT seria_id, cislo FROM priklad WHERE id=?) p
SET priklad.cislo = priklad.cislo - 1
WHERE priklad.seria_id = p.seria_id
AND priklad.cislo > p.cislo;
SQL
        , $id);

        $this->getConnection()->exec('DELETE FROM priklad WHERE id = ?', $id);
        $this->getConnection()->commit();
        } catch (PDOException $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

}
