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
        $submitHandler = $this->context->sources->submitHandler;
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
        $grid['actions']->getComponent('eriesenia')->setHandler(callback($this, 'sendPriklady'));
        $grid['actions']->getComponent('oboduj')->setHandler(callback($this, 'oboduj'));
        return parent::setGridHandlers($grid);
    }

    public function oboduj($row)
    {
        $this->populateObodujForm($row['id'], $this['obodujForm'])->render();
    }
    public function renderOboduj()
    {
        $template = $this->template;
        $template->setFile(APP_DIR . $this->templateDir . '/oboduj.latte');
        $this['obodujForm']->validate();
        $template->form = $this['obodujForm'];
    }

    public function createComponentObodujForm()
    {
        $form = new Form();
        $form->setRenderer(new \EditFormRenderer());
        $form->addGroup('Body');
        $form->addHidden('priklad');
        $form->onSuccess[] = callback($this, 'submitOboduj');

        $priklad = $this->context->httpRequest->getPost('priklad');
        if (!is_null($priklad)) {
            return $this->populateObodujForm($priklad, $form);
        }
        return $form;
    }
    public function populateObodujForm($id, $form)
    {
        $form->setAction($this->link('oboduj', array('do' => 'obodujForm-submit')));
        $riesitelia = $this->fetchObodujRiesitelia($id);

        $form['priklad']->setValue($id);
        foreach ($riesitelia as $riesitel) {
            $form->addText(
                "riesitel.{$riesitel['id']}",
                "{$riesitel['meno']} {$riesitel['priezvisko']}",
                3
            )->setType('number')
            ->setDefaultValue($riesitel['body'])
            ->addCondition(Form::FILLED)->addRule(
                Form::INTEGER,
                'Počet bodov musí byť kladné číslo'
            )->addRule(
                Form::RANGE,
                'Počet bodov musí byť kladné číslo',
                array(0, null)
            );
        }
        $form->addSubmit('send', 'Oboduj');
        return $form;
    }

    private function fetchObodujRiesitelia($id)
    {
        $sql = <<<SQL
SELECT meno, priezvisko, body, riesitel.id FROM riesitel 
LEFT JOIN osoba ON riesitel.osoba_id = osoba.id
LEFT JOIN riesitel_priklady ON riesitel_priklady.riesitel_id = riesitel.id
WHERE riesitel_priklady.priklad_id = ?
ORDER BY priezvisko ASC, meno ASC
SQL;
        return $this->context->database->fetchAll($sql, $id);
    }

    public function submitOboduj()
    {
        $this['grid']->flashMessage('Nahodené body');
        $form = $this['obodujForm'];
        $values = \FlatArray::inflate($form->values);
        foreach ($values['riesitel'] as $id => $body) {
            if ($body === '') {
                $body = null;
            }
            $this->context->sources->riesitelSeriaSource
                ->setPrikladById($id, $values['priklad'], $body);
        }
        $this->redirect('default');
    }

    public function sendPriklady($row)
    {
        $archiv = $this->context->sources->submitHandler->getArchiv($row['id']);
        $response = new \Nette\Application\Responses\FileResponse($archiv, 'ulohy.zip');
        $this->sendResponse($response);
        unlink($archiv);
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
