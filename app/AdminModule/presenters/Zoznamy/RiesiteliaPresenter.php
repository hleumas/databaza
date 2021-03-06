<?php
namespace AdminModule;

/**
 * Databaza FKS
 *
 * @package    Presenters
 */



use Gridito\Grid;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Utils\Neon;
use Gridito\FullTextSelection;
/**
 * Zoznamy presenter.
 *
 * @author     Samuel
 * @package    Presenters
 */
class RiesiteliaPresenter extends ZoznamyPresenter
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

    public function createComponentForm()
    {
        $sources = $this->context->sources;
        $showFKS = ($sources->kategoria->id === 1);
        $form = new \RiesitelForm(
            'form',
            $sources->SkolaSource->getAll(),
            $sources->typStudiaSource->getAll(),
            $showFKS
        );
        $form->addSubmit('odosli', 'Odošli');
        $form->onSuccess[] = callback($this, 'onSubmit');
        $form->onSubmit[] = callback($this, 'setSubmitted');
        return $form;
    }

    public function createGridModel()
    {
        $model = new FullTextSelection(
            $this->context->database->table('zoznamy_riesitel_view'));
        $search = $this->getParam('search', false);
        if ($search) {
            return $model->filter($search, array('meno', 'priezvisko', 'email', 'mesto', 'rok_maturity'));
        } else {
            return $model;
        }
    }
    public function getData($id)
    {
        $source = $this->context->sources->riesitelSource;
        $record = $source->getById($id);
        $data = \FlatArray::deflate($record);
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
        $record = new \RiesitelRecord(\FlatArray::inflate($this['form']->values));
        if ($record['koresp_kam'] != \RiesitelRecord::KORESP_ELSE) {
            $record['koresp_adresa'] = null;
        } else {
            $record['koresp_adresa']['stat'] = 'SR';
        }
        $record['osoba']['adresa']['stat'] = 'SR';
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
