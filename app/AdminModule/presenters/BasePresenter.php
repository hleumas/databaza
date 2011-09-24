<?php
namespace AdminModule;

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
        return new \Menu(APP_DIR . '/AdminModule/templates/menu.neon', 'menu');
    }

}
