<?php
namespace SubmitModule;

/**
 * Databaza FKS
 *
 * @package    Presenters
 */

use Nette\Application\UI\Form;


/**
 * Udaje presenter.
 *
 * @author     Samuel
 * @package    Presenters
 */
class UdajePresenter extends BasePresenter
{

    public function createComponentUdajeForm()
    {
        $sources = $this->context->sources;
        $form = new \RiesitelForm(
            'udajeForm',
            $sources->skolaSource->getAll(),
            $sources->typStudiaSource->getAll()
        );
        $form->addSubmit('odosli', 'Odošli');

        /** Add required */
        $form['osoba.email']->setRequired('Zadaj svoj email');
        $form['osoba.datum_narodenia']->setRequired('Vyplň dátum narodenia');
        $form['osoba.telefon']->setRequired('Vyplň svoj telefón');
        return $form;
    }

    public function createComponentLoginForm()
    {
        $form = new Form();
        $form->setRenderer(new \EditFormRenderer());
        $form->addGroup('Prihlasovacie údaje');
        $form->addText('login', 'Login:')
            ->addRule(Form::FILLED, 'Zvoľte si svoj login');
        $form->addPassword('password', 'Heslo:')
            ->addRule(Form::FILLED, 'Vyplňte prosím heslo')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mať aspoň %d znakov', 8);
        $form->addPassword('passwordConfirm', 'Heslo ešte raz:')
            ->addRule(Form::EQUAL, 'Heslá sa nezhodujú.', $form['password']);
        $form->onSuccess[] = callback($this, 'onLoginSubmit');
        $form->addSubmit('submit', 'Dokonči registráciu');
        return $form;
    }

    public function createComponentPasswordChangeForm()
    {
        $form = new Form();
        $form->setRenderer(new \EditFormRenderer());
        $form->addGroup('Prihlasovacie údaje');
        $form->addPassword('oldPassword', 'Staré heslo:')
            ->addRule(Form::FILLED, 'Vyplň svoje staré heslo');
        $form->addPassword('password', 'Nové heslo:')
            ->addRule(Form::FILLED, 'Zadaj nové heslo')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mať aspoň %d znakov', 8);
        $form->addPassword('passwordConfirm', 'Heslo ešte raz:')
            ->addRule(Form::EQUAL, 'Heslá sa nezhodujú.', $form['password']);
        $form->onSuccess[] = callback($this, 'onPasswordChangeSubmit');
        $form->addSubmit('submit', 'Zmeň heslo');
        return $form;
    }

    public function createComponentNameForm()
    {
        $form = new Form();
        $form->setRenderer(new \EditFormRenderer());
        $dataRiesitelia = $this->context->sources->riesitelSource->getAll();
        $riesitelia = array();
        $riesitelia['-1'] = 'Riešim prvýkrát';
        foreach ($dataRiesitelia as $riesitel) {
            $riesitelia[$riesitel['id']] =
                "{$riesitel['meno']} {$riesitel['priezvisko']}";
        }
        $form->addGroup('Kto som');
        $form->addSelect('riesitel', 'Moje meno:', $riesitelia);
        $form['riesitel']->setPrompt('Nájdi sa v zozname');
        $form['riesitel']->addRule(Form::FILLED, 'Nájdite svoje meno v zozname, alebo zvoľte možnosť „Riešim prvýkrát.“');

        /** Inteligent javascript selectBox */
        $form['riesitel']->getControlPrototype()->class[] = 'chosen';
        $form->addSubmit('next', 'Pokračuj v registrácii');
        $form->onSuccess[] = callback($this, 'onNameSubmit');
        return $form;
    }

    public function onNameSubmit()
    {
        $values = $this['nameForm']->getValues();
        $registrateData = $this->context->session->getSection('registrateData');
        $registrateData['riesitelID'] = $values['riesitel'];
        if ($values['riesitel'] == '-1') {
            $this->redirect('RegistrateUdaje');
        } else {
            $this->redirect('RegistrateLogin');
        }
    }
    public function getUdaje($id)
    {
        $record = $this->context->sources->riesitelSource
            ->getById($id);
        $data = \FlatArray::deflate($record);
        foreach (array('typ_studia', 'skola') as $key) {
            if (isset($data["$key.id"])) {
                $data[$key] = $data["$key.id"];
            } else {
                unset($data[$key]);
            }
        }
        return $data;
    }
    public function onRegistrateUdajeSubmit()
    {
        $registrateData = $this->context->session->getSection('registrateData');
        $registrateData['riesitel'] =
            $this->createRiesitelRecord($this['udajeForm']);
        $this->redirect('RegistrateLogin');
    }

    public function onUdajeSubmit()
    {
        $this->context->sources->riesitelSource
            ->update($this->createRiesitelRecord($this['udajeForm']));
        $this->flashMessage('Údaje zmenené');
        $this->redirect('this');
    }

    public function createRiesitelRecord($udajeForm)
    {
        $sources = $this->context->sources;
        $record = new \RiesitelRecord(\FlatArray::inflate($udajeForm->values));

        if ($record['koresp_kam'] != \RiesitelRecord::KORESP_ELSE) {
            $record['koresp_adresa'] = null;
        } else {
            $record['koresp_adresa']['stat'] = 'SR';
        }
        $record['osoba']['adresa']['stat'] = 'SR';
        $record['typ_studia'] = $sources->typStudiaSource->getById($record['typ_studia']);
        $record['datum'] = new \Nette\DateTime();
        return $record;
    }

    public function onLoginSubmit()
    {
        $form = $this['loginForm'];
        $registrateData = $this->context->session->getSection('registrateData');
        $authenticator = $this->context->authenticator;
        $data = $form->values;
        if (!$authenticator->loginExists($data['login'])) {
            if ($registrateData['riesitelID'] == -1) {
                $registrateData['riesitelID'] = $this->context->sources
                    ->riesitelSource->insert($registrateData['riesitel']);
            }
            $authenticator->createAccount(
                $registrateData['riesitelID'],
                array($data['login'], $data['password']),
                0 /* Do not activate the account */
            );
            $this->redirect('Sign:in');
        } else {
            $form['login']->addError('Tento login je už obsadený');
        }
    }

    public function actionRegistrateUdaje()
    {
        $this['udajeForm']->onSuccess[] = callback($this, 'onRegistrateUdajeSubmit');
    }
    public function actionOsobne()
    {
        $identity = $this->identity;
        $this['udajeForm']->setDefaults($this->getUdaje($identity->id));
        $this['udajeForm']->onSuccess[] = callback($this, 'onUdajeSubmit');
    }

    public function onPasswordChangeSubmit()
    {
        $data = $this['passwordChangeForm']->getValues();
        if (!$this->context->authenticator->verifyCredentials(array(
            $this->identity->data['login'],
            $data['oldPassword']
        ))) {
            $this['passwordChangeForm']['oldPassword']->addError('Nesprávne heslo');
        } else {
            $this->context->authenticator->passwd(array(
                $this->identity->data['login'],
                $data['password']));
            $this->flashMessage('Heslo úspešne zmenené');
            $this->redirect('Udaje:osobne');
        }
    }

}
