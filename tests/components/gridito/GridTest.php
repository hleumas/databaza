<?php
require_once 'PHPUnit/Framework.php';
require_once '../../../www/index.php';

use Nette\Utils\Strings;
use Gridito\Grid;
class GridTest extends PHPUnit_Framework_TestCase
{

    public static function formatProvider()
    {
        $values = array('prdel' => 'zadok', 'oko' => 'ucho', 'noha' => '',
            'ruka' => null, 'mozog' => 'sval');
        return array(
            array($values, 'Videl som ti %prdel%. Až mi z toho %noha% odpadla.',
            'Videl som ti zadok. Až mi z toho  odpadla.'),
            array($values, '%%%mozog%%% je velmi dolezity %%organ%%. A tak %%.',
            '%sval% je velmi dolezity %organ%. A tak %.'));
    }

    /**
     * @dataProvider formatProvider
     */
    public function testFormat($record, $format, $result)
    {
        $this->assertEquals(Grid::formatRecordString($record, $format), $result);
    }
 

}
