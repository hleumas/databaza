<?php
namespace AdminModule;
class StitkyPresenter extends BasePresenter
{

    private $sql =
            'SELECT o.meno, o.priezvisko, a.ulica, a.psc, a.mesto 
            FROM riesitel LEFT JOIN osoba o ON riesitel.osoba_id = o.id 
            LEFT JOIN adresa a ON o.adresa_id = a.id ';
    private $type = '';
    public function actionDefault($type='riesitelia')
    {
        $this->type = $type;
        if ($type === 'riesitelia') {
            $this->sql = 'SELECT o.meno, o.priezvisko, a.ulica, a.psc, a.mesto 
            FROM riesitel LEFT JOIN osoba o ON riesitel.osoba_id = o.id 
            LEFT JOIN adresa a ON o.adresa_id = a.id ';
        } else {
            $this->sql = 'SELECT s.nazov, a.ulica, a.psc, a.mesto FROM skola s 
                LEFT JOIN adresa a ON s.adresa_id = a.id ';
        }
    }
    public function createComponentForm()
    {
        $form = new \Nette\Application\UI\Form;
        $form->addTextArea('sql', $this->sql);
        $form->addSubmit('submit', 'getStitky');
        $form->onSuccess[] = callback($this, 'sendStitky');
        return $form;
    }

    public function sendStitky()
    {
        $items = $this->context->database->fetchAll($this->sql . $this['form']['sql']->value);
        $adresy = array();
        foreach ($items as $item) {
            if ($this->type === 'riesitelia') {
                $nazov = "{$item['meno']} {$item['priezvisko']}";
            } else {
                $nazov = "{$item['nazov']}";
            }
            $adresy[] = array(
                "$nazov",
                "{$item['ulica']}",
                "{$item['psc']}, {$item['mesto']}",
                "");
        }
        $file = \Stitky::renderStitky($adresy, 3, 8);
        $response = new \Nette\Application\Responses\FileResponse($file, "stitky.pdf");
        $this->sendResponse($response);
        unlink($file);
        //$this->template->result = $adresy;
    }
}
