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

    protected $_data = array();
    protected $_fields = array();
    protected $_mandatory = array();
    protected $_object = array();


    public function __construct()
    {
        foreach ($this->_fields as $field) {
            $this->_data[$field] = null;
        }
    }

    public function offsetExists($offset)
    {
        return array_key_exists($this->_data);
    }

    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }

    public function offsetSet($offset, $data)
    {
        $this->_data[$offset] = $data;
    }

    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    public function rewind()
    {
        return reset($this->_data);
    }

    public function current()
    {
        return current($this->_data);
    }

    public function key()
    {
        return key($this->_data);
    }

    public function next()
    {
        return next($this->_data);
    }

    public function valid()
    {
        return key($this->_data) !== null;
    }

    /**
     * Set only the data already defined by record
     *
     * @param array $data
     */
    public function setData($data)
    {
        foreach ($this->_object as $class => $objField) {
            if (array_key_exists($objField, $data)) {
                if (is_null($data[$objField])) {
                    $this->_data[$objField] = null;
                } else {
                    if (is_null($this->_data[$objField])) {
                        $this->_data[$objField] = new $class();
                    }
                    $this->_data[$objField]->setData($data[$objField]);
                }
            }
        }

        foreach ($this->_fields as $field) {
            if (!array_key_exists($field, $data)
                || is_object($this->_data[$field])) {
                continue;
            }
            $this->_data[$field] = $data[$field];
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

    public static function normalizePhone($phone)
    {
        if (!is_null($phone)) {
            return Strings::replace($phone, '/\s*/');
        }
        return null;
    }
    public static function isPhoneValid($phone)
    {
        if (is_null($phone)) {
            return true;
        }
        if (!Strings::match($phone, '#^\s*([0-9/]\s){5,}$#')) {
            return false;
        }
        return true;
    }
}
