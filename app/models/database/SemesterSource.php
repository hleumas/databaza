<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * SemesterSource
 *
 * @authore Samuel
 * @package database
 */

use \Nette\Database\Connection;
class SemesterSource extends \Nette\Object
{

    private $adresaSrc = null;
    private $kategoria;    
    private $dbConnection;
    private $table;
        
    public function __construct($dbConnection, $kategoria)
    {
        $this->dbConnection = $dbConnection;
        $this->kategoria = $kategoria;
        $this->table = 'semester';
    }

    public function getLastId()
    {
        $fetch = $this->dbConnection
            ->table($this->table)
            ->select('id')
            ->where('kategoria_id', $this->kategoria->id)
            ->order('rok DESC, cast DESC')
            ->limit(1)
            ->fetch();
        if ($fetch === false) {
            return false;
        } else {
            return $fetch['id'];
        }
    }

    public function getAll()
    {
        $fetch = $this->dbConnection
            ->table($this->table)
            ->where('kategoria_id', $this->kategoria->id)
            ->order('rok DESC, cast DESC')
            ->fetchPairs('id');
        if ($fetch === false) {
            return array();
        } else {
            return $fetch;
        }
    }
    public function insertNew()
    {
        $fetch = $this->dbConnection
            ->table($this->table)
            ->where('kategoria_id', $this->kategoria->id)
            ->order('rok DESC, cast DESC')
            ->limit(1)
            ->fetch();

        if ($fetch === false) {
            throw new InvalidIdException('There is no semester in kategory');
        }

        $record = array(
            'rok' => $fetch['rok'],
            'cast' => $fetch['cast'] + 1,
            'kategoria_id' => $this->kategoria->id
        );
        if ($this->kategoria->pocet_casti < $record['cast']) {
           $record['rok']++;
           $record['cast'] = 1;
        }
        $this->dbConnection
            ->table($this->table)
            ->insert($record);
    }
    public function deleteLast()
    {
        $fetch = $this->dbConnection
            ->table($this->table)
            ->where('kategoria_id', $this->kategoria->id)
            ->order('rok DESC, cast DESC')
            ->limit(1)
            ->select('id')
            ->fetch();
        if ($fetch === false) {
            throw new InvalidIdException('There is no semester in kategory');
        }
        $this->dbConnection
            ->table($this->table)
            ->where('id', $fetch['id'])
            ->delete();
    }
}

