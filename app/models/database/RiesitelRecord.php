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

class RiesitelRecord extends CommonRecord
{

    const KORESP_DOMOV = 0;
    const KORESP_SKOLA = 1;
    const KORESP_ELSE  = 2;
    protected $_fields = array(
        'id',
        'osoba',
        'skola',
        'rok_maturity',
        'typ_studia',
        'telefon_rodic',
        'koresp_kam',
        'koresp_adresa'
    );

    protected $_object = array('osobaRecord' => 'osoba', 'SkolaRecord' => 'skola', 'AdresaRecord' => 'koresp_adresa');
    protected $_mandatory = array('osoba', 'skola', 'rok_maturity', 'typ_studia', 'koresp_kam');
    /**
     * Check the validity of data
     *
     * @throws InvalidDataException
     */
    public function validate()
    {
        $data = $this->_data;
        if (!is_numeric($data['rok_maturity'])
            || $data['rok_maturity'] < 1980
            || $data['rok_maturity'] > 2030) {
            throw new InvalidDataException('Invalid rok_maturity format');
        }

        if (!self::isPhoneValid($data['telefon_rodic'])) {
            throw new InvalidDataException('Invalid telefon_rodic format');
        }

        if (!is_numeric($data['koresp_kam'])
            || $data['koresp_kam'] < 0
            || $data['koresp_kam'] > 2) {
            throw new InvalidDataException('Invalid koresp_kam value');
        }

        if ($data['koresp_kam'] == self::KORESP_ELSE && is_null($data['koresp_adresa'])) {
            throw new InvalidDataException('koresp_adresa must be set with KORESP_ELSE');
        }

    }
}
