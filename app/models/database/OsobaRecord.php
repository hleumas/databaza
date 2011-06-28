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

    protected $_data = array(
        'id',
        'meno',
        'priezvisko',
        'datum_narodenia',
        'adresa',
        'email',
        'telefon',
        'jabber'
    );

    protected $_object = array('adresa');
    protected $_mandatory = array('meno', 'priezvisko');

    public function normalize()
    {
        parent::normalize();
        $this->_data['telefon'] = self::normalize($this->_data['telefon']);
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
            && !instanceof(\Nette\DateTime) {
                throw new InvalidDataException("datum_narodenia must be an DateTime instance");
        }

        if (!self::isPhoneValid($this->_data['telefon'])) {
            throw new InvalidDataException('Invalid phone format');
        }

        if (!\Nette\Forms\Controls\TextBase::validateEmail($this->_data['email']) {
            throw new InvalidDataException('Invalid email format');
        }

        if (!Strings::match($this->_data['psc'], '/^\s*([0-9]\s*){5}$/')) {
            throw new InvalidDataException("{$this->_data['psc']} is not valid psc value");
        }
    }
}
