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
class SeriaRecord extends CommonRecord
{

    protected $_fields = array('id', 'cislo', 'termin', 'semester', 'aktualna');
    protected $_mandatory = array('termin');

    /**
     * Check the validity of data
     *
     * @throws InvalidDataException
     */
    public function validate()
    {
        parent::validate();
        if (!($this->data['termin'] instanceOf \Nette\DateTime)) {
            throw new InvalidDataException("{$this->data['termin']} must be instance of \Nette\DateTime");
        }
    }
}
