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
class SkolaSource extends CommonSource
{

    private $adresaSrc = null;
    public function __construct($dbConnection)
    {
        parent::__construct($dbConnection);
        $this->setTable('skola');
        
    }

    public function setAdresaSource($src)
    {
        $this->adresaSrc = $src;
        $this->addPropertySource('adresa', $src);
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
        return new SkolaRecord(parent::getById($id));
    }

    public function getAll()
    {
        return $this->getConnection()
            ->table('zoznamy_skola_view')
            ->select('id, nazov, mesto')
            ->order('mesto ASC, nazov ASC')
            ->fetchPairs('id');
    }


    /**
     * Delete record from the database
     *
     * @param string  $id
     * @param bool    $force
     */
    public function delete($id, $force = false)
    {
        try {
            parent::delete($id);
        } catch (PDOException $e) {
            $riesitel = $this->getConnection()
                ->fetch('SELECT meno, priezvisko FROM riesitel LEFT JOIN osoba ON riesitel.osoba_id = osoba.id WHERE skola_id = ?', $id);
            throw new DBIntegrityException("Školu navštevuje riešiteľ {$riesitel['meno']} {$riesitel['priezvisko']}.");
        }
    }
}
