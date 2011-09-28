<?php
/**
 * Menu
 *
 * @author  Samuel
 * @package Menu
 */

/**
 * Menu component
 *
 * Draws simple menu
 *
 * @author  Samuel
 * @package Menu
 */
use Nette\Application\UI\Control;
use Nette\Utils\Neon;
use Nette\Utils\Finder;
class Menu extends Control
{
    /** array of menu items */
    private $_items;
    private $_htmlClass;

    /**
     * Constructor
     *
     * @param string $descriptionFile path to the file containing menu 
     *                                description
     * @param string $htmlClass       optional css class of the ul
     */
    public function __construct($menuItems, $htmlClass = null)
    {
        $this->_items = $menuItems;
        $this->_htmlClass = $htmlClass;
    }

    /** 
     * Render method
     */
    public function render()
    {
        $template = $this->template;

        /** Set the template file to menu.latte in the current directory*/
        $template->setFile(dirname(__FILE__) . '/menu.latte');
        $template->items = $this->_items;
        $template->class = $this->_htmlClass;
        $template->render();
    }
}
