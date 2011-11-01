<?php
namespace AdminModule;

/**
 * Databaza FKS
 *
 * @package    Presenters
 */



use Gridito\Grid;
use Gridito\FKSVysledkovka;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Utils\Neon;
/**
 * Zoznamy presenter.
 *
 * @author     Samuel
 * @package    Presenters
 */
class VysledkovkaPresenter extends BasePresenter
{

    /** @persistent */
    public $seria;

    public function getSerie()
    {
        $lastSeriaId = $this['seriaSelector']->seria;
        $sql = <<<SQL
SELECT id FROM
seria,(SELECT semester_id, cislo from seria where id=2) t
WHERE seria.semester_id=t.semester_id AND seria.cislo <= t.cislo
SQL;
        $data = $this->context->database->fetchAll($sql, $lastSeriaId);
        if ($data === false) {
            return array();
        }
        $serie = array();
        foreach ($data as $seria) {
            $serie[] = $seria['id'];
        }
        return $serie;
            
    }
    public function createComponentSeriaSelector()
    {
        $sources = $this->context->sources;
        return new \SeriaSelector(
            $this->seria,
            $sources->seriaSource,
            $sources->semesterSource,
            $sources->kategoria);
    }

    public function createComponentGridA()
    {
        $grid = new Grid();
        $grid->setModel(new FKSVysledkovka(
            $this->context->database,
            $this->getSerie(),
            FKSVysledkovka::A
        ));
        $grid->addColumn('poradie', 'por');
        $grid->addColumn('meno', 'Meno');
        $grid->addColumn('priezvisko', 'Priezvisko');
        $grid->addColumn(4, 'A1');
        $grid->addColumn(5, 'A2');
        $grid->addColumn(6, 'A3');
        $grid->addColumn(7, 'A4');
        $grid->addColumn('sum', 'SUM');
        $grid->addColumn('total', 'Spolu');
        return $grid;
    }

    public function createComponentGridB()
    {
        $grid = new Grid();
        $grid->setModel(new FKSVysledkovka(
            $this->context->database,
            $this->getSerie(),
            FKSVysledkovka::B
        ));
        $grid->addColumn('poradie', 'por');
        $grid->addColumn('meno', 'Meno');
        $grid->addColumn('priezvisko', 'Priezvisko');
        $grid->addColumn(1, 'B0');
        $grid->addColumn(2, 'B1');
        $grid->addColumn(3, 'B2');
        $grid->addColumn(4, 'B3');
        $grid->addColumn(5, 'B4');
        $grid->addColumn('sum', 'SUM');
        $grid->addColumn('total', 'Spolu');
        return $grid;
    }
}
