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
        $content = Html::el('form class="edit"');
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
                    continue;
                }
                if ($control instanceOf \Nette\Forms\Controls\Button) {
                    $el->create('dt');
                    $el->create('dd')->add($control->getControl());
                    continue;
                }
                $label = $control->getLabel();
                if (!is_string($label)) {
                    $label = $label->getText();
                }
                $el->create('dt', $label);
                $el->create('dd')->add($control->getControl());
                $value = $control->getValue();
            }
            $elgroup->create('div style="{clear: both;}"');
        }

            return $content;
    }
}
