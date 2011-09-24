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
    /** @persistent */
    public $semester;
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

    public function createGridModel()
    {
        return new NetteModel(
            $this->context->database
            ->table('seria')
            ->where('semester_id', $this->getSemesterId())
            ->order('cislo DESC'));
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

    public function createComponentForm()
    {
        $form = parent::createComponentForm();
        $form['aktualna']->setValue(true);
        return $form;
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
        $semesterSource = $this->context->sources->semesterSource;
        $form = $this['form'];
        $data = $form->getValues();
        $data['termin'] = new \Nette\DateTime($data['termin']);
        $data['semester'] = $this->getSemesterId();
        if ($data['semester'] == $semesterSource->getLastId()) {
            $semesterSource->insertNew();
        }
        $record = new SeriaRecord($data);
        if (!empty($record['id'])) {
            $this->context->sources->seriaSource->update($record);
            $this['grid']->flashMessage("Zmenená séria");
        } else {
            $seriaId = $this->context->sources->seriaSource->insert($record);
            $i = 1;
            foreach (explode(',', $this->context->sources->kategoria->kody) as $kod) {
                $priklad = new PrikladRecord;
                $priklad['seria'] = $seriaId;
                $priklad['cislo'] = $i;
                $priklad['kod']   = $kod;
                $priklad['body']  = '9';
                $priklad['nazov'] = $kod . ' Pomenuj!';
                $this->context->sources->prikladSource->insert($priklad);
                $i++;
            }
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
        $form->addSelect('semester', 'Semester:', $items)
            ->setValue($this->getSemesterId());
        $form->addSubmit('nacitaj', 'Načítaj');
        return $form;
    }

}
