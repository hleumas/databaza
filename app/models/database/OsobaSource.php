<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * OsobaSource
 *
 * @authore Samuel
 * @package database
 */

use \Nette\Database\Connection;
class OsobaSource extends CommonSource
{

    private $adresaSrc;
    public function __construct($dbConnection)
    {
        parent::__construct($dbConnection);
        $this->adresaSrc = new AdresaSource($dbConnection);
        
    }
    protected function getTable()
    {
        return 'osoba';
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
        if ($data['adresa_id']) {
            $data['adresa'] = $this->adresaSrc->getById($data['adresa_id']);
        }
        return new OsobaRecord($data);
    }

    /**
     * Insert record into db
     *
     * @param IRecord $record
     *
     * @return int last id
     */
    protected function insertDb($record)
    {
        $data = $record->getData();

        $conn = $this->getConnection();
        $adresa = new AdresaRecord($data['adresa']);
        $data['adresa_id'] = $this->adresaSrc->insert($adresa);
        unset($data['adresa']);
        $conn->table($this->getTable())->insert($data);
        return $conn->lastInsertId();
    }

    /**
     * Update record in the database
     *
     * @param IRecord $record
     */
    public function updateDb($record)
    {
        $data = $record->getData();
        $id = $data['id'];

        $adresa = new AdresaRecord($data['adresa']);
        $adresa_row = $this->whereId($id)->select('adresa_id')->fetch();
        $adresa['id'] = $adresa_row['adresa_id'];
        $this->adresaSrc->update($adresa);

        unset($data['adresa']);
        $data['adresa_id'] = $adresa['id'];
        $this->whereId($id)->update($data);
    }

    /**
     * Delete record from the database
     *
     * @param string  $id
     * @param bool    $force
     */
    public function delete($id, $force = false)
    {
        $adresa_row = $this->whereId($id)->select('adresa_id')->fetch();
        $this->whereId($id)->delete();
        $this->adresaSrc->delete($adresa_row['adresa_id']);
    }
}
