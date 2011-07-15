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


    public function __construct($data = null)
    {
        foreach ($this->_fields as $field => $type) {
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
        foreach ($this->_fields as $field => $type) {
            if (!isset($data[$field]) && !array_key_exists($field, $data)) {
                continue;
            }
            if ($type[1] == 'object') {
                $this->setObjField($field, $data[$field], $type[2]);
            } else {
                $this[$field] = $data[$field] === '' ? null : $data[$field];
            }
        }
    }

    private function setObjField($field, $value, $class)
    {
        if (is_scalar($value) || is_null($value)) {
            $this[$field] = $value;
            return;
        }
        if (is_null($this[$field])) {
            $this[$field] = new $class();
        }
        $this[$field]->setData($value);
    }


    /**
     * Basic normalization based on trimming all values
     */
    public function normalize()
    {
        foreach ($this->_data as $key => $elem) {
            $type = $this->_fields[$key];
            if ($type[1] == 'object') {
                if ($elem instanceOf IRecord) {
                    $elem->normalize();
                }
                continue;
            }
            $callback = array('self', 'normalize' . ucfirst($type[1]));
            if (is_string($elem)) {
                $elem = Strings::trim($elem);
            }
            if ($elem === '') {
                $elem = null;
            }
            if (is_null($elem)) {
                continue;
            }
            if (method_exists(get_class(), $callback[1])) {
                $elem = call_user_func($callback, $elem);
            }
            $this->_data[$key] = $elem;
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
        foreach ($this->_fields as $field => $type) {
            if (is_null($this->_data[$field]) 
                || (is_string($this->_data[$field])
                && Strings::trim($this->_data[$field]) === '')
                ) {
                if ($type[0]) {
                    throw new InvalidDataException("The $field value in "
                        . get_class($this) . ' must not be empty');
                } else {
                    continue;
                }
            }

            $elem = $this[$field];
            if ($type[1] == 'object') {
                if ($this[$field] instanceOf IRecord) {
                    $elem->validate();
                } else {
                    continue;
                }
            }
            $callback = array('self', 'is' . ucfirst($type[1]) . 'Valid');
            if (method_exists(get_class(), $callback[1])) {
                if (!call_user_func($callback, $elem)) {
                    throw new InvalidDataException("$elem is not valid $field value");
                }
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
        return Strings::match($phone, '#^\s*[+]?\s*([0-9/]\s*){5,}$#');
    }

    public static function isEmailValid($email)
    {
        $atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
        $localPart = "(?:\"(?:[ !\\x23-\\x5B\\x5D-\\x7E]*|\\\\[ -~])+\"|$atom+(?:\\.$atom+)*)"; // quoted or unquoted
        $chars = "a-z0-9\x80-\xFF"; // superset of IDN
        $domain = "[$chars](?:[-$chars]{0,61}[$chars])"; // RFC 1034 one domain component
        return (bool) Strings::match($email, "(^$localPart@(?:$domain?\\.)+[-$chars]{2,19}\\z)i");
    }

    public static function isYearValid($year)
    {
        return Strings::match($year, '#^\s*(1\s*9|2\s*0)(\s*[0-9]){2}\s*$#');
    }

    public static function normalizeYear($year)
    {
        return (int)$year;
    }

    public static function isIntegerValid($integer)
    {
        return Strings::match($integer, '#^\s*([0-9]\s*)*$#');
    }
    public static function normalizeInteger($integer)
    {
        return (int)$integer;
    }

    public static function isPscValid($psc)
    {
        return Strings::match($psc, '/^\s*([0-9]\s*){5}$/');
    }

    public static function normalizePsc($psc)
    {
        return Strings::replace($psc, '/\s*/');
    }

    public static function validateDate($date)
    {
        return $date instanceOf \Nette\DateTime;
    }

}
