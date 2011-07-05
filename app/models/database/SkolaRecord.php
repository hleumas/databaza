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
class SkolaRecord extends CommonRecord
{

    protected $_fields = array (
        'id',
        'nazov',
        'skratka',
        'adresa',
        'email',
        'telefon',
        'zakladna',
        'stredna'
    );

    protected $_object = array('AdresaRecord' => 'adresa');
    protected $_mandatory = array('nazov', 'skratka', 'zakladna', 'stredna', 'adresa');

    /**
     * Check the validity of data
     *
     * @throws InvalidDataException
     */
    public function validate()
    {
        parent::validate();

        if (!is_bool($this->_data['zakladna'])) {
            throw new InvalidDataException('zakladna must be boolean');
        }

        if (!is_bool($this->_data['stredna'])) {
            throw new InvalidDataException('stredna must be boolean');
        }

        if (!self::isPhoneValid($this->_data['telefon'])) {
            throw new InvalidDataException('Invalid phone format');
        }

        if (!self::isEmailValid($this->_data['email'])) {
            throw new InvalidDataException('Invalid email format');
        }

    }

}
