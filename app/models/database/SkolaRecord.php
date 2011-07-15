<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * Skola Record
 *
 * @authore Samuel
 * @package database
 */

class SkolaRecord extends CommonRecord
{
    protected $_fields = array (
        'id'       => array(false, 'custom'),
        'nazov'    => array(true, 'custom'),
        'skratka'  => array(true, 'custom'),
        'adresa'   => array(true, 'object', 'AdresaRecord'),
        'email'    => array(false, 'email'),
        'telefon'  => array(false, 'phone'),
        'zakladna' => array(true, 'bool'),
        'stredna'  => array(true, 'bool')
    );
}
