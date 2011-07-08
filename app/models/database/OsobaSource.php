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
        $this->setTable('osoba');
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
     * @return OsobaRecord
     */
    public function getById($id)
    {
        return new OsobaRecord(parent::getById($id));
    }

}
