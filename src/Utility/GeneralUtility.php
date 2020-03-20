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

    /**
     * @param array $array
     * @param string $environmentKey
     * @return array
     */
    public static function mergeArrayWithEnvironmentUrl($array, $environmentKey) {
        if (!empty($_ENV[$environmentKey])) {
            $parseUrl = parse_url($_ENV[$environmentKey]);
            if (!empty($parseUrl['host'])) {
                $array['host'] = $parseUrl['host'];
            }
            if (!empty($parseUrl['port'])) {
                $array['port'] = $parseUrl['port'];
            }
            if (!empty($parseUrl['user'])) {
                $array['user'] = $parseUrl['user'];
            }
            if (!empty($parseUrl['pass'])) {
                $array['pass'] = $parseUrl['pass'];
            }
        }

        return $array;
    }

    /**
     * @return bool
     */
    public static function isSsl() {
        $isHttps = isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1');
        $isHttpsForwarded = isset($_SERVER['HTTP_X_FORWARDED_SSL']) && (strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on' || $_SERVER['HTTP_X_FORWARDED_SSL'] == '1');
        $isSslPort = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443';
        return ($isHttps || $isHttpsForwarded || $isSslPort);
    }
}
