<?php
/**
 * Databaza FKS
 *
 * @author  Samuel
 * @package Form
 */

/**
 * NeonFormFactory
 *
 * @author  Samuel
 * @package Form
 */

use Nette\Utils\Html;
use Nette\Utils\Neon;
use Nette\Application\UI\Form;
class NeonFormFactory extends \Nette\Object
{
    private static $properties = array('method', 'action');
    private static $controls   = array(
        'text'        => array('label', 'cols', 'maxLength'),
        'password'    => array('label', 'cols', 'maxLength'),
        'textArea'    => array('label', 'cols', 'rows'),
        'upload'      => array('label'),
        'hidden'      => array(),
        'checkbox'    => array('label'),
        'radiolist'   => array('label', 'items'),
        'select'      => array('label', 'items', 'size', 'setPrompt' => 'prompt'),
        'multiSelect' => array('label', 'items', 'size'),
        'submit'      => array('label'),
        'button'      => array('label'));

    public static function createForm($neonString)
    {
        $config = Neon::decode($neonString);
        $form = new Form();
        if (is_null($config)) {
            return $form;
        }
        foreach (self::$properties as $atr) {
            if (isset($config[$atr])) {
                $form->$atr = $config[$atr];
            }
        }

        if (isset($config['renderer'])) {
            $renderer = new $config['renderer']['class'];
            $form->renderer = $renderer;
        }
        self::addControls($form, $config);
        return $form;

    }

    public static function addControls($container, $config)
    {
        foreach ($config as $name => $control) {
            if (!isset($control['type'])) {
                continue;
            }
            $type = $control['type'];
            if (isset(self::$controls[$type])) {
                $params = array($name);
                $setters = array();
                foreach (self::$controls[$type] as $key => $param) {
                    if (is_numeric($key)) {
                        $params[] = isset($control[$param]) ? $control[$param] : null;
                    } elseif (isset($control[$param])) {
                        $setters[$key] = $control[$param];
                    }
                }

                $fctrl = call_user_func_array(
                    callback($container, 'add' . ucfirst($type)), $params);
                foreach ($setters as $method => $value) {
                    call_user_func(array($fctrl, $method), $value);
                }
                if (isset($control['required'])) {
                    $fctrl->setRequired($control['required']);
                }
            } elseif ($type == 'container') {
                self::addControls($container->addContainer($name), $control);
            } elseif ($type == 'group') {
                $container->addGroup($name);
            }
        }
    }

}
