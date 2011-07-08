<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * RiesitelSource
 *
 * @authore Samuel
 * @package database
 */

use \Nette\Database\Connection;
abstract class CommonSource implements ISource
{
    private $_dbConnection;
    private $table;
    private $propertySource = array();
    /**
     * Source constructor
     *
     * @param \Nette\Database\Connection $dbConnection
     */
    public function __construct($dbConnection)
    {
        $this->_dbConnection = $dbConnection;
    }

    protected function addPropertySource($name, $source)
    {
        $this->propertySource[$name] = $source;
    }

    public function getConnection()
    {
        return $this->_dbConnection;
    }

    protected function whereId($id)
    {
        return $this->_dbConnection->table($this->getTable())
            ->where('id', $id);
    }

    protected function getTable()
    {
        return $this->table;
    }

    protected function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Get the record by id
     *
     * @param string $id
     *
     * @return array
     */
    public function getById($id)
    {
        $fetch = $this->whereId($id)->fetch();
        if ($fetch === false) {
            throw new InvalidIdException("Id $id was not found in database");
        }
        $data = FlatArray::toArray($fetch);
        foreach ($this->propertySource as $property => $source) {
            $property_id = "{$property}_id";
            if (!isset($data[$property_id]) || is_null($data[$property_id])) {
                continue;
            }
            $data[$property] = $source->getById($data[$property_id]);
            unset($data[$property_id]);
        }
        return $data;
    }

    /**
     * Insert record into db
     *
     * @param IRecord $record
     *
     * @return int last id
     */
    public function insert($record)
    {
        $record->validate();
        $record->normalize();
        return $this->insertDb($record);
    }

    /**
     * Update record in the database
     *
     * @param IRecord $record
     * @return int last id
     */
    public function update($record)
    {
        $record->validate();
        $record->normalize();
        return $this->updateDb($record);
    }

    private function saveSubrecords($record, $idValues = null)
    {
        foreach ($this->propertySource as $property => $source) {
            $property_id = "{$property}_id";
            if ($record[$property] instanceOf IRecord) {
                if (is_null($idValues)) {
                    $record[$property_id] = $source->insert($record[$property]);
                } else {
                    $record[$property]['id'] = $idValues[$property_id];
                    $record[$property_id] = $source->update($record[$property]);
                }
            } else {
                $record[$property_id] = $record[$property];
            }
            unset($record[$property]);
        }
    }

    protected function insertDb($record)
    {
        $this->saveSubrecords($record);
        $this->_dbConnection->table($this->getTable())->insert($record);
        return $this->_dbConnection->lastInsertId();
    }

    private function getIdValues($id)
    {
        if (empty($this->propertySource)) {
            return array();
        }
        $idColumns = implode('_id,', array_keys($this->propertySource)) . '_id';
        return $this->whereId($id)->select($idColumns)->fetch();
    }

    protected function updateDb($record)
    {
        $id = $record['id'];
        $this->saveSubrecords($record, $this->getIdValues($id));
        $this->whereId($id)->update($record);
        return $id;
    }

    /**
     * Delete record from the database
     *
     * @param string  $id
     * @param bool    $force
     */
    public function delete($id, $force = false)
    {
        if (is_null($id)) {
            return;
        }
        $idValues = $this->getIdValues($id);
        $this->whereId($id)->delete();
        foreach ($this->propertySource as $property => $source) {
            $property_id = "{$property}_id";
            $source->delete($idValues[$property_id]);
        }
    }
}
