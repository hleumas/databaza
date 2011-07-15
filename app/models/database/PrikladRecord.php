<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * Priklad Record
 *
 * @authore Samuel
 * @package database
 */

class PrikladRecord extends CommonRecord
{
    protected $_fields = array(
        'id'          => array(false, 'custom'),
        'seria'       => array(true, 'custom'),
        'nazov'       => array(true, 'custom'),
        'body'        => array(true, 'integer'),
        'opravovatel' => array(false, 'custom'),
        'vzorakovac'  => array(false, 'custom'),
        'poznamka'    => array(false, 'custom')
    );
}
