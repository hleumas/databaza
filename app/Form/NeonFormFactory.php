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
        'selectBox'   => array('label', 'items', 'size'),
        'multiSelect' => array('label', 'items', 'size'),
        'submit'      => array('label'),
        'button'      => array('label'));

    public static function createForm($neonString)
    {
        $config = Neon::decode($neonString);
        $form = new Form();
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
                foreach (self::$controls[$type] as $param) {
                    $params[] = isset($control[$param]) ? $control[$param] : null;
                }

                call_user_func_array(callback($container, 'add' . ucfirst($type)), $params);
            } elseif ($type == 'container') {
                self::addControls($container->addContainer($name), $control);
            } elseif ($type == 'group') {
                $container->addGroup($name);
            }
        }
    }

}
