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
class PrikladyPresenter extends ZoznamyPresenter
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
    public function getGridSource()
    {
        return $this->context->database
            ->table('priklad')
            ->where('seria_id', $this['seriaSelector']->seria)
            ->order('cislo');
    }

    public function setGridHandlers($grid)
    {
        return parent::setGridHandlers($grid);
    }

    public function createComponentForm()
    {
        $form = parent::createComponentForm();
        return $form;
    }
    public function getData($id)
    {
        $data = $this->context->sources->PrikladSource->getById($id);
        return $data;
    }

    public function delete($row)
    {
        try {
            $this->context->sources->prikladSource->delete($row['id']);
            $this['grid']->flashMessage('Príklad odstránený');
        } catch (DBIntegrityException $e) {
            $this['grid']->flashMessage($e->getMessage(), 'error');
        }
    }

    public function onSubmit()
    {
        $prikladSource = $this->context->sources->prikladSource;
        $form = $this['form'];
        $data = $form->getValues();
        $data['seria'] = $this['seriaSelector']->seria;
        $data['body'] = 9;
        $record = new PrikladRecord($data);
        if (!empty($record['id'])) {
            $this->context->sources->prikladSource->update($record);
            $this['grid']->flashMessage("Zmenený príklad");
        } else {
            $this->context->sources->prikladSource->insert($record);
            $this['grid']->flashMessage("Pridaný príklad");
        }
        $this->redirect('this');
    }
}
