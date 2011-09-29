<?php
namespace SubmitModule;

/**
 * My Application
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */

use Nette\Application\UI,
	Nette\Security as NS;


/**
 * Sign in/out presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class SignPresenter extends BasePresenter
{


	/**
	 * Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$form->addText('username', 'Login:')
			->setRequired('Vyplňte prosím login.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Vyplňte prosím heslo.');

		$form->addSubmit('send', 'Prihlásiť');

		$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}



	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
            $this->getUser()->setExpiration('+ 40 minutes', TRUE);
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Priklady:zoznam');

		} catch (NS\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}



	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Boli ste odhlásení.');
		$this->redirect('in');
	}

}
