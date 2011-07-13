<?php
/**
 * Databaza FKS
 *
 * @author  Samuel
 * @package Form
 */

/**
 * NeonGriditoFactory
 *
 * @author  Samuel
 * @package Form
 */

use Nette\Utils\Html;
use Nette\Utils\Neon;
use Nette\Application\UI\Form;
use Gridito\Grid;
use Gridito\NetteModel;
class NeonGriditoFactory extends \Nette\Object
{

    public static function createGrid($selection, $neonString)
    {
        $settings = Neon::decode($neonString);

        /** Create Grid and set the model */
        $grid = new Grid();
        $grid->setModel(new NetteModel($selection));

        /** Add columns */
        foreach ($settings['columns'] as $key => $column) {
            $grid->addColumn($key, null, $column);
        }

        /** Create buttons */
        foreach ($settings['buttons'] as $key => $button) {
            $call  = empty($button['toolbar']) ? 'add' : 'addToolbar';
            $call .= empty($button['window']) ? ''     : 'Window';
            $call .= empty($button['check']) ? 'Button': 'CheckButton';
            unset($button['toolbar']);
            unset($button['window']);
            unset($button['check']);

            call_user_func(array($grid, $call), $key, null, $button);
        }

        return $grid;

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

                $fctrl = call_user_func_array(
                    callback($container, 'add' . ucfirst($type)), $params);
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
