<?php
namespace SubmitModule;
use Nette\Utils\Neon;

/**
 * My Application
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */


/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
    public function createComponentMenu()
    {
        $file = APP_DIR . '/SubmitModule/templates/menu.neon';
        if (!is_file($file)) {
            throw new Nette\FileNotFoundException("File $file does not exists!");
        }
        $data = file_get_contents($file);
        if ($data === false) {
            throw new Nette\FileNotFoundException("File $file is not readable!");
        }
        $menuItems = Neon::decode($data);
        if ($this->user->isLoggedIn()) {
            $menuItems['Odhlásiť ' . $this->user->identity->data['login']] = 'Sign:out';
        } else {
            $menuItems['Prihlásiť'] = 'Sign:in';
        }
        return new \Menu($menuItems, 'menu');
    }

    public function getIdentity()
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        return $this->user->identity;
    }

}
