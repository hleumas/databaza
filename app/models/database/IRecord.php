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

interface IRecord
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
     * Get the data
     *
     * @return array
     */
    public function getData();

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
