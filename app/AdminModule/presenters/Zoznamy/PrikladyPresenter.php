<?php
namespace AdminModule;

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
        return new \SeriaSelector(
            $this->seria,
            $sources->seriaSource,
            $sources->semesterSource,
            $sources->kategoria);
    }
    public function createGridModel()
    {
        return new NetteModel(
            $this->context->database
            ->table('priklad')
            ->where('seria_id', $this['seriaSelector']->seria)
            ->order('cislo'));
    }

    public function setGridHandlers($grid)
    {
        $grid->setEditHandler(callback($this, 'handleEdit'));
        $prikladSource = $this->context->sources->prikladSource;
        $grid['actions']->getComponent('up')->setHandler(
            function($row) use ($prikladSource) {
                $prikladSource->lowerNumber($row['id']);
            }
        )->setEnabled(function($row) {return $row['cislo'] > 1;});
        $grid['actions']->getComponent('down')->setHandler(
            function($row) use ($prikladSource) {
                $prikladSource->raiseNumber($row['id']);
            }
        )->setEnabled(
            function($row) use($prikladSource) {
                return $row['cislo'] < $prikladSource->getLastNumber($row['seria_id']);
            });
        return parent::setGridHandlers($grid);
    }

    public function handleEdit($post)
    {
        $grid = $this['grid'];
        $data = $this->context->sources->prikladSource->getById($post['id']);
        foreach ($post as $column => $value) {
            $data[$column] = $value;
        }
        try {
            $cislo = $data['cislo'];
            $this->context->sources->prikladSource->update($data);
            $grid->flashMessage("Upravený príklad číslo $cislo {$data['nazov']}");
            $grid->invalidateControl('flashes');
        } catch (InvalidDataException $e) {
            $grid->flashMessage("Názov nesmie byť prázdny", 'error');
            $grid->invalidateControl();
        }
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
        $record = new \PrikladRecord($data);
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
