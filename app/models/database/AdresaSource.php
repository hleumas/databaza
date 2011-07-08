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
class AdresaSource extends CommonSource
{

    protected function getTable()
    {
        return 'adresa';
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
        return new AdresaRecord(parent::getById($id));
    }

}
