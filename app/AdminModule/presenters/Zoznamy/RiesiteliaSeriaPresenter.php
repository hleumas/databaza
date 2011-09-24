<?php

/**
 * Databaza FKS
 *
 * @package    Presenters
 */



use Gridito\Grid;
use Gridito\NetteModel;
use Nette\Utils\Html;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;
/**
 * Zoznamy presenter.
 *
 * @author     Samuel
 * @package    Presenters
 */
class RiesiteliaSeriaPresenter extends ZoznamyPresenter
{

    /** @persistent */
    public $seria;

    public function createComponentSeriaSelector()
    {
        $sources = $this->context->sources;
        return new SeriaSelector(
            $this->seria,
            $sources->seriaSource,
            $sources->semesterSource,
            $sources->kategoria);
    }

    public function createGridModel()
    {
        return new RiesiteliaSeriaModel(
            $this->context->database,
            $this['seriaSelector']->seria
        );
    }

    public function createComponentGrid()
    {
        $grid = parent::createComponentGrid();
        $kody = $grid->getModel()->getKody();

        foreach ($kody as $priklad) {
            $grid->addColumn($priklad['cislo'], "{$priklad['kod']}")
                ->setEditable(true)
                ->setRenderer(function($row) use ($priklad) {
                if (!isset($row->$priklad['cislo'])) {return '';}
                return $row->$priklad['cislo'] < 0 ? '*' : $row->$priklad['cislo'];});
        }
        $grid->addColumn('meskanie', 'Meškanie')->setEditable(true);
        $grid->addColumn('bonus', 'Bonus')->setEditable(true);
        return $grid;
    }

    public function setGridHandlers($grid)
    {
        parent::setGridHandlers($grid);
        $grid->setEditHandler(callback($this, 'handleEdit'));
        return $grid;
    }

    public function handleEdit($post)
    {
        $grid = $this['grid'];
        $source = $this->context->sources->riesitelSeriaSource;
        $seria = $this['seriaSelector']->seria;
        $riesitel = $post['id'];

        foreach ($post as $key=>$value) {
            $value = Strings::trim($value);
            if (is_numeric($key)) {
                if ($value === '') {
                    $source->deletePriklad($riesitel, $seria, $key);
                    $grid->flashMessage("Príklad číslo $key bol riešiteľovi odstránený");
                } else {
                    $value = RiesitelSeriaRecord::isIntegerValid($value) ? $value : null;
                    $source->setPriklad($riesitel, $seria, $key, $value);
                    $grid->flashMessage("Riešiteľovi boli zmenené body za príklad číslo $key");
                    if (is_null($value)) {
                        $grid->invalidateControl();
                    }
                }
            } else if ($key === 'meskanie') {
                $value = $value === '' ? 0 : $value;
                if (!RiesitelSeriaRecord::isIntegerValid($value, 0)) {
                    $grid->flashMessage('Nekorektná hodnota pre meškanie');
                    $grid->invalidateControl();
                } else {
                    $source->updateMeskanie($riesitel, $seria, $value);
                    $grid->flashMessage('Riešiteľovi bolo upravené meškanie');
                }
            } else if ($key === 'bonus') {
                $value = $value === '' ? 0 : $value;
                if (!is_numeric($value)) {
                    $grid->flashMessage('Nekorektná hodnota pre bonus');
                    $grid->invalidateControl();
                } else {
                    $source->updateBonus($riesitel, $seria, $value);
                    $grid->flashMessage('Riešiteľovi bol upravený bonus');
                }
            }
        }

        $grid->invalidateControl('flashes');
    }

    public function createComponentForm()
    {
        $form = parent::createComponentForm();
        $dataRiesitelia = $this->context->database
            ->table('zoznamy_riesitel_view')
            ->select('id, meno, priezvisko')
            ->order('priezvisko ASC, meno ASC')
            ->fetchPairs('id');
        $riesitelia = array();
        foreach ($dataRiesitelia as $riesitel) {
            $riesitelia[$riesitel['id']] =
                "{$riesitel['meno']} {$riesitel['priezvisko']}";
        }
        
        $form['riesitel']->setItems($riesitelia);
        $form['riesitel']->setPrompt('Zvoľte riešiteľa');
        $form['riesitel']->getControlPrototype()->class[] = 'chosen';
        $form['meskanie']->defaultValue = 0;
        $form['bonus']->defaultValue = 0;
        $form['seria']->value = $this['seriaSelector']->seria;

        $dataPriklady = $this->context->database
            ->table('priklad')
            ->select('id, cislo, kod, nazov')
            ->where('seria_id', $this['seriaSelector']->seria)
            ->order('cislo ASC')
            ->fetchPairs('id');

        foreach ($dataPriklady as $priklad) {
            $form->addCheckBox("priklad.{$priklad['cislo']}", "{$priklad['kod']}");
        }
        $form->addSubmit('odosli', 'Odobálkuj');

        return $form;
    }

    public function getData($id)
    {
        /*
        $data = $this->context->sources->PrikladSource->getById($id);
        return $data;
         */
    }

    public function delete($row)
    {
        try {
            $this->context->sources->riesitelSeriaSource->delete($row->id, $this['seriaSelector']->seria);
            $this['grid']->flashMessage("Zrušené odobálkovanie riešiteľa {$row['meno']} {$row['priezvisko']}");
        } catch (DBIntegrityException $e) {
            $this['grid']->flashMessage($e->getMessage(), 'error');
        }
        $this['grid']->invalidateControl();
    }

    public function onSubmit()
    {
        $riesitelSource = $this->context->sources->riesitelSeriaSource;
        $data = FlatArray::inflate($this['form']->getValues());
        $record = new RiesitelSeriaRecord($data);
        foreach ($data['priklad'] as $key => $priklad) {
            if ($priklad) {
                $record[$key] = null;
            }
        }

        $this->context->sources->riesitelSeriaSource->insert($record);
        $this['grid']->flashMessage('Odobálkovaný riešiteľ');
        $this->redirect('this');
    }
}
