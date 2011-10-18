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
class RiesitelSeriaSource
{
    private $dbConnection;

    /**
     * Source constructor
     *
     * @param \Nette\Database\Connection $dbConnection
     */
    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Get the record by id
     *
     * @param string $id
     *
     * @return RiesitelRecord
     */
    public function getById($riesitel, $seria)
    {
        $fetch = $this->dbConnection
             ->select('meskanie, bonus')
             ->table('riesitel_seria')
             ->where('riesitel_id', $riesitel)
             ->where('seria_id', $seria)
             ->fetch();
        if ($fetch === false) {
            throw new InvalidIdException('Invalid id of riesitel or seria');
        }
        $record = new RiesitelRecord($fetch);
        $record['riesitel'] = $riesitel;
        $record['seria'] = $seria;
        $sql = <<<SQL
SELECT riesitel_priklady.body, priklad.cislo
FROM riesitel_priklady LEFT JOIN priklad
ON priklad.riesitel_id = riesitel_priklady.riesitel_id
WHERE priklad.seria_id = ?
AND riesitel_priklady.riesitel_id = ?
SQL;
        $priklady = $this->dbConnection->fetchAll($sql, $seria, $riesitel);
        foreach ($priklady as $priklad) {
            $record[$priklad['cislo']] = $priklad['body'];
        }
        return $record;
    }

    public function hasRecord($riesitel, $seria)
    {
        return (bool) $this->dbConnection
            ->table('riesitel_seria')
            ->where('riesitel_id', $riesitel)
            ->where('seria_id', $seria)
            ->count('*');
    }

    public function setPriklad($riesitel, $seria, $cislo, $body)
    {
        $fetch = $this->dbConnection
             ->table('priklad')
             ->where('seria_id', $seria)
             ->where('cislo', $cislo)
             ->select('id')
             ->fetch();
        if ($fetch === false) {
            throw new InvalidIdException('Invalid priklad cislo or seria');
        }
        $this->setPrikladById($riesitel, $fetch['id'], $body);
    }

    public function setPrikladById($riesitel, $priklad, $body)
    {
        $sql = <<<SQL
INSERT INTO riesitel_priklady
(riesitel_id, priklad_id, body) VALUES (?, ?, ?)
ON DUPLICATE KEY UPDATE body=VALUES(body)
SQL;
        $this->dbConnection->exec($sql, $riesitel, $priklad, $body);
    }

    public function deletePriklad($riesitel, $seria, $cislo)
    {
        $sql = <<<SQL
DELETE riesitel_priklady.*
FROM riesitel_priklady LEFT JOIN priklad
ON riesitel_priklady.priklad_id = priklad.id
WHERE riesitel_priklady.riesitel_id = ?
AND   priklad.seria_id = ?
AND   priklad.cislo = ?
SQL;
        $this->dbConnection->exec($sql, $riesitel, $seria, $cislo);


    }

    public function computeMeskanie($seriaId, $date)
    {
        $row = $this->dbConnection
            ->table('seria')
            ->select('termin')
            ->where('id', $seriaId)
            ->fetch();
        $termin = new \Nette\DateTime($row['termin']);
        $meskanie = $date->diff($termin)->days;
        return $meskanie > 0 ? $meskanie : 0;
    }

    private function updateColumnValue($riesitel, $seria, $column, $value)
    {
        $sql = <<<SQL
UPDATE riesitel_seria
SET $column=?
WHERE riesitel_seria.riesitel_id = ?
AND riesitel_seria.seria_id = ?
SQL;
        $this->dbConnection->exec($sql, $value, $riesitel, $seria);
    }

    public function updateMeskanie($riesitel, $seria, $meskanie)
    {
        $this->updateColumnValue($riesitel, $seria, 'meskanie', $meskanie);
    }

    public function updateBonus($riesitel, $seria, $bonus)
    {
        $this->updateColumnValue($riesitel, $seria, 'bonus', $bonus);
    }

    private function setPriklady($record)
    {
        /** Priklady have numeric keys */
        foreach ($record as $key => $value) {
            if (is_numeric($key)) {
                $this->setPriklad($record['riesitel'], $record['seria'], $key, $value);
            }
        }
    }

    private function getInsertData($record)
    {
        return array(
                'riesitel_id' => $record['riesitel'],
                'seria_id' => $record['seria'],
                'meskanie' => $record['meskanie'],
                'bonus' => $record['bonus']
            );
    }
    public function insert($record)
    {
        $this->dbConnection->exec(
            'INSERT INTO riesitel_seria',
            $this->getInsertData($record)
        );
        $this->setPriklady($record);
    }

    public function update($record)
    {
        $this->dbConnection->exec(
            'UPDATE riesitel_seria SET ? WHERE riesitel_id=? AND seria_id=?',
            $this->getInsertData($record)
        );
        $this->setPriklady($record);
    }

    /**
     * Delete record from the database
     *
     * @param string $riesitel
     * @param string $seria
     */
    public function delete($riesitel, $seria)
    {
        $sql = <<<SQL
DELETE riesitel_priklady.*, riesitel_seria.*
FROM riesitel_seria LEFT JOIN priklad
ON riesitel_seria.seria_id = priklad.seria_id
LEFT JOIN riesitel_priklady
ON riesitel_priklady.priklad_id = priklad.id
AND riesitel_seria.riesitel_id = riesitel_priklady.riesitel_id
WHERE riesitel_seria.seria_id = ?
AND riesitel_seria.riesitel_id = ?
SQL;
        $this->dbConnection->exec($sql, $seria, $riesitel);
    }

}
