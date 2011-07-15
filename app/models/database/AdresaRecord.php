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

use \Nette\Utils\Strings;
class AdresaRecord extends CommonRecord
{
    protected $_fields = array(
        'id'          => array(false, 'custom'),
        'organizacia' => array(false, 'custom'),
        'ulica'       => array(true, 'custom'),
        'psc'         => array(true, 'psc'),
        'mesto'       => array(true, 'mesto'),
        'stat'        => array(true, 'stat')
    );
}
