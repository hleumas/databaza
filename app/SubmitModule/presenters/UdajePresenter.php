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
        $showFKS = ($sources->kategoria->id === 1);
        $form = new \RiesitelForm(
            'udajeForm',
            $sources->skolaSource->getAll(),
            $sources->typStudiaSource->getAll(),
            $showFKS
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
        } elseif ($this->context->database
                    ->table('users')
                    ->where('id', $values['riesitel'])
                    ->count('*')) {
            $this['nameForm']['riesitel']->addError('Konto už existuje. Ak si zabudol heslo, napíš na otazky@fks.sk');
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

    private function sendRegistrationEmail($riesitel, $activationLink)
    {
        $celeMeno = $riesitel['osoba']['meno'] . ' '
                  . $riesitel['osoba']['priezvisko'];
        $mail = new Message;
        $mail->setFrom('otazky@fks.sk')
            ->addTo($riesitel['osoba']['email'], $celeMeno)
            ->setSubject('Dokončenie registrácie na FKS Submite')
            ->setBody(<<<MESSAGE
Milá/ý $celeMeno,
gratulujeme k úspešnému vytvoreniu si konta na FKS submite!

Pre aktivovanie si konta prosím navštív nasledovný link:
$activationLink

V prípade akýchkoľvek problémov nás kontaktuj na emailovej adrese otazky@fks.sk. 
Veľa šťastia pri riešení prajú

Vedúci FKS
MESSAGE
        )->send();
    }
    public function onLoginSubmit()
    {
        $form = $this['loginForm'];
        $source = $this->context->sources->riesitelSource;
        $registrateData = $this->context->session->getSection('registrateData');
        $authenticator = $this->context->authenticator;
        $data = $form->values;
        $riesitelID = $registrateData['riesitelID'];
        if (!$authenticator->loginExists($data['login'])) {
            if ($riesitelID == -1) {
                $riesitel = $registrateData['riesitel'];
                $riesitelID = $source->insert($riesitel);
            }
            $authenticator->createAccount(
                $riesitelID,
                array($data['login'], $data['password']),
                0 /* Do not activate the account */
            );

            /** Ugly hack, because insert changes riesitel */
            $riesitel = $source->getById($riesitelID);
            $this->sendRegistrationEmail(
                $riesitel,
                $this->link('//Udaje:activate',
                $data['login'],
                $authenticator->getActivationHash($data['login'])));
            $this->redirect('registrationSuccess');
        } else {
            $form['login']->addError('Tento login je už obsadený');
        }
    }

    public function actionActivate($login, $activationCode)
    {
        try {
            $this->context->authenticator->activateByHash($login, $activationCode);
            $this->redirect('activationSuccess');
        } catch (AuthenticationException $e) {
            $this->redirect('activationError');
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
