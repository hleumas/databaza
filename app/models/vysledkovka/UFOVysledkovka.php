<?php

namespace Gridito;

use Nette\Database\Table\Selection;
use \Nette\Utils\Strings;

/**
 * FullTextModel
 *
 * @author Samuel Hapak
 * @license MIT
 */
class UFOVysledkovka extends VysledkovkaModel
{

    private $pCount = 4;
    private $priklady = array(1, 2, 3, 4);

    private $seriaId;

	/**
	 * Constructor
     *
     * @param       $dbConection connection to database
     * @param array $serie       ids of all series to take into account
	 */
	public function __construct($dbConection, $serie)
	{
        parent::__construct($dbConection, $serie, $this->pCount);
	}

    protected function getRiesitelia($dbConection, $serie)
    {
        $sql = <<<SQL
SELECT riesitel_seria.riesitel_id as id, riesitel_seria.seria_id,
riesitel_seria.bonus, riesitel_seria.meskanie, riesitel.typ_studia_id,
riesitel.rok_maturity, riesitel.telefon_rodic, osoba.telefon, osoba.email, 
semester.rok, semester.cast, osoba.meno, osoba.priezvisko, skola.skratka
FROM riesitel_seria LEFT JOIN riesitel ON
riesitel_seria.riesitel_id = riesitel.id
LEFT JOIN osoba ON
riesitel.osoba_id = osoba.id
LEFT JOIN seria ON
seria.id = riesitel_seria.seria_id
LEFT JOIN semester ON
semester.id = seria.semester_id
LEFT JOIN skola ON
riesitel.skola_id = skola.id
WHERE riesitel_seria.seria_id IN (?)
SQL;
        return $dbConection->fetchAll($sql, $serie);
    }

    protected function filterRiesitelia($results)
    {
        return $results;
    }


    public function getSum($riesitel)
    {
        $sum = 0;
        foreach ($this->priklady as $priklad) {
            $sum += is_numeric($riesitel[$priklad])
                ? $riesitel[$priklad]
                : 0;
        }
        return $sum;
    }

    protected function getEmptyRiesitel($riesitel)
    {
        $result = \FlatArray::toArray($riesitel);
        $result['meskanie'] = 0;
        $result['bonus'] = 0;
        return $result;
    }

    protected function getBonus($riesitel)
    {
        $rokyDoMaturity = $riesitel['rok_maturity'] - $riesitel['rok'] - $riesitel['cast'] + 2;
        $hodnota = $riesitel['sum'] * (36-$riesitel['sum']);
        if ($rokyDoMaturity == 6)  {
            return round(0.008 * $hodnota, 2);
        }
        if ($rokyDoMaturity > 6) {
            return round(0.015 * $hodnota, 2);
        }
        return 0;
    }

}
