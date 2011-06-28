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

interface IRecord extends ArrayAccess, Iterator
{

    /**
     * Check the validity of data
     *
     * @throws InvalidDataException
     */
    public function validate();

    /**
     * Normalize the data
     */
    public function normalize();

    /**
     * Set the data
     *
     * @param array $data
     */
    public function setData($data);
}

class InvalidDataException extends \Exception
{
}
