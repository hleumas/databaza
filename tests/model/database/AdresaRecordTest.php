<?php
require_once 'PHPUnit/Framework.php';
require_once '../../../www/index.php';

use Nette\Utils\Strings;
class AdresaRecordTest extends PHPUnit_Framework_TestCase
{

    public $correctAdresa = array(
        'ulica' => 'Borská 15',
        'mesto' => 'Bratislava',
        'stat'  => 'SR',
        'psc'   => '12345'
    );

    public static function arrayize($array)
    {
        $pass = array();
        foreach ($array as $elem) {
            $pass[] = array($elem);
        }
        return $pass;
    }
    public static function correctPscProvider()
    {
        $data = array('83 231', '12345', '  1 2 3 4 5',
            "1\n3421", '99999 ', ' 19352 ', '874 2 1', '00000',
            '01234', '02 547');
        return self::arrayize($data);
    }

    public static function invalidPscProvider()
    {
        $data = array('8432', '  1 2 3 4', '1234 ', 'a1235', '542983',
            'abcdef', '', '1');
        return self::arrayize($data);
    }
    public function setUp()
    {
        $this->record = new AdresaRecord;
    }

    public static function junkProvider()
    {
        $data = array(
            array(array('ulica'=>'Janovská', 'proteza' =>'simple'),
            array('id' => null, 'ulica' => 'Janovská',
            'mesto' => null, 'psc' => null, 'stat' => null, 'organizacia' => null)));
        return $data;
    }

    /**
     * @dataProvider junkProvider
     */
    public function testSetter($set, $result)
    {
        $this->record->setData($set);
        $a = array();
        foreach ($this->record as $key => $elem) {
            $a[$key] = $elem;
        }
        $this->assertEquals($a, $result);
    }

    /**
     * @dataProvider correctPscProvider
     */
    public function testCorrectPSCValidation($psc)
    {
        $adresa = $this->correctAdresa;
        $adresa['psc'] = $psc;
        $this->record->setData($adresa);
        $this->record->validate();

    }

    /**
     * @dataProvider invalidPscProvider
     * @expectedException InvalidDataException
     */
    public function testInvalidPscValidation($psc)
    {
        $adresa = $this->correctAdresa;
        $adresa['psc'] = $psc;
        $this->record->setData($adresa);
        $this->record->validate();
    }

    public static function validAdresyProvider()
    {
        $data = array(
            array('organizacia' => 'SS a MS', 'ulica' => 'Jankova 14', 'mesto' => 'ba', 'stat' => 'sr', 'psc' => '12345'),
            array('mesto' => 'br', 'organizacia' => ' SS a MS ', 'ulica' => '   J  ', 'stat' => 'sr', 'psc' => '12345'),
            array('mesto' => ' ba', 'organizacia' => '', 'ulica' => 'Jankova 14', 'stat' => 'sr', 'psc' => '12345'),
            array('mesto' => 'Bratislava  ', 'id' => '13', 'organizacia' => null, 'ulica' => 'Jankova 14', 'stat' => 'sr', 'psc' => '12345'));
        return self::arrayize($data);
    }

    public static function invalidAdresyProvider()
    {
        $data = array(
            array('organizacia' => 'SS a MS', 'ulica' => 'Jankova 14', 'mesto' => 'ba', 'stat' => '  ', 'psc' => '12345'),
            array('mesto' => 'br', 'organizacia' => ' SS a MS ', 'ulica' => '   J  ', 'stat' => "\n  ", 'psc' => '12345'),
            array('mesto' => null, 'organizacia' => '', 'ulica' => 'Jankova 14', 'stat' => 'sr', 'psc' => '12345'),
            array('id' => '13', 'organizacia' => 'SS a MS', 'ulica' => 'Jankova 14', 'stat' => 'sr', 'psc' => '12345'));
        return self::arrayize($data);
    }
    /**
     * @dataProvider validAdresyProvider
     */
    public function testValidAdresy($adresa)
    {
        $this->record->setData($adresa);
        $this->record->validate();
    }

    /**
     * @dataProvider invalidAdresyProvider
     * @expectedException InvalidDataException
     */
    public function testInvalidAdresy($adresa)
    {
        $this->record->setData($adresa);
        $this->record->validate();
    }


}
