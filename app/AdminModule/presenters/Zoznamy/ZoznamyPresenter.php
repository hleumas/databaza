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
/**
 * Zoznamy presenter.
 *
 * @author     Samuel
 * @package    Presenters
 */
abstract class ZoznamyPresenter extends BasePresenter
{

    public $templateDir = '/AdminModule/templates/Zoznamy';
    private $semesterId = null;
    public  $submitted = false;

    public abstract function onSubmit();
    public abstract function delete($row);
    public abstract function getData($id);

    public function getPresenterName()
    {
        list($module, $presenter) = explode(':', $this->name);
        return lcfirst($presenter);
    }
    public function formatTemplateFiles()
    {
        $presenter = $this->presenterName;
        return array(APP_DIR . "$this->templateDir/$presenter.latte");
    }

    public function formatLayoutTemplateFiles()
    {
        return array(APP_DIR . "/AdminModule/templates/@layout.latte");
    }
    public function formatGridFiles()
    {
        $presenter = $this->presenterName;
        return APP_DIR . "$this->templateDir/$presenter.neon";
    }

    public function formatFormFiles()
    {
        $presenter = $this->presenterName;
        return APP_DIR . "$this->templateDir/{$presenter}Form.neon";
    }

    public function setSubmitted()
    {
        $this->submitted = true;
    }
    public function createComponentForm()
    {
        $file = $this->formatFormFiles();
        if (!is_file($file) || !is_readable($file)) {
            throw new \Nette\FileNotFoundException("File $file is missing or is not readable.");
        }
        $form = \NeonFormFactory::createForm(file_get_contents($file));
        $form->onSuccess[] = callback($this, 'onSubmit');
        $form->onSubmit[] = callback($this, 'setSubmitted');
        return $form;
    }

    public abstract function createGridModel();

    public function createComponentGrid()
    {
        /** Check the existence of file */
        $file = $this->formatGridFiles();
        if (!is_file($file) || !is_readable($file)) {
            throw new \Nette\FileNotFoundException("File $file is missing or is not readable.");
        }

        $grid = \NeonGriditoFactory::createGrid(
            $this->createGridModel(),
            file_get_contents($file));

        return $this->setGridHandlers($grid);
    }

    public function setGridHandlers($grid)
    {
        /** Set button handlers */
        $getForm = callback($this, 'getComponent');
        $getData = callback($this, 'getData');

        foreach (array('delete', 'detail', 'edit') as $action) {
            $actions[$action] = $grid['actions']->getComponent($action, false);
        }
        $actions['pridaj'] = $grid['toolbar']->getComponent('pridaj', false);

        if (!is_null($actions['delete'])) {
            $actions['delete']->handler = callback($this, 'delete');
        }

        if (!is_null($actions['detail'])) {
            $actions['detail']->handler = 
                function($row) use ($getForm, $getData) {
                $form = $getForm('form');
                $form->setDefaults($getData->invoke($row['id']));
                $form->setRenderer(new \DisplayFormRenderer);
                $form->render();
            };
        }

        if (!is_null($actions['edit'])) {
            $actions['edit']->handler =
                function($row) use ($getForm, $getData) {
                $form = $getForm('form');
                $form->setDefaults($getData->invoke($row['id']));
                $form->render();
            };
        }

        if (!is_null($actions['pridaj']))  {
            $actions['pridaj']->handler =
                function() use ($getForm) {
                $form = $getForm('form');
                $form->render();
            };
        }
        return $grid;
    }
}
