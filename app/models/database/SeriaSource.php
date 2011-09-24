<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * SeriaSource
 *
 * @authore Samuel
 * @package database
 */

use \Nette\Database\Connection;
class SeriaSource extends CommonSource
{

    private $kategoria;
    public function setKategoria($kategoria)
    {
        $this->kategoria = $kategoria;
    }
    protected function getTable()
    {
        return 'seria';
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
        $seria = parent::getById($id);
        $seria['semester'] = $seria['semester_id'];
        $seria['aktualna'] = ($id == $this->kategoria->aktualna_seria_id);
        return new SeriaRecord($seria);
    }

    public function getAll($semesterId = null)
    {
        if (is_null($semesterId)) {
            $fetch = $this->getConnection()
                ->table($this->getTable())
                ->select('seria.*')
                ->where('seria.semester_id = semester.id')
                ->where('semester.kategoria_id', $this->kategoria->id)
                ->order('cislo DESC')
                ->fetchPairs('id');
        } else {
            $fetch = $this->getConnection()
                ->table($this->getTable())
                ->where('semester_id', $semesterId)
                ->order('cislo DESC')
                ->fetchPairs('id');
        }
        $result = array();
        foreach ($fetch as $id => $seria) {
            $result[$id] = new SeriaRecord($seria);
            $result[$id]['semester'] = $seria['semester_id'];
            $result[$id]['aktualna'] = ($id == $this->kategoria->aktualna_seria_id);
        }
        return $result;
    }

    public function setAktualna($id)
    {
        if ($this->kategoria->aktualna_seria_id == $id) {
            return;
        }
        $this->kategoria->aktualna_seria_id = $id;
        $set = array(
            'aktualna_seria_id' => $this->kategoria->aktualna_seria_id
        );
        $this->getConnection()
            ->table('kategoria')
            ->where('id', $this->kategoria->id)
            ->update($set);
    }

    public function getAktualna()
    {
        if ($this->kategoria->aktualna_seria_id == null) {
            return false;
        }
        return $this->getById($this->kategoria->aktualna_seria_id);
    }

    public function updateCisla($semesterId)
    {
        $this->getConnection()
            ->exec(<<<SQL
UPDATE seria s, (
    SELECT @c := @c + 1 as c, s.id FROM
        seria s, (SELECT @c := 0) r
        WHERE s.semester_id = ?
        ORDER BY termin
            ) c
SET s.cislo=c.c
WHERE s.id=c.id
SQL
        , $semesterId);
    }

    public function insertDb($record)
    {
        $record['semester_id'] = $record['semester'];
        $aktualna = $record['aktualna'];
        $record['cislo'] = 0;
        unset($record['semester']);
        unset($record['aktualna']);
        $id = parent::insertDb($record);
        if ($aktualna) {
            $this->setAktualna($id);
        }
        $this->updateCisla($record['semester_id']);
        return $id;
    }
    public function updateDb($record)
    {
        $record['semester_id'] = $record['semester'];
        $aktualna = $record['aktualna'];
        $record['cislo'] = 0;
        unset($record['semester']);
        unset($record['aktualna']);
        $id = parent::updateDb($record);
        if ($aktualna) {
            $this->setAktualna($id);
        }
        $this->updateCisla($record['semester_id']);
        return $id;
    }

    public function delete($id, $force=false)
    {
        if ($this->kategoria->aktualna_seria_id == $id) {
            throw new DBIntegrityException("Nemožno vymazať aktuálnu sériu! Nastavte inú sériu ako aktuálnu a mazanie zopakujte.");
        }
        $sql = <<<SQL
DELETE priklad.*
FROM seria LEFT JOIN priklad
ON seria.id = priklad.seria_id
WHERE seria.id = ?
SQL;
        $connection = $this->getConnection();
        $seria = $this->getById($id);
        try {
            $connection->beginTransaction();
            $connection->exec($sql, $id);
            $connection->exec(
                'DELETE FROM seria WHERE id = ?',
                $id
            );
            $connection->commit();
            $this->updateCisla($seria['semester']);
        } catch (PDOException $e) {
            $connection->rollBack();
            throw $e;
        }
    }

}
