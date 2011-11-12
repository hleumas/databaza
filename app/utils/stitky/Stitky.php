<?php
/**
 * Databaza FKS
 *
 * @author  Samuel
 * @package utils
 */

/**
 * Stitky
 *
 * @author  Samuel
 * @package utils
 */

class Stitky extends \Nette\Object
{
    const HEIGHT = 279;
    const WIDTH  = 210;

    public static function renderStitky($data, $xcount, $ycount)
    {
        $v = round(self::HEIGHT / $ycount, 2);
        $h = round(self::WIDTH / $xcount, 2);

        $rules = "\\def\\vyska{{$v}mm}%\n\\def\\sirka{{$h}mm}%\n";

        foreach ($data as $row) {
            $rules .= '\adresa{'
                   .  implode('}{', $row)
                   .  "}\n";
        }

        $name = array();
        exec("mktemp -d", $name);
        $name = $name[0];
        copy(__DIR__ . "/stitky.tex", "{$name}/stitky.tex");
        file_put_contents($name . '/data.tex', $rules);
        chdir($name);
        exec("cslatex '{$name}/stitky.tex'");
        exec("dvipdf '{$name}/stitky.dvi'");
        $result = tempnam('/tmp', 'sti');
        copy("{$name}/stitky.pdf", $result);
        exec("rm -r $name");
        return $result;
    }

}
