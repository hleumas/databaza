<?php
namespace AdminModule;

/**
 * Databaza FKS
 *
 * @package    Presenters
 */



use Nette\Application\UI\Form;
use Nette\Utils\Neon;
/**
 * Skoly presenter.
 *
 * @author     Samuel
 * @package    Presenters
 */
class SubmitPresenter extends BasePresenter
{

    public function createComponentSubmitForm()
    {
        $form = new Form();
        $form->addUpload('riesenia', 'Opravené riešenia');
        $form->addSubmit('submit', 'Nahraj');
        $form->onSuccess[] = callback($this, 'onSubmit');
        return $form;
    }

    public function onSubmit()
    {
        $archivName = $this['submitForm']->values['riesenia'];
        $this->context->sources->submitHandler->saveArchiv($archivName);
        unlink($archivName);
        $this->flashMessage('Úspešne nahraté');
        $this->redirect('default');
    }

}
