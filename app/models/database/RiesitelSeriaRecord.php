<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * RiesitelSeria Record
 *
 * @authore Samuel
 * @package database
 */

class RiesitelSeriaRecord extends CommonRecord
{
    protected $_fields = array(
        'riesitel'    => array(true, 'custom'),
        'seria'       => array(true, 'custom'),
        'meskanie'    => array(true, 'custom'),
        'bonus'       => array(true, 'custom'),
        'obalky'      => array(true, 'integer'),
    );

    public function validate()
    {
        parent::validate();
        foreach ($this as $key => $elem) {
            if (!is_numeric($key)) {
                continue;
            }
            if (!parent::isIntegerValid($elem)) {
                throw new InvalidDataException("$elem is not valid body value");
            }
        }
    }
}
