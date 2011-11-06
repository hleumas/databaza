<?php
namespace AdminModule;

/**
 * Databaza FKS
 *
 * @package    Presenters
 */



use Gridito\Grid;
use Gridito\FKSVysledkovka;
use Gridito\UFOVysledkovka;
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

    private $priklady = array(
        'vysledkovkaFKSA' => array(4, 5, 6, 7),
        'vysledkovkaFKSB' => array(1, 2, 3, 4, 5),
        'vysledkovkaUFO'  => array(1, 2, 3, 4)
    );

    public function getSerie()
    {
        $lastSeriaId = $this['seriaSelector']->seria;
        $sql = <<<SQL
SELECT id FROM
seria,(SELECT semester_id, cislo from seria where id=?) t
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

    public function renderDefault()
    {
        $this->template->fks = ($this->context->sources->kategoria->nazov == 'FKS');
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

    public function getVysledkovkaTemplate($name)
    {
        $file = __DIR__ . "/../templates/Vysledkovka/$name.neon";
        if (!is_file($file) || !is_readable($file)) {
            throw new \Nette\FileNotFoundException("File $file is missing or is not readable.");
        }
        return $file;
    }
    public function createComponent($name)
    {
        if (Strings::startsWith($name, 'vysledkovka')) {
            return $this->createComponentVysledkovka($name);
        }
        return parent::createComponent($name);
    }

    public function getVysledkovkaModel($name)
    {
        $db = $this->context->database;
        $serie = $this->getSerie();
        switch($name) {
        case 'vysledkovkaFKSA':
            return new FKSVysledkovka($db, $serie, FKSVysledkovka::A);
        case 'vysledkovkaFKSB':
            return new FKSVysledkovka($db, $serie, FKSVysledkovka::B);
        case 'vysledkovkaUFO':
            return new UFOVysledkovka($db, $serie);
        default:
            throw new Exception("Neznama vysledkovka");
        }
    }
    public function createComponentVysledkovka($name)
    {
        $file  = $this->getVysledkovkaTemplate($name);
        $model = $this->getVysledkovkaModel($name);
        $grid = \NeonGriditoFactory::createGrid(
            $model,
            file_get_contents($file)
        );
        $grid['columns']->getComponent('rocnik')->setRenderer(function($row) {
            return $row['typ_studia_id'] - $row['rok_maturity'] + $row['rok'] + $row['cast'] - 1;
        });
        $grid['toolbar']->getComponent('download')->setHandler(callback($this, 'download' . ucfirst($name)));
        //$grid->addColumn('rocnik', 'Ročník')->setRenderer(
        return $grid;
    }

    public function downloadVysledkovkaFKSA()
    {
        $this->actionDownload('vysledkovkaFKSA');
    }

    public function downloadVysledkovkaFKSB()
    {
        $this->actionDownload('vysledkovkaFKSB');
    }

    public function downloadVysledkovkaUFO()
    {
        $this->actionDownload('vysledkovkaUFO');
    }

    public function getTexVysledkovka($name)
    {
        $riesitelia = $this->getVysledkovkaModel($name)->getItems();
        $tex = '';
        foreach ($riesitelia as $riesitel) {
            $tex .= implode('&', array(
                $riesitel['poradie'],
                $riesitel['meno'] . ' ' . $riesitel['priezvisko'],
                $riesitel['skratka']
            ));
            foreach ($this->priklady[$name] as $priklad) {
                $tex .= "&{$riesitel[$priklad]}";
            }
            $tex .= '&';
            $tex .= implode('&', array(
                $riesitel['bonus'], 
                number_format($riesitel['sum'], 2, ',', ' '),
                number_format($riesitel['total'], 2, ',', ' ')
            ));
            $tex .= "\\\\\n";
        }
        return $tex;

    }

    public function actionDownload($name)
    {
        $file = tempnam('/tmp', 'vys');
        file_put_contents($file, $this->getTexVysledkovka($name));
        $response = new \Nette\Application\Responses\FileResponse($file, "$name.tex");
        $this->sendResponse($response);
        unlink($file);
    }
}
