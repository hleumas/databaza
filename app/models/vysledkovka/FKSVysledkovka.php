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
class FKSVysledkovka extends VysledkovkaModel
{

    const A = 1;
    const B = 2;
    private $pCount = 7;
    private $category;
    private $priklady = array(
        self::B => array(2, 3, 4, 5),
        self::A => array(4, 5, 6, 7)
    );

    private $seriaId;

	/**
	 * Constructor
     *
     * @param int   $seriaId id of main seria
     * @param array $serie   ids of all series to take into account
     * @param int   $pCount  number of priklady
	 */
	public function __construct($dbConection, $serie, $category)
	{
        $this->priklady = $this->priklady[$category];
        $this->category = $category;
        parent::__construct($dbConection, $serie, $this->pCount);
	}

    protected function getRiesitelia($dbConection, $serie)
    {
        $sql = <<<SQL
SELECT riesitel_seria.riesitel_id as id, riesitel_seria.seria_id,
riesitel_seria.bonus, riesitel_seria.meskanie, riesitel.typ_studia_id,
riesitel.rok_maturity, riesitel.sustredeni, riesitel.vyhier,
riesitel.celostatiek, semester.rok, semester.cast, osoba.meno, osoba.priezvisko, skola.skratka
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
        $filter = $this->category === self::A
            ? callback($this, 'filterA')
            : callback($this, 'filterB');
        foreach ($results as $id => $riesitel) {
            if (!$filter($riesitel)) {
                unset($results[$id]);
            }
        }
        return $results;
    }

    public function filterA($riesitel)
    {
        if (!$this->filterB($riesitel)) {
            return true;
        }
        if (isset($riesitel['sent6']) || isset($riesitel['sent7'])) {
            return true;
        }
        return false;
    }
    public function filterB($riesitel)
    {
        if ($riesitel['sustredeni'] > 2 || $riesitel['celostatiek'] > 0) {
            return false;
        }

        if (!isset($riesitel['sent1']) && !isset($riesitel['sent2']) && !isset($riesitel['sent3'])) {
            return false;
        }

        /** 1 = maturuje tento rok */
        $rokyDoMaturity = $riesitel['rok_maturity'] - $riesitel['rok'] - $riesitel['cast'] + 2;

        if ($riesitel['vyhier'] > 0 && $rokyDoMaturity <= 2) {
            return false;
        }

        return true;
    }

    public function getSum($riesitel)
    {
        $body = array();
        foreach ($this->priklady as $priklad) {
            $body[] = is_numeric($riesitel[$priklad])
                ? $riesitel[$priklad]
                : 0;
        }

        if ($this->category === self::B
            && $riesitel['sustredeni'] == 0
            && is_numeric($riesitel[1])) {
            $body[] = $riesitel[1];
        }
        rsort($body);

        $sum = 0;
        for ($i = 0; $i < 4; $i++) {
            $sum += $body[$i];
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
        //return $riesitel['bonus'] - 2 * $riesitel['meskanie'];
        //dump($riesitel);
        return 0;
    }

}
