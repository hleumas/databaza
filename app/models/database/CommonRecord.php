<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * CommonRecord abstract class
 *
 * @authore Samuel
 * @package database
 */

use \Nette\Utils\Strings;
abstract class CommonRecord extends Nette\Object implements IRecord
{

    protected $_data;
    protected $_mandatory;
    /**
     * Retrieve data from the record
     *
     * @return array data
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Set only the data already defined by record
     *
     * @param array $data
     */
    public function setData($data)
    {
        foreach ($data as $key => $item) {
            if (array_key_exists($key, $this->_data)) {
                $this->_data[$key] = $item;
            }
        }
    }

    /**
     * Basic normalization based on trimming all values
     */
    public function normalize()
    {
        foreach ($this->_data as $key => $elem) {
            if (is_null($elem)) {
                continue;
            }
            if ($elem instanceOf IRecord) {
                $elem->normalize();
                continue;
            }
            if (is_string($elem)) {
                $this->_data[$key] = Strings::trim($elem);
            }
        }
    }

    /**
     * Validates the presence of mandatory properties and validity of nested 
     * records
     *
     * @throws InvalidDataException
     */
    public function validate()
    {
        foreach ($this->_mandatory as $key) {
            if (is_null($this->_data[$key]) ||
                (is_string($this->_data[$key]) && 
                Strings::trim($this->_data[$key]) == '')) {
                    throw new InvalidDataException("The $key value in "
                        . get_class($this) . " must not be empty");
            }
        }

        foreach ($this->_data as $elem) {
            if ($elem instanceOf IRecord) {
                $elem->validate();
            }
        }
    }
}
