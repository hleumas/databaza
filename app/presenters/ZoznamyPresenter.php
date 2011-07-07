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
        $db = $this->getContext()->database;
        $studiumSrc = new TypStudiaSource($db);
        $studia = $studiumSrc->getAll();
        $stuSelect = array();
        foreach ($studia as $id => $studium) {
            $stuSelect[$id] = $studium['nazov'];
        }
        $form['typ_studia']->setItems($stuSelect);

        $dataSkoly = $db->table('zoznamy_skola_view')
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
                $nazov = Html::el('option', $nazov)->title($skola['nazov']);
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
        return FlatArray::deflate($data);
    }
    public function getDataRiesitelia($id)
    {
        $source = new RiesitelSource($this->getContext()->database);
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
        $record = new SkolaRecord(FlatArray::inflate($form->getValues()));
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
