<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * RiesitelPrikladFile
 *
 * @authore Samuel
 * @package database
 */

use \Nette\Database\Connection;
class RiesitelPrikladFileSource
{

    private $_dbConnection;
    private $table = 'priklady_files';

    private $_riesitelSeriaSource;

    public function __construct($dbConnection, $riesitelSeriaSource)
    {
        $this->_dbConnection = $dbConnection;
        $this->_riesitelSeriaSource = $riesitelSeriaSource;
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
        return $this->_dbConnection->table($this->table)->select('*')->where('id', $id)->fetch();
    }

    public function insert($record)
    {
        $row = $this->_dbConnection
            ->table('riesitel_priklady')
            ->select('id')
            ->where('riesitel_id', $record['riesitel'])
            ->where('priklad_id', $record['priklad'])
            ->fetch();
        if (!$row) {
            $this->_dbConnection->exec(
                'INSERT INTO riesitel_priklady',
                array(
                    'riesitel_id' => $record['riesitel'],
                    'priklad_id' => $record['priklad'],
                    'submit' => '1'
                ));
        }
        $meskanie = $this->_riesitelSeriaSource->computeMeskanie(
            $record['seria'],
            $record['uploaded']);
        if ($this->_riesitelSeriaSource->hasRecord($record['riesitel'], $record['seria'])) {
            $this->_riesitelSeriaSource->updateMeskanie($record['riesitel'], $record['seria'], $meskanie);
        } else {
            $this->_riesitelSeriaSource->insert(array(
               'riesitel' => $record['riesitel'], 
               'seria' => $record['seria'],
               'meskanie' => $meskanie,
               'bonus' => 0
           ));

        }
        $data['riesitel_id'] = $record['riesitel'];
        $data['priklad_id'] = $record['priklad'];
        $data['filename'] = $record['file']->getName();
        $data['filesize'] = $record['file']->getSize();
        $data['uploaded'] = $record['uploaded'];
        $data['content'] = file_get_contents($record['file']->getTemporaryFile());

        unlink($record['file']->getTemporaryFile());
        $this->_dbConnection->exec('INSERT INTO priklady_files', $data);
    }

    public function update($record)
    {

    }

    public function listByRiesitelSeria($riesitelId, $seriaId)
    {
        $sql = <<<SQL
SELECT priklady_files.*, priklad.kod, priklad.nazov,
priklad.opravovatel, riesitel_priklady.body,
riesitel_priklady.id as odovzdal, priklad.id as priklad_id
FROM priklad LEFT JOIN riesitel_priklady
ON priklad.id = riesitel_priklady.priklad_id
AND riesitel_priklady.riesitel_id = ?
LEFT JOIN priklady_files
ON priklady_files.riesitel_id = riesitel_priklady.riesitel_id
AND priklady_files.priklad_id = priklad.id
WHERE priklad.seria_id = ?
ORDER BY priklad.cislo ASC, priklady_files.uploaded DESC
SQL;
        $list = $this->_dbConnection->fetchAll($sql, $riesitelId, $seriaId);
        foreach ($list as &$item) {
            if (!is_null($item['odovzdal']) && is_null($item['body'])) {
                $item['body'] = -1;
            }
        }
        return $list;
    }

}
