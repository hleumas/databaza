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
abstract class ZoznamyPresenter extends BasePresenter
{

    private $templateDir = '/templates/Zoznamy';
    private $semesterId = null;

    public abstract function onSubmit();
    public abstract function delete($row);
    public abstract function getData($id);
    public abstract function getGridSource();

    public function formatTemplateFiles()
    {
        $name = lcfirst($this->name);
        return array(APP_DIR . "$this->templateDir/$name.latte");
    }

    public function formatLayoutTemplateFiles()
    {
        return array(APP_DIR . "/templates/@layout.latte");
    }
    public function formatGridFiles()
    {
        $name = lcfirst($this->name);
        return APP_DIR . "$this->templateDir/$name.neon";
    }

    public function formatFormFiles()
    {
        $name = lcfirst($this->name);
        return APP_DIR . "$this->templateDir/{$name}Form.neon";
    }

    public function createForm()
    {
        $file = $this->formatFormFiles();
        if (!is_file($file) || !is_readable($file)) {
            throw new Nette\FileNotFoundException("File $file is missing or is not readable.");
        }
        $form = NeonFormFactory::createForm(file_get_contents($file));
        $form->onSuccess[] = callback($this, 'onSubmit');
        return $form;
    }

    public function createComponentGrid()
    {
        /** Check the existence of file */
        $file = $this->formatGridFiles();
        if (!is_file($file) || !is_readable($file)) {
            throw new Nette\FileNotFoundException("File $file is missing or is not readable.");
        }

        $grid = NeonGriditoFactory::createGrid(
            $this->getGridSource(),
            file_get_contents($file));

        /** Create form */
        $grid->addComponent($this->createForm(), "form");

        return $this->setGridHandlers($grid);
    }

    public function setGridHandlers($grid)
    {
        /** Set button handlers */
        $form    = $grid['form'];
        $getData = callback($this, 'getData');
        $grid['actions']->getComponent('delete')->handler =
            callback($this, 'delete');

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
}
