<?php
require_once 'PHPUnit/Framework.php';
require_once '../../www/index.php';

class FlatArrayTest extends PHPUnit_Framework_TestCase
{
    public static function flatProvider()
    {
        $flat = array('janko.hrasko.zeleny' => 'jhz',
            'janko.hrasko.modry' => 'jhm',
            'janko.hrasko.cerveny' => 'jhc',
            'janko.nehrasko' => 'jneh',
            'janko.bojsa.moc' => 'jbojmo',
            'misko' => 'michal'
        );
        $inflat = array(
            'janko' => array(
                'hrasko' => array('zeleny' => 'jhz', 'modry' => 'jhm', 'cerveny' => 'jhc'),
                'bojsa' => array('moc' => 'jbojmo'),
                'nehrasko' => 'jneh'),
            'misko' => 'michal'
        );
        return array(array($flat, $inflat));
    }

    /**
     * @dataProvider flatProvider
     */
    public function testDeflate($flat, $inflat)
    {
        $this->assertEquals(FlatArray::deflate($inflat), $flat);
    }

    /**
     * @dataProvider flatProvider
     */
    public function testInflate($flat, $inflat)
    {
        $this->assertEquals(FlatArray::inflate($flat), $inflat);
    }
}
