<?php
/**
 * SeriaSelector
 *
 * @author  Samuel
 * @package SeriaSelector
 */

/**
 * SeriaSelector component
 *
 * Draws simple seria selector
 *
 * @author  Samuel
 * @package Menu
 */
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
class SeriaSelector extends Control
{
    /** array of menu items */
    private $seriaSource;
    private $semesterSource;
    private $selected;
    private $kategoria;

    /**
     * Constructor
     *
     * @param int            $selected
     * @param SeriaSource    $seriaSource
     * @param SemesterSource $semesterSource
     */
    public function __construct($selected, $seriaSource, $semesterSource, $kategoria)
    {
        $this->seriaSource = $seriaSource;
        $this->semesterSource = $semesterSource;
        $this->kategoria = $kategoria;
        $this->selected = $this->getSelected($selected);
    }

    public function createComponentForm()
    {
        $form = new Form();
        $form->setMethod('get')
             ->setAction($this->parent->link('this'));
        $semestre = FlatArray::toArray($this->semesterSource->getAll());
        $serie = $this->seriaSource->getAll();
        $items = array();
        foreach ($semestre as &$sem) {
            $sem['serie'] = array();
        }
        foreach ($serie as $seria) {
            $semestre[$seria['semester']]['serie'][] = $seria;
        }
        foreach ($semestre as $semester) {
            $name = "{$semester['rok']} "
                  . (($semester['cast'] == 1) ? 'leto' : 'zima');
            $items[$name] = array();
            foreach ($semester['serie'] as $seria) {
                $items[$name][$seria['id']] = "{$seria['cislo']}. séria";
            }
        }
        $form->addSelect('seria', 'Séria:', $items)
            ->setValue($this->selected);
        $form->addSubmit('submit', 'Načítaj');
                
        return $form;
    }
    private function getSelected($selected)
    {
        $serie = $this->seriaSource->getAll();
        if (isset($serie[$selected])) {
            return $selected; 
        }
        return $this->kategoria->aktualna_seria_id;
    }

    public function getSeria()
    {
        return $this->selected;
    }

    /** 
     * Render method
     */
    public function render()
    {
        $template = $this->template;

        /** Set the template file to menu.latte in the current directory*/
        $template->setFile(dirname(__FILE__) . '/seriaSelector.latte');
        $template->render();
    }
}
