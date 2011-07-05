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

    public function createComponentGridRiesitelia()
    {
        return $this->createComponentGrid('riesitelia');
    }

    public function createComponentGridSkoly()
    {
        return $this->createComponentGrid('skoly');
    }

    public function createComponentGrid($type)
    {
        /** Load the grid settings to $settings */
        $file = APP_DIR . "{$this->templateDir}/$type.neon";
        if (!is_file($file) || !is_readable($file)) {
            throw new Nette\FileNotFoundException("File $file is missing or is not readable.");
        }
        $settings = Neon::decode(file_get_contents($file));

        $grid = new Grid();

        $grid->setModel(new NetteModel($this->getContext()
                ->getService('database')->table($settings['table'])));

        /** Create form */
        $grid->addComponent(
            callback($this, 'createComponent' . ucfirst($settings['form']))
            ->invoke(), $settings['form']);
        $form = $grid[$settings['form']];

        /** Add columns */
        foreach ($settings['columns'] as $key => $column) {
            $grid->addColumn($key, null, $column);
        }

        $but = &$settings['buttons'];
        $values = callback($this, $settings['data']);
        $but['delete']['handler'] = callback($this, $but['delete']['handler']);

        $but['detail']['handler'] = function($row) use ($form, $values) {
            $def= $values->invoke($row['id']);
            $form->setDefaults($values->invoke($row['id']));
            $form->setRenderer(new DisplayFormRenderer);
            $form->render();
        };

        $but['edit']['handler'] = function($row) use ($form, $values) {
            $form->setDefaults($values->invoke($row['id']));
            $form->render();
        };

        $but['pridaj']['handler'] = function() use ($form) {
            $form->render();
        };

        foreach ($settings['buttons'] as $key => $button) {
            $toolbar = isset($button['toolbar']) ? $button['toolbar'] : false;
            $window  = isset($button['window']) ? $button['window'] : false;
            unset($button['toolbar']);
            unset($button['window']);
            $call  = $toolbar ? 'addToolbar' : 'add';
            $call .= $window  ? 'WindowButton' : 'Button';

            call_user_func(array($grid, $call), $key, null, $button);
        }


        return $grid;

    }

    public function getSkolaData($id)
    {
        $source = new SkolaSource($this->getContext()->database);
        $data = $source->getById($id);
        if (is_null($data['adresa'])) {
            $data['adresa'] = new AdresaRecord();
        }
        return $data;
    }
    public function getRiesitelData($id)
    {
    }

    public function deleteRiesitel($row)
    {
    }

    public function deleteSkola($row)
    {
    }

    public function createComponentSkolaForm()
    {
        $form = new Form;
        $form->setRenderer(new EditFormRenderer);
        $form->addGroup('Všeobecné informácie');
        $form->addText('nazov', 'Názov:')->setRequired(true);
        $form->addText('skratka', 'Skratka:')->setRequired(true);
        $form->addGroup('Poskytované vzdelanie');
        $form->addCheckbox('zakladna', 'Základné:');
        $form->addCheckbox('stredna', 'Stredné:');
        $form->addGroup('Adresa');
        $form->addContainer('adresa');
        $form['adresa']->addText('ulica', 'Ulica:')->setRequired(true);
        $form['adresa']->addText('mesto', 'Mesto:')->setRequired(true);
        $form['adresa']->addText('psc', 'PSČ:')->setRequired(true);

        $form->addGroup('Kontaktné údaje');
        $form->addText('email', 'Email:');
        $form->addText('telefon', 'Telefón:');

        $form->addSubmit('posli', 'Odošli');
        return $form;
    }

    public function createComponentRiesitelForm()
    {
        $form = new Form;
        $form->setRenderer(new EditFormRenderer);
        return $form;
    }

    public function renderRiesitelia()
    {
    }

}
