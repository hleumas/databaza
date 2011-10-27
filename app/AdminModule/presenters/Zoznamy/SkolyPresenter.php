<?php
namespace AdminModule;

/**
 * Databaza FKS
 *
 * @package    Presenters
 */



use Gridito\Grid;
use Gridito\FullTextModel;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Utils\Neon;
/**
 * Skoly presenter.
 *
 * @author     Samuel
 * @package    Presenters
 */
class SkolyPresenter extends ZoznamyPresenter
{
    /** @persistent */
    public $search;

    public function createComponentSearch()
    {
        $form = new Form();
        $form->setMethod('get');
        $form->addText('search', '');
        $form->addSubmit('submit', 'Hľadaj');
        return $form;
    }

    public function createGridModel()
    {
        $model = new FullTextModel(
            $this->context->database->table('zoznamy_skola_view'));
        $search = $this->getParam('search', false);
        if ($search) {
            return $model->filter($search, array('nazov', 'skratka', 'mesto', 'email'));
        } else {
            return $model;
        }
    }

    public function getData($id)
    {
        $data = $this->context->sources->SkolaSource->getById($id);
        if (is_null($data['adresa'])) {
            $data['adresa'] = new \AdresaRecord();
        }
        return \FlatArray::deflate($data);
    }

    public function delete($row)
    {
        try {
            $this->context->sources->skolaSource->delete($row['id']);
            $this['grid']->flashMessage("Škola {$row['nazov']} odstránená");
        } catch (DBIntegrityException $e) {
            $this['grid']->flashMessage($e->getMessage(), 'error');
        }
    }

    public function onSubmit()
    {
        $form = $this['form'];
        $record = new \SkolaRecord(\FlatArray::inflate($form->getValues()));
        $record['adresa']['stat'] = 'SR';
        if (!empty($record['id'])) {
            $this->context->sources->skolaSource->update($record);
            $this['grid']->flashMessage("Zmenená škola {$record['nazov']}");
        } else {
            $this->context->sources->skolaSource->insert($record);
            $this['grid']->flashMessage("Pridaná škola {$record['nazov']}");
        }
        $this->redirect('this');
    }

}
