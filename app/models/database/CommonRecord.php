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


    public function __construct($data = null)
    {
        foreach ($this->_fields as $field) {
            $this->_data[$field] = null;
        }
        if (!is_null($data)) {
            $this->setData($data);
        }
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }

    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }

    public function offsetSet($offset, $data)
    {
        if (method_exists($this, 'set' . ucfirst($offset))) {
            call_user_func(array($this, 'set' . ucfirst($offset)), $data);
        } else {
            $this->_data[$offset] = $data;
        }
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
        foreach ($this->_object as $class => $objField) {
            if (isset($data[$objField]) || array_key_exists($objField, $data)) {
                if (is_scalar($data[$objField]) || is_null($data[$objField])) {
                    $this[$objField] = $data[$objField];
                } else {
                    if (is_null($this[$objField])) {
                        $this[$objField] = new $class();
                    }
                    $this[$objField]->setData($data[$objField]);
                }
            }
        }

        foreach ($this->_fields as $field) {
            if ((!array_key_exists($field, $data) && !isset($data[$field]))
                || is_object($this[$field])) {
                continue;
            }
            if ($data[$field] === '') {
                $data[$field] = null;
            }
            $this[$field] = $data[$field];
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
        if (!Strings::match($phone, '#^\s*[+]?\s*([0-9/]\s*){5,}$#')) {
            return false;
        }
        return true;
    }

    public static function isEmailValid($email)
    {
        if (is_null($email)) {
            return true;
        }
        $atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
        $localPart = "(?:\"(?:[ !\\x23-\\x5B\\x5D-\\x7E]*|\\\\[ -~])+\"|$atom+(?:\\.$atom+)*)"; // quoted or unquoted
        $chars = "a-z0-9\x80-\xFF"; // superset of IDN
        $domain = "[$chars](?:[-$chars]{0,61}[$chars])"; // RFC 1034 one domain component
        return (bool) Strings::match($email, "(^$localPart@(?:$domain?\\.)+[-$chars]{2,19}\\z)i");
    }

    public static function isYearValid($year)
    {
        if (is_null($year)) {
            return true;
        }
        if (!Strings::match($year, '#^\s*(1\s*9|2\s*0)(\s*[0-9]){2}\s*$#')) {
            return false;
        }
        return true;
    }
}
