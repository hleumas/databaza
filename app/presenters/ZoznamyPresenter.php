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
        return $form;
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
        $settings = Neon::decode(file_get_contents($file));

        /** Create Grid and set the model */
        $grid = new Grid();
        $grid->setModel(new NetteModel($this->getContext()
                ->getService('database')->table($settings['table'])));

        /** Create form */
        $grid->addComponent($this->createForm($type), "form");
        $form = $grid['form'];

        /** Add columns */
        foreach ($settings['columns'] as $key => $column) {
            $grid->addColumn($key, null, $column);
        }

        /** Set button handlers */
        $but = &$settings['buttons'];
        $getData = callback($this, 'getData' . ucfirst($type));
        $but['delete']['handler'] = callback($this, 'delete' . ucfirst($type));

        $but['detail']['handler'] = function($row) use ($form, $getData) {
            $form->setDefaults($getData->invoke($row['id']));
            $form->setRenderer(new DisplayFormRenderer);
            $form->render();
        };

        $but['edit']['handler'] = function($row) use ($form, $getData) {
            $form->setDefaults($getData->invoke($row['id']));
            $form->render();
        };

        $but['pridaj']['handler'] = function() use ($form) {
            $form->render();
        };

        /** Create buttons */
        foreach ($settings['buttons'] as $key => $button) {
            $call  = empty($button['toolbar']) ? 'add' : 'addToolbar';
            $call .= empty($button['window']) ? 'Button' : 'WindowButton';
            unset($button['toolbar']);
            unset($button['window']);

            call_user_func(array($grid, $call), $key, null, $button);
        }


        return $grid;

    }

    public function getDataSkoly($id)
    {
        $source = new SkolaSource($this->getContext()->database);
        $data = $source->getById($id);
        if (is_null($data['adresa'])) {
            $data['adresa'] = new AdresaRecord();
        }
        return $data;
    }
    public function getDataRiesitelia($id)
    {
    }

    public function deleteRiesitelia($row)
    {
    }

    public function deleteSkoly($row)
    {
        $source = new SkolaSource($this->getContext()->database);
        try {
            $source->delete($row['id']);
            $this['grid']->flashMessage("Škola {$row['nazov']} odstránená");
        } catch (DBIntegrityException $e) {
            $this['grid']->flashMessage($e->getMessage(), 'error');
        }
    }


    public function spracujSkoly()
    {
        $source = new SkolaSource($this->getContext()->database);
        $form = $this['grid']['form'];
        $record = new SkolaRecord($form->getValues());
        $record['adresa']['stat'] = 'SR';
        if (!empty($record['id'])) {
            $source->update($record);
            $this['grid']->flashMessage("Zmenená škola {$record['nazov']}");
        } else {
            $source->insert($record);
            $this['grid']->flashMessage("Pridaná škola {$record['nazov']}");
        }
        $this->redirect('this');
    }


    public function spracujRiesitelia()
    {
    }
}
