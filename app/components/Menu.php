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
    public function __construct($descriptionFile, $htmlClass = null)
    {
        if (!is_file($descriptionFile)) {
            throw new Nette\FileNotFoundException("File $descriptionFile does not exists!");
        }
        $data = file_get_contents($descriptionFile);
        if ($data === false) {
            throw new Nette\FileNotFoundException("File $descriptionFile is not readable!");
        }
        $this->_items = Neon::decode($data);
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
