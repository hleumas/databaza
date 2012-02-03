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
    const HEIGHT = 297;
    const WIDTH  = 210;

    public static function renderStitky($data, $xcount, $ycount, $m_top = 0, $m_right = 0, $m_bottom = 0, $m_left = 0)
    {
        $height = self::HEIGHT;
        $width  = self::WIDTH;
        $theight = self::HEIGHT - $m_top - $m_bottom - 7;
        $twidth  = self::WIDTH - $m_left - $m_right;
        $v = round($theight / $ycount, 2) - 0.1;
        $h = round($twidth / $xcount, 2) - 0.1;

        $rules  = "\initpage{{$height}mm}{{$width}mm}{{$m_left}mm}{{$m_right}mm}{{$m_top}mm}{{$m_bottom}mm}\n";
        $rules .= "\\def\\vyska{{$v}mm}%\n\\def\\sirka{{$h}mm}%\n";
        $rules .= "\begin{document}\n\\noindent";

        foreach ($data as $row) {
            $rules .= '\adresa{'
                   .  implode('}{', $row)
                   .  "}\n";
        }
        $rules .= "\end{document}\n";

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
