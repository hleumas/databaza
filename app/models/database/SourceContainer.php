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

class SourceContainer extends \Nette\DI\Container
{
    public function createServiceTypStudiaSource()
    {
        return new TypStudiaSource($this->params['database']);
    }

    public function createServiceAdresaSource()
    {
        return new AdresaSource($this->params['database']);
    }

    public function createServiceSkolaSource()
    {
        $src = new SkolaSource($this->params['database']);
        $src->setAdresaSource($this->adresaSource);
        return $src;
    }

    public function createServiceOsobaSource()
    {
        $src = new OsobaSource($this->params['database']);
        $src->setAdresaSource($this->adresaSource);
        return $src;
    }

    public function createServiceRiesitelSource()
    {
        $src = new RiesitelSource($this->params['database']);
        $src->setOsobaSource($this->osobaSource);
        $src->setAdresaSource($this->adresaSource);
        $src->setTypStudiaSource($this->typStudiaSource);
        $src->setSkolaSource($this->skolaSource);
        return $src;
    }

    public function createServiceSemesterSource()
    {
        return new SemesterSource($this->params['database'], $this->kategoria);
    }
    public function createServiceSeriaSource()
    {
        $src = new SeriaSource($this->params['database']);
        $src->setKategoria($this->kategoria);
        return $src;
    }

    public function createServicePrikladSource()
    {
        $src = new PrikladSource($this->params['database']);
        $src->setSeriaSource($this->seriaSource);
        return $src;
    }

    public function createServiceRiesitelSeriaSource()
    {
        return new RiesitelSeriaSource($this->params['database']);
    }


    public function createServiceKategoria()
    {
        $fetch = $this->params['database']
            ->table('kategoria')
            ->where('id', $this->params['kategoria_id'])
            ->fetch();
        $kategoria = new StdClass();
        $kategoria->id = $fetch['id'];
        $kategoria->nazov = $fetch['nazov'];
        $kategoria->pocet_casti = $fetch['pocet_casti'];
        $kategoria->aktualna_seria_id = $fetch['aktualna_seria_id'];
        return $kategoria;
    }
}
