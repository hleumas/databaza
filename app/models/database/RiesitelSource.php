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
        $this->adresaSrc = new AdresaSource($dbConnection);
        $this->skolaSrc  = new skolaSource($dbConnection);
        $this->osobaSrc  = new OsobaSource($dbConnection);
        $this->studiaSrc = new TypStudiaSource($dbConnection);
    }

    protected function getTable()
    {
        return 'riesitel';
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
        $data = array();
        $fetch = $this->whereId($id)->fetch();
        if ($fetch === false) {
            throw new InvalidIdException("Id $id was not found in database");
        }
        $data = FlatArray::toArray($fetch);

        foreach (array(
            'osoba' => $this->osobaSrc,
            'skola' => $this->skolaSrc,
            'koresp_adresa' => $this->adresaSrc,
            'typ_studia' => $this->studiaSrc) as $property => $source) {
            if ($data["{$property}_id"]) {
                $data[$property] = $source->getById($data["{$property}_id"]);
            }
        }

        return new RiesitelRecord($data);
    }

    /**
     * Insert record into db
     *
     * @param IRecord $record
     */
    public function insertDb($record)
    {
    }

    /**
     * Update record in the database
     *
     * @param IRecord $record
     */
    public function updateDb($record)
    {
    }

    /**
     * Delete record from the database
     *
     * @param string  $id
     * @param bool    $force
     */
    public function delete($id, $force)
    {
    }
}
