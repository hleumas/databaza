<?php
/**
 * Databaza FKS
 *
 * @author  Samuel
 * @package utils
 */

/**
 * FlatArray
 *
 * @author  Samuel
 * @package utils
 */

use Nette\Utils\Html;
use Nette\Utils\Neon;
use Nette\Application\UI\Form;
class FlatArray extends \Nette\Object
{
    public static function getLeafs($array)
    {
        $leafs = array();
        foreach ($array as $item) {
            if (is_array($item) || $item instanceOf Traversable) {
                $item = self::getLeafs($item);
            } else {
                $item = array($item);
            }
            $leafs = array_merge($leafs, $item);
        }
        return $leafs;
    }
    public static function toArray($iterator)
    {
        $array = array();
        foreach ($iterator as $key => $value) {
            if ($value instanceOf Traversable || is_array($value)) {
                $value = self::toArray($value);
            }
            $array[$key] = $value;
        }
        return $array;
    }
    public static function deflate($array, $prefix='')
    {
        $deflated = array();
        foreach ($array as $key => $elem) {
            if ($elem instanceOf Traversable || is_array($elem)) {
                $deflated = array_merge($deflated, self::deflate($elem, "$prefix$key."));
            } else {
                $deflated["$prefix$key"] = $elem;
            }
        }
        return $deflated;
    }
    public static function inflate($array)
    {
        $inflated;
        foreach ($array as $key => $value) {
            $cur = &$inflated;
            $curKey = $key;
            $pos = strpos($curKey, '.');
            while ($pos !== false) {
                $preKey = substr($curKey, 0, $pos);
                $curKey = substr($curKey, $pos+1);
                if (!array_key_exists($preKey, $cur)) {
                    $cur[$preKey] = array();
                }
                $cur = &$cur[$preKey];
                $pos = strpos($curKey, '.');
            }
            $cur[$curKey] = $value;
        }
        return $inflated;
    }
}
