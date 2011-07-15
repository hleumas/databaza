<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * Osoba Record
 *
 * @authore Samuel
 * @package database
 */

class OsobaRecord extends CommonRecord
{
    protected $_fields = array(
        'id'              => array(false, 'custom'),
        'meno'            => array(true, 'custom'),
        'priezvisko'      => array(true, 'custom'),
        'datum_narodenia' => array(false, 'date'),
        'adresa'          => array(false, 'object', 'AdresaRecord'),
        'email'           => array(false, 'email'),
        'telefon'         => array(false, 'phone'),
        'jabber'          => array(false, 'custom') 
    );
}
