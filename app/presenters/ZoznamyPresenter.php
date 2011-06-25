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
/**
 * Zoznamy presenter.
 *
 * @author     Samuel
 * @package    Presenters
 */
class ZoznamyPresenter extends BasePresenter
{


    public static function emailHandler($email, $maxlen)
    {
        return Html::el('a', array(
            'href' => 'mailto:' . $email,
            'title' => $email))
            ->setText(Strings::truncate($email, $maxlen));
    }
    public function createComponentGrid()
    {
        $connection = $this->getContext()->getService('database');
        $grid = new Grid();
        $grid->addComponent($this->createComponentForm(), 'form');
        $grid->setModel(new NetteModel($connection, 'zoznamy_riesitel_view'));
        $grid->addColumn('meno', 'Meno')->setSortable(true)->setLength(16);
        $grid->addColumn('priezvisko', 'Priezvisko')->setSortable(true)
            ->setLength(16);
        $grid->addColumn('mesto', 'Mesto')->setSortable(true)->setLength(24);
        $grid->addColumn('telefon', 'Telefón')->setLength(20);
        $grid->addColumn('email', 'Email')
            ->setRenderer(function($row){echo ZoznamyPresenter::emailHandler($row['email'], 30);});
        $grid->addColumn('skola_skratka', 'Škola')->setSortable(true)->setLength(16);
        $grid->addColumn('rok_maturity', 'Maturita')->setSortable(true);
        $grid->addColumn('typ_studia', 'Štúdium');
        $grid->addToolbarButton('pridaj', 'Pridaj', array('icon' => 'ui-icon-plusthick'));
        $grid->addWindowButton('detail', 'Detail', array(
            'icon' => 'ui-icon-search',
            'handler' => function($row) use ($grid) {
                echo 'cakuk';
                $grid['form']->render();}));
        $grid->addButton('edit', 'Zmeň', array('icon' => 'ui-icon-pencil'));
        $grid->addButton('delete', 'Zmaž', array(
            'icon' => 'ui-icon-closethick',
            'confirmationQuestion' => function($row) {
                return "Skutočne zmazať riešiteľa {$row['meno']} {$row['priezvisko']}?";},
            'handler' => function($row) use ($grid) {
                $grid->flashMessage('Trhni si!', 'error');}));
        return $grid;

    }

    public function createComponentForm()
    {
        $form = new Form;
        $form->addText('meno', 'Meno:')
            ->setRequired(true);
        $form->addSubmit('posli', 'Pošli ma!');
        return $form;
    }
    public function renderRiesitelia()
    {

    }

}
