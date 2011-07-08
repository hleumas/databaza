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

use \Nette\Utils\Strings;
class OsobaRecord extends CommonRecord
{

    protected $_fields = array(
        'id',
        'meno',
        'priezvisko',
        'datum_narodenia',
        'adresa',
        'email',
        'telefon',
        'jabber'
    );

    protected $_object = array('AdresaRecord' => 'adresa');
    protected $_mandatory = array('meno', 'priezvisko');

    public function normalize()
    {
        parent::normalize();
    }
    /**
     * Check the validity of data
     *
     * @throws InvalidDataException
     */
    public function validate()
    {
        parent::validate();
        if (!is_null($this->_data['datum_narodenia']) 
            && !$this->_data['datum_narodenia'] instanceof \Nette\DateTime) {
                throw new InvalidDataException("datum_narodenia must be an DateTime instance");
        }

        if (!self::isPhoneValid($this->_data['telefon'])) {
            throw new InvalidDataException('Invalid phone format');
        }

        if (!self::isEmailValid($this->_data['email'])) {
            throw new InvalidDataException('Invalid email format');
        }
    }
}
