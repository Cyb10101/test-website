<?php
namespace App\Utility;

class GeneralUtility {
    public static function getContentByTag($content, $tag) {
        if (preg_match_all('/<'.$tag.'[^>]*>(.*?)<\/'.$tag.'>/is', $content, $match)) {
            return $match;
        }
        return false;
    }

    public static function generateImageTag($filename, $type) {
        if (is_readable($filename)) {
            $handle = fopen($filename, 'rb');
            $content = '';
            while (!feof($handle)) {
                $content .= fread($handle, 1024);
            }
            fclose($handle);
            $base64 = base64_encode($content);
            return '<img style="max-width: 100%; max-height: 500px;" src="data:' . $type . ';base64,' . $base64 . '">';
        }
        return '';
    }

    public static function testDatabaseConnection($host, $username, $password) {
        if (class_exists('mysqli')) {
            try {
                $mysqli = new \mysqli($host, $username, $password);
                if ($mysqli->connect_errno) {
                    return 'Failed to connect to MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error;
                }
                return $mysqli->host_info;
            } catch (\Exception $exception) {
                return $exception->getMessage();
            }
        }
        return 'MySQLi does not exists!';
    }
}
