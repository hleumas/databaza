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

    const MATURITA_SUFFIX = '-06-31';
    const KORESP_DOMOV = 0;
    const KORESP_SKOLA = 1;
    const KORESP_ELSE  = 2;
    protected $_fields = array(
        'id',
        'osoba',
        'skola',
        'rok_maturity',
        'rocnik',
        'datum',
        'typ_studia',
        'telefon_rodic',
        'koresp_kam',
        'koresp_adresa'
    );

    protected $_object = array('osobaRecord' => 'osoba', 'SkolaRecord' => 'skola', 'AdresaRecord' => 'koresp_adresa');
    protected $_mandatory = array('osoba', 'skola', 'rok_maturity', 'typ_studia', 'koresp_kam');


    public function updateRocnik()
    {
        if (!($this['datum'] instanceOf \Nette\DateTime)
            || is_null($this['typ_studia']) 
            || !is_numeric($this['rok_maturity'])
            || !is_numeric($this['typ_studia']['maturitny_rocnik'])) {
            return;
        }
        $maturita = new \Nette\DateTime($this['rok_maturity'] . self::MATURITA_SUFFIX);
        $this->_data['rocnik'] = 
            $this['typ_studia']['maturitny_rocnik']
            - floor($this['datum']->diff($maturita)->days / 365.25);
    }

    public function updateRok_maturity()
    {
        if (!($this['datum'] instanceOf \Nette\DateTime)
            || is_null($this['typ_studia']) 
            || !is_numeric($this['rocnik'])
            || !is_numeric($this['typ_studia']['maturitny_rocnik'])) {
            return;
        }
        $month = $this['datum']->format('n');
        $year  = $this['datum']->format('Y');
        if ($month > 6) {
            $year++;
        }
        $this->_data['rok_maturity'] = 
            $year
            + $this['typ_studia']['maturitny_rocnik']
            - $this['rocnik'];
    }

    public function updateMissing()
    {
        if (!is_null($this['rok_maturity'])) {
            $this->updateRocnik();
        } elseif (!is_null($this['rocnik'])) {
            $this->updateRok_maturity();
        }
    }

    public function setRok_maturity($rok_maturity)
    {
        $this->_data['rok_maturity'] = $rok_maturity;
        $this->updateRocnik();
    }

    public function setRocnik($rocnik)
    {
        $this->_data['rocnik'] = $rocnik;
        $this->updateRok_maturity();
    }

    public function setTyp_studia($typ_studia)
    {
        $this->_data['typ_studia'] = $typ_studia;
        $this->updateMissing();
    }

    public function setDatum($datum)
    {
        $this->_data['datum'] = $datum;
        $this->updateMissing();
    }
    /**
     * Check the validity of data
     *
     * @throws InvalidDataException
     */
    public function validate()
    {
        parent::validate();
        $data = $this->_data;
        if (!is_numeric($data['rok_maturity'])
            || $data['rok_maturity'] < 1980
            || $data['rok_maturity'] > 2030) {
            throw new InvalidDataException("{$data['rok_maturity']} is not valid rok_maturity");
        }

        if (!self::isPhoneValid($data['telefon_rodic'])) {
            throw new InvalidDataException('Invalid telefon_rodic format');
        }

        if (!self::isYearValid($data['rok_maturity'])) {
            throw new InvalidDataException("{$data['rok_maturity']} is not valid year");
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
