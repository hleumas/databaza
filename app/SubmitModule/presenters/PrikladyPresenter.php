<?php
namespace SubmitModule;

/**
 * Databaza FKS
 *
 * @package    Presenters
 */

use Nette\Application\UI\Form;
use Nette\Mail\Message;


/**
 * Priklady presenter.
 *
 * @author     Samuel
 * @package    Presenters
 */
class PrikladyPresenter extends BasePresenter
{
    private function getSeriaId()
    {
        return $this->context->sources->kategoria->aktualna_seria_id;
    }
    public function createComponentForm()
    {
        $form = new Form();
        $priklady = $this->context->sources->prikladSource->getAll($this->getSeriaId());
        foreach ($priklady as $priklad) {
            $items[$priklad['id']] = $priklad['kod'] . ' ' . $priklad['nazov'];
        }
        $form->addSelect('priklad', 'Príklad:', $items)
            ->setPrompt('Zvoľ príklad')
            ->setRequired('Zvoľ príklad');
        $form->addUpload('file', 'Riešenie:')
            ->addRule(Form::MAX_FILE_SIZE, 'Príliš veľký súbor', 1000000)
            ->setRequired('Zvoľ riešenie na odovzdanie');
        $form->addSubmit('odovzdaj', 'Odovzdaj');
        $form->onSuccess[] = callback($this, 'onSubmit');
        return $form;
    }
    public function onSubmit()
    {
        $riesitelId = $this->identity->id;
        $data = $this['form']->getValues();
        $priklady = $this->context->sources->prikladSource->getAll($this->getSeriaId());
        $this->context->sources->riesitelPrikladFileSource->insert(array(
            'riesitel' => $riesitelId,
            'priklad' => $data['priklad'],
            'seria' => $this->getSeriaId(),
            'file' => $data['file'],
            'uploaded' => new \Nette\Datetime()
        ));
        $this->flashMessage("Odovzdané riešenie príkladu {$priklady[$data['priklad']]['kod']}");
        $this->redirect('this');
    }

    public function actionStiahni($id)
    {
        $file = $this->context->sources->riesitelPrikladFileSource->getById($id);
        $fname = tempnam('/tmp', 'down');
        file_put_contents($fname, $file['content']);
        $response = new \Nette\Application\Responses\FileResponse($fname, $file['filename']);
        $this->sendResponse($response);
    }

    public function renderZoznam()
    {
        /** Source container */
        $sources = $this->context->sources;

        /** Id riesitela */
        $riesitelId = $this->identity->id;

        /** Id serie, docasne berieme aktualnu */
        $seriaId = $this->getSeriaId();

        /** Zoznam prikladov a suborov*/
        $zoznam = $sources->riesitelPrikladFileSource
            ->listByRiesitelSeria($riesitelId, $seriaId);

        //\Nette\Diagnostics\Debugger::dump($zoznam);
        $this->template->priklady = $zoznam;

    }

}
