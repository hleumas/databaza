<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * Record Interface
 *
 * @authore Samuel
 * @package database
 */

class SeriaRecord extends CommonRecord
{
    protected $_fields = array(
        'id' => array(false, 'custom'),
        'cislo' => array(false, 'integer'),
        'termin' => array(true, 'date'),
        'semester' => array(false, 'custom'),
        'aktualna' => array(false, 'bool')
    );
}
