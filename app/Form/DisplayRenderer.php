<?php
/**
 * Databaza FKS
 *
 * @author  Samuel
 * @package Form
 */

/**
 * DisplayFormRenderer
 *
 * @author  Samuel
 * @package Form
 */

use Nette\Utils\Html;
class DisplayFormRenderer implements Nette\Forms\IFormRenderer
{
    public function render(\Nette\Forms\Form $form, $mode = null)
    {
        $content = Html::el('div class="details"');
        foreach ($form->getGroups() as $group) {
            if (!$group->getControls() || !$group->getOption('visual')) {
                continue;
            }
            $elgroup = $content->create('div class="viewGroup"');
            $grouplabel = $group->getOption('label');
            if (!is_string($grouplabel)) {
                $grouplabel = $grouplabel->getText();
            }
            $elgroup->create('h5', $grouplabel);
            $el = $elgroup->create('dl');

            foreach($group->getControls() as $control) {
                if ($control instanceOf \Nette\Forms\Controls\HiddenField
                    || $control instanceOf \Nette\Forms\Controls\Button) {
                    continue;
                }
                $label = $control->getLabel();
                if (!is_string($label)) {
                    $label = $label->getText();
                }
                $el->create('dt', $label);
                $value = $control->getValue();
                if ($control instanceOf \Nette\Forms\Controls\Checkbox) {
                    if ($value) {
                        $el->create('dd')->create('span class="yes"');
                    } else {
                        $el->create('dd')->create('span class="no"');
                    }

                } elseif ($control instanceOf \Nette\Forms\Controls\SelectBox) {
                    $items = FlatArray::getLeafs($control->getItems());
                    if (!isset($items[$value])) {
                        $el->create('dd class="na"', 'n/a');
                        continue;
                    }
                    $value = $items[$value];
                    if (!is_string($value)) {
                        $value = $value->getTitle();
                    }
                    $el->create('dd', $value);
                } else {
                    if ($value !== '') {
                        $el->create('dd', $value);
                    } else {
                        $el->create('dd class="na"', 'n/a');
                    }
                }
            }
            $elgroup->create('div style="{clear: both;}"');
        }

            return $content;
    }
}
