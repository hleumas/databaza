<?php

/**
 * FKS databaza
 *
 * @author Samuel
 *
 */

/**
 * SubmitHandler
 *
 * @authore Samuel
 * @package database
 */

use \Nette\Object;
use \Nette\Database\Connection;
use \Nette\Utils\Strings;
class SubmitHandler extends Object
{

    private $_dbConnection;
    private $_riesitelSeriaSource;

    public function __construct($dbConnection, $riesitelSeriaSource)
    {
        $this->_dbConnection = $dbConnection;
        $this->_riesitelSeriaSource = $riesitelSeriaSource;
    }

    private function getData($prikladId)
    {
        $sql = <<<SQL
SELECT priklady_files.id, priklady_files.riesitel_id, priklady_files.priklad_id, 
filename, content, body, meno, priezvisko
FROM priklady_files LEFT JOIN riesitel_priklady
ON riesitel_priklady.priklad_id = priklady_files.priklad_id
AND riesitel_priklady.riesitel_id = priklady_files.riesitel_id
LEFT JOIN riesitel ON riesitel.id = priklady_files.riesitel_id
LEFT JOIN osoba ON riesitel.osoba_id = osoba.id
WHERE priklady_files.priklad_id = ?
SQL;
        $rows = $this->_dbConnection->fetchAll($sql, $prikladId);
        $riesitelia = array();
        foreach ($rows as $row) {
            if (is_null($row['body'])) {
                $row['body'] = '*';
            }
            if (!isset($riesitelia[$row['riesitel_id']])) {
                foreach (array('priklad_id', 'meno', 'priezvisko', 'body') as $key) {
                    $riesitelia[$row['riesitel_id']][$key] = $row[$key];
                }
                $riesitelia[$row['riesitel_id']]['files'] = array();
            }
            $riesitelia[$row['riesitel_id']]['files'][] = $row;
        }
        return $riesitelia;

    }

    private function fillArchiv($data, $archiv)
    {
        $mainDir = 'riesenia';
        $archiv->addEmptyDir($mainDir);
        $archiv->addFile(__DIR__ . '/SubmitHandlerNavod', "$mainDir/citajMa.txt");
        foreach($data as $rid => $riesitel) {
            $riesitelDir = Strings::webalize("$rid-"
                         . $riesitel['priklad_id'] . '-'
                         . $riesitel['meno'] . '-'
                         . $riesitel['priezvisko'],
                         '._-', false);
            $path = "$mainDir/$riesitelDir";
            $archiv->addEmptyDir("$path");
            $archiv->addFromString("$path/body.txt", $riesitel['body']);
            foreach ($riesitel['files'] as $file) {
                $filename = Strings::webalize(
                    "{$file['id']}-{$file['filename']}",
                    '._', false);
                $archiv->addFromString("$path/$filename", $file['content']);
            }
        }
    }
    public function getArchiv($prikladId)
    {
        $archivName = tempnam('/tmp', 'db');
        $archiv = new ZipArchive();
        if ($archiv->open($archivName, ZIPARCHIVE::CREATE) !== true) {
            throw new Exception("Cannot open $archivName");
        }
        $this->fillArchiv(
            $this->getData($prikladId),
            $archiv);

        $archiv->close();
        return $archivName;
    }

    public function saveArchiv($archivName)
    {
        $zip = new \ZipArchive();
        $zip->open($archivName);
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $match = (Strings::match(
                $stat['name'],
                '#[^/]+/([0-9]+)-([0-9]+)-[^/]*/(([0-9]+)-[^/]*|body\.txt)$#'
            ));
            if (is_null($match)) {
                continue;
            }

            if ($match[3] === 'body.txt') {
                $body = $zip->getFromIndex($i);
                $body = Strings::match(
                    $zip->getFromIndex($i),
                    '#^\s*([0-9]+)\s*$#'
                );
                $body = is_null($body) ? null : (int)$body[1];

                $this->_riesitelSeriaSource
                    ->setPrikladById($match[1], $match[2], $body);
            } else {
                $file = array(
                    'filesize' => $stat['size'],
                    'content' => $zip->getFromIndex($i)
                );
                $this->_dbConnection->exec(
                    'UPDATE priklady_files SET ? WHERE id=?',
                    $file,
                    $match[4]
                );
            }

        }
    }

}
