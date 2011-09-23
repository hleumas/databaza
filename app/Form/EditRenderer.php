<?php
/**
 * Databaza FKS
 *
 * @author  Samuel
 * @package Form
 */

/**
 * EditFormRenderer
 *
 * @author  Samuel
 * @package Form
 */

use Nette\Utils\Html;
class EditFormRenderer implements Nette\Forms\IFormRenderer
{
    public function render(\Nette\Forms\Form $form, $mode = null)
    {
        $content = Html::el('form')->action($form->action)->method($form->method);
        foreach ($form->getGroups() as $group) {
            if (!$group->getControls() || !$group->getOption('visual')) {
                continue;
            }
            $elgroup = $content->create('fieldset');
            $grouplabel = $group->getOption('label');
            if (!is_string($grouplabel)) {
                $grouplabel = $grouplabel->getText();
            }
            $elgroup->create('legend', $grouplabel);
            $el = $elgroup->create('dl');

            foreach($group->getControls() as $control) {
                if ($control instanceOf \Nette\Forms\Controls\HiddenField) {
                    $el->add($control->getControl());
                    continue;
                }
                if ($control instanceOf \Nette\Forms\Controls\Button) {
                    $el->create('dt');
                    $el->create('dd')->add($control->getControl());
                    continue;
                }
                $label = $control->getLabel();
                $el->create('dt')->add($label);
                $dd = $el->create('dd');
                $dd->add($control->getControl());
                if ($control->hasErrors())  {
                    $dd->class('error');
                    $errors = $control->getErrors();
                    $dd->create('span class="error"');
                    $dd->create('', $control->errors[0]);
                }
            }
            $elgroup->create('div style="{clear: both;}"');
        }

            return $content;
    }
}
