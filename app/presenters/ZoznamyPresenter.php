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
class ZoznamyPresenter extends BasePresenter
{

    private $templateDir = '/templates/Zoznamy';

    public function createForm($type)
    {
        $file = APP_DIR . "{$this->templateDir}/{$type}Form.neon";
        if (!is_file($file) || !is_readable($file)) {
            throw new Nette\FileNotFoundException("File $file is missing or is not readable.");
        }
        $form = NeonFormFactory::createForm(file_get_contents($file));
        $form->onSuccess[] = callback($this, 'spracuj' . ucfirst($type));
        if (method_exists($this, "initForm" . ucfirst($type))) {
            call_user_func(array($this, "initForm" . ucfirst($type)), $form);
        }
        return $form;
    }
    public function initFormRiesitelia($form)
    {
        $studia = $this->context->sources->typStudiaSource->getAll();
        $stuSelect = array();
        foreach ($studia as $id => $studium) {
            $stuSelect[$id] = $studium['nazov'];
        }
        $form['typ_studia']->setItems($stuSelect);

        $dataSkoly = $this->context->database
            ->table('zoznamy_skola_view')
            ->select('id, nazov, mesto, zakladna, stredna')
            ->order('mesto ASC, nazov ASC')
            ->fetchPairs('id');
        $skoly = array();
        foreach ($dataSkoly as $skola) {
            $mesto = $skola['mesto'];
            if (!isset($skoly[$mesto])) {
                $skoly[$skola['mesto']] = array();
            }
            $nazov = Strings::truncate($skola['nazov'], 28);
            if (strlen($skola['nazov']) > 28) {
                $nazov = Html::el('option', $nazov)
                    ->title($skola['nazov'])
                    ->value($skola['id']);
            }
            $skoly[$mesto][$skola['id']] = $nazov;
        }
        
        $form['skola']->setItems($skoly);
    }

    public function createComponentGrid()
    {
        return $this->createGrid($this->getView());
    }
    public function createGrid($type)
    {
        /** Load the grid settings to $settings */
        $file = APP_DIR . "{$this->templateDir}/$type.neon";
        if (!is_file($file) || !is_readable($file)) {
            throw new Nette\FileNotFoundException("File $file is missing or is not readable.");
        }

        $grid = NeonGriditoFactory::createGrid(
            $this->context->database,
            file_get_contents($file));

        /** Create form */
        $grid->addComponent($this->createForm($type), "form");
        $form = $grid['form'];

        /** Set button handlers */
        $getData = callback($this, 'getData' . ucfirst($type));
        $grid['actions']->getComponent('delete')->handler = 
            callback($this, 'delete' . ucfirst($type));

        $grid['actions']->getComponent('detail')->handler = 
            function($row) use ($form, $getData) {
            $form->setDefaults($getData->invoke($row['id']));
            $form->setRenderer(new DisplayFormRenderer);
            $form->render();
        };

        $grid['actions']->getComponent('edit')->handler =
            function($row) use ($form, $getData) {
            $form->setDefaults($getData->invoke($row['id']));
            $form->render();
        };

        $grid['toolbar']->getComponent('pridaj')->handler =
            function() use ($form) {
            $form->render();
        };



        return $grid;

    }

    public function getDataSkoly($id)
    {
        $data = $this->context->sources->SkolaSource->getById($id);
        if (is_null($data['adresa'])) {
            $data['adresa'] = new AdresaRecord();
        }
        return FlatArray::deflate($data);
    }
    public function getDataRiesitelia($id)
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

    public function deleteRiesitelia($row)
    {
        try {
            $this->context->sources->riesitelSource->delete($row['id']);
            $this['grid']->flashMessage("Riešiteľ {$row['meno']} {$row['priezvisko']} odstránený");
        } catch (DBIntegrityException $e) {
            $this['grid']->flashMessage($e->getMessage(), 'error');
        }
    }

    public function deleteSkoly($row)
    {
        try {
            $this->context->sources->skolaSource->delete($row['id']);
            $this['grid']->flashMessage("Škola {$row['nazov']} odstránená");
        } catch (DBIntegrityException $e) {
            $this['grid']->flashMessage($e->getMessage(), 'error');
        }
    }


    public function spracujSkoly()
    {
        $form = $this['grid']['form'];
        $record = new SkolaRecord(FlatArray::inflate($form->getValues()));
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


    public function spracujRiesitelia()
    {
        $sources = $this->context->sources;
        $form = $this['grid']['form'];
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
