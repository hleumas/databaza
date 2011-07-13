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
class SeriePresenter extends ZoznamyPresenter
{

    private $semesterId = null;
    public function getSemesterId()
    {
        if (is_null($this->semesterId)) {
            $semester = $this->getParam('semester', false);
            if ($semester === false) {
                $seria = $this->context->sources->seriaSource->getAktualna();
                if ($seria === false) {
                    $semester = $this->context->sources->SemesterSource->getLastId();
                } else {
                    $semester = $seria['semester'];
                }
            }
            $this->semesterId = $semester;
        }
        return $this->semesterId;
    }

    public function getGridSource()
    {
        return $this->context->database
            ->table('seria')
            ->where('semester_id', $this->getSemesterId())
            ->order('cislo DESC');
    }

    public function setGridHandlers($grid)
    {
        $aktualna = &$this->context->sources->kategoria->aktualna_seria_id;
        $source = $this->context->sources;
        $seriaSource = $this->context->sources->seriaSource;
        $grid['actions']->getComponent('aktualna')->SetChecked(
            function($row) use (&$aktualna) {
                return ($row['id'] == $aktualna);
            })->setEnabled(
            function($row) use (&$aktualna) {
                return ($row['id'] != $aktualna);
            }
            )->setHandler(
                function($row) use ($seriaSource) {
                    $seriaSource->setAktualna($row['id']);
                }
            );
        return parent::setGridHandlers($grid);
    }

    public function getData($id)
    {
        $data = $this->context->sources->SeriaSource->getById($id);
        return $data;
    }

    public function delete($row)
    {
        try {
            $this->context->sources->seriaSource->delete($row['id']);
            $this['grid']->flashMessage('Séria odstránená');
        } catch (DBIntegrityException $e) {
            $this['grid']->flashMessage($e->getMessage(), 'error');
        }
    }

    public function onSubmit()
    {
        $form = $this['grid']['form'];
        $data = $form->getValues();
        $data['termin'] = new \Nette\DateTime($data['termin']);
        $data['semester'] = $this->getSemesterId();
        $record = new SeriaRecord($data);
        if (!empty($record['id'])) {
            $this->context->sources->seriaSource->update($record);
            $this['grid']->flashMessage("Zmenená séria");
        } else {
            $this->context->sources->seriaSource->insert($record);
            $this['grid']->flashMessage("Pridaná séria");
        }
        $this->redirect('this');
    }

    public function createComponentSemesterSelect()
    {
        $form = new Form();
        $form->setMethod('get');
        $semestre = $this->context->sources->semesterSource->getAll();
        $items = array();
        $cast = array(1 => 'leto', 2 => 'zima');
        foreach ($semestre as $id => $semester) {
            $items[$id] = "{$semester['rok']} {$cast[$semester['cast']]}";
        }
        $form->addSelect('semester', 'Semester:', $items);
        $form->addSubmit('nacitaj', 'Načítaj');
        return $form;
    }

}
