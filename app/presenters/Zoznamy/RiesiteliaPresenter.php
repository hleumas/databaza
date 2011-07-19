<?php

/**
 * Databaza FKS
 *
 * @package    Presenters
 */



use Gridito\Grid;
use Gridito\NetteModel;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Utils\Neon;
/**
 * Zoznamy presenter.
 *
 * @author     Samuel
 * @package    Presenters
 */
class RiesiteliaPresenter extends ZoznamyPresenter
{

    public function createComponentForm()
    {
        $form = parent::createComponentForm();
        $studia = $this->context->sources->typStudiaSource->getAll();
        $stuSelect = array();
        foreach ($studia as $id => $studium) {
            $stuSelect[$id] = $studium['nazov'];
        }
        $form['typ_studia']->setItems($stuSelect);
        $form['typ_studia']->setPrompt('Zvoľte typ štúdia');

        $dataSkoly = $this->context->database
            ->table('zoznamy_skola_view')
            ->select('id, nazov, mesto')
            ->order('mesto ASC, nazov ASC')
            ->fetchPairs('id');
        $skoly = array();
        foreach ($dataSkoly as $skola) {
            $mesto = $skola['mesto'];
            if (!isset($skoly[$mesto])) {
                $skoly[$skola['mesto']] = array();
            }
            $nazov = $skola['nazov'];
            /*$nazov = Strings::truncate($skola['nazov'], 28);
            if (strlen($skola['nazov']) > 28) {
                $nazov = Html::el('option', $nazov)
                    ->title($skola['nazov'])
                    ->value($skola['id']);
            }*/
            $skoly[$mesto][$skola['id']] = $nazov;
        }
        
        $form['skola']->setItems($skoly);
        $form['skola']->setPrompt('Zvoľte školu');
        $form['skola']->getControlPrototype()->class[] = 'chosen';
        return $form;
    }

    public function getGridSource()
    {
        return $this->context->database->table('zoznamy_riesitel_view');
    }
    public function getData($id)
    {
        $source = $this->context->sources->riesitelSource;
        $record = $source->getById($id);
        $data = FlatArray::deflate($record);
        foreach (array('typ_studia', 'skola') as $key) {
            if (isset($data["$key.id"])) {
                $data[$key] = $data["$key.id"];
            } else {
                unset($data[$key]);
            }
        }
        return $data;
    }

    public function delete($row)
    {
        try {
            $this->context->sources->riesitelSource->delete($row['id']);
            $this['grid']->flashMessage("Riešiteľ {$row['meno']} {$row['priezvisko']} odstránený");
        } catch (DBIntegrityException $e) {
            $this['grid']->flashMessage($e->getMessage(), 'error');
        }
    }

    public function onSubmit()
    {
        $sources = $this->context->sources;
        $form = $this['form'];
        $record = new RiesitelRecord(FlatArray::inflate($form->values));
        if ($record['koresp_kam'] != RiesitelRecord::KORESP_ELSE) {
            $record['koresp_adresa'] = null;
        } else {
            $record['koresp_adresa']['stat'] = 'SR';
        }
        $record['osoba']['adresa']['stat'] = 'SR';
        $record['osoba']['datum_narodenia'] = new \Nette\DateTime($record['osoba']['datum_narodenia']);
        $record['typ_studia'] = $sources->typStudiaSource->getById($record['typ_studia']);
        $record['datum'] = new \Nette\DateTime();

        $osoba = $record['osoba'];
        if (!empty($record['id'])) {
            $sources->riesitelSource->update($record);
            $this['grid']->flashMessage("Zmenený riešiteľ {$osoba['meno']} {$osoba['priezvisko']}");
        } else {
            $sources->riesitelSource->insert($record);
            $this['grid']->flashMessage("Pridaný riešiteľ {$osoba['meno']} {$osoba['priezvisko']}");
        }

        $this->redirect('this');
            
    }

}
