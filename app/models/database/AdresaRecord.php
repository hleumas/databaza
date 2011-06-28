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

    protected $_data = array(
        'id' => null,
        'organizacia' => null,
        'ulica' => null,
        'psc' => null,
        'mesto' => null,
        'stat' => null
    );

    protected $_fields = array('id', 'organizacia', 'ulica', 'psc',
        'mesto', 'stat');
    protected $_mandatory = array('ulica', 'psc', 'mesto', 'stat');

    public function normalize()
    {
        parent::normalize();
        if (!is_null($this->_data['psc'])) {
            $this->_data['psc'] = Strings::replace($this->_data['psc'], '/\s*/');
        }
    }
    /**
     * Check the validity of data
     *
     * @throws InvalidDataException
     */
    public function validate()
    {
        parent::validate();

        if (!Strings::match($this->_data['psc'], '/^\s*([0-9]\s*){5}$/')) {
            throw new InvalidDataException("{$this->_data['psc']} is not valid psc value");
        }
    }
}
