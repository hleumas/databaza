<?php
/**
 * Databaza FKS
 *
 * @author  Samuel
 * @package Form
 */

/**
 * RiesitelForm
 *
 * @author  Samuel
 * @package Form
 */

use Nette\Utils\Html;
use Nette\Utils\Neon;
use Nette\Application\UI\Form;

class RiesitelForm extends Form
{

    private $_definitionFile = '/Form/riesitelForm.neon';
    private $_name;

    public function __construct($name, $skoly, $studia)
    {
        $this->_name = $name;
        parent::__construct();
        $file = APP_DIR . $this->_definitionFile;
        if (!is_file($file) || !is_readable($file)) {
            throw new \Nette\FileNotFoundException("File $file is missing or is not readable.");
        }
        \NeonFormFactory::createForm(file_get_contents($file), $this);
        $this['typ_studia']->setItems($this->getStudia($studia));
        $this['typ_studia']->setPrompt('Zvoľte typ štúdia');

        $this['skola']->setItems($this->getSkoly($skoly));
        $this['skola']->setPrompt('Zvoľte školu');
        $this['skola']->getControlPrototype()->class[] = 'chosen';

        /** Validation */
        $this['osoba.adresa.psc']->addRule(
            Form::PATTERN,
            'PSČ má byť päťciferné číslo',
            '^(\s*[0-9]){5}\s*$'
        );
        $this['osoba.email']
            ->setType('email')
            ->addCondition(Form::FILLED)
            ->addRule(
            Form::EMAIL,
            'Neplatný email'
        );
        $this['osoba.telefon']->addRule(
            Form::PATTERN,
            'Zadajte platné telefónne číslo',
            '^\s*[+]?(\s*[0-9/])*\s*$'
        );
        $this['telefon_rodic']->addRule(
            Form::PATTERN,
            'Zadajte platné telefónne číslo',
            '^\s*[+]?(\s*[0-9/])*\s*$'
        );

        $this['rocnik']
            ->setType('number')
            ->addRule(
                FORM::RANGE,
                'Ročník musí byť číslo od 1 do 10',
                array(1, 10)
            );

        $prefix = $this->getPrefix();
        $this['koresp_kam']->addCondition(FORM::EQUAL, '2')
            ->toggle("$prefix-koresp_adresa.ulica-dt")
            ->toggle("$prefix-koresp_adresa.ulica-dd")
            ->toggle("$prefix-koresp_adresa.mesto-dt")
            ->toggle("$prefix-koresp_adresa.mesto-dd")
            ->toggle("$prefix-koresp_adresa.psc-dt")
            ->toggle("$prefix-koresp_adresa.psc-dd");
        $this['koresp_adresa.ulica']
            ->addConditionOn($this['koresp_kam'], FORM::EQUAL, '2')
            ->addRule(FORM::FILLED, 'Vyplň korešpondenčnú adresu');

        $this['koresp_adresa.mesto']
            ->addConditionOn($this['koresp_kam'], FORM::EQUAL, '2')
            ->addRule(FORM::FILLED, 'Vyplň korešpondenčnú adresu');

        $this['koresp_adresa.psc']
            ->addConditionOn($this['koresp_kam'], FORM::EQUAL, '2')
            ->addRule(FORM::FILLED, 'Vyplň PSČ');


        $this['koresp_adresa.psc']
            ->addCondition(Form::FILLED)
            ->addRule(
            Form::PATTERN,
            'PSČ má byť päťciferné číslo',
            '^(\s*[0-9]){5}\s*$'
        );

    }

    private function getPrefix()
    {
        return 'frm' . $this->_name;
    }
    private function getSkoly($dataSkoly)
    {
        $skoly = array();
        foreach ($dataSkoly as $skola) {
            $mesto = $skola['mesto'];
            if (!isset($skoly[$mesto])) {
                $skoly[$skola['mesto']] = array();
            }
            $nazov = $skola['nazov'];
            $skoly[$mesto][$skola['id']] = $nazov;
        }

        return $skoly;
    }

    private function getStudia($dataStudia)
    {
        $studia = array();
        foreach ($dataStudia as $id => $studium) {
            $studia[$id] = $studium['nazov'];
        }
        return $studia;
    }
}
