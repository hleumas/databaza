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
class RiesitelSource extends CommonSource
{
    private $adresaSrc;
    private $skolaSrc;
    private $osobaSrc;
    private $studiaSrc;
    /**
     * Source constructor
     *
     * @param \Nette\Database\Connection $dbConnection
     */
    public function __construct($dbConnection)
    {
        parent::__construct($dbConnection);
        $this->setTable('riesitel');
    }

    public function setAdresaSource($src)
    {
        $this->adresaSrc = $src;
        $this->addPropertySource('koresp_adresa', $src);
    }

    public function setOsobaSource($src)
    {
        $this->osobaSrc = $src;
        $this->addPropertySource('osoba', $src);
    }

    public function setSkolaSource($src)
    {
        $this->skolaSrc = $src;
        $this->addPropertySource('skola', $src);
    }

    public function settypStudiaSource($src)
    {
        $this->studiaSrc = $src;
        $this->addPropertySource('typ_studia', $src);
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
        $data = parent::getById($id);
        $data['datum'] = new \Nette\DateTime();
        return new RiesitelRecord($data);
    }
    public function getAll()
    {
        return $this->getConnection()
            ->table('zoznamy_riesitel_view')
            ->select('id, meno, priezvisko')
            ->order('priezvisko ASC, meno ASC')
            ->fetchPairs('id');
    }

    /**
     * Insert record into db
     *
     * @param IRecord $record
     */
    public function insertDb($record)
    {
        $record['typ_studia'] = $record['typ_studia']['id'];
        unset($record['rocnik']);
        unset($record['datum']);
        return parent::insertDb($record);
    }

    /**
     * Update record in the database
     *
     * @param IRecord $record
     */
    public function updateDb($record)
    {
        $record['typ_studia'] = $record['typ_studia']['id'];
        unset($record['rocnik']);
        unset($record['datum']);
        return parent::updateDb($record);
    }

    /**
     * Delete record from the database
     *
     * @param string  $id
     * @param bool    $force
     */
    public function delete($id, $force = false)
    {
        $idValues = $this->whereId($id)
            ->select('koresp_adresa_id, osoba_id')
            ->fetch();
        $this->whereId($id)->delete();
        $this->osobaSrc->delete($idValues['osoba_id']);
        $this->adresaSrc->delete($idValues['koresp_adresa_id']);
    }

}
