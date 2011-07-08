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
}
