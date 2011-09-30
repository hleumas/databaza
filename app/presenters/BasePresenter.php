<?php

use \Nette\Utils\Neon;

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
        $file = APP_DIR . '/AdminModule/templates/menu.neon';
        if (!is_file($file)) {
            throw new \Nette\FileNotFoundException("File $file does not exists!");
        }
        $data = file_get_contents($file);
        if ($data === false) {
            throw new \Nette\FileNotFoundException("File $file is not readable!");
        }
        return new \Menu(Neon::decode($data), 'menu');
    }

}
