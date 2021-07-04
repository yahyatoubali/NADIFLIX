<?php

/**
 * ====================================================================================
 *                           Google Drive Proxy Player (c) CodySeller
 * ----------------------------------------------------------------------------------
 * @copyright This software is exclusively sold at codester.com. If you have downloaded this
 *  from another site or received it from someone else than me, then you are engaged
 *  in an illegal activity. You must delete this software immediately or buy a proper
 *  license from https://www.codester.com/codyseller?ref=codyseller.
 *
 *  Thank you for your cooperation and don't hesitate to contact me if anything :)
 * ====================================================================================
 *
 * @author CodySeller (http://codyseller.com)
 * @link http://codyseller.com
 * @license http://codyseller.com/license
 */


class Helper
{

    public function __construct()
    {
        
    }


    public static function clean($data) {
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);
        // we are done...
        return trim($data);
    }

    public static function getUserAgent()
    {
        $ua ='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36';
        return $ua;
    }

    public static function isUrl($url) {
        if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url) && filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        }
        return false;
    }


    public static function isI($url) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_USERAGENT, self::getUserAgent());
        // curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_ENCODING,  'gzip,deflate');
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        return $info["http_code"];
    }

    public static function curl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, self::getUserAgent());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch,CURLOPT_ENCODING , 'gzip');
        curl_setopt($ch,CURLOPT_CAINFO, NULL);
        curl_setopt($ch,CURLOPT_CAPATH, NULL);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


    public static function e($str)
    {
        return openssl_encrypt($str,"AES-128-ECB",_SEC_LOCK);
    }

    public static function d($str)
    {
        return openssl_decrypt($str,"AES-128-ECB",_SEC_LOCK);
    }

    public static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function tnow()
    {
        $dt = new DateTime("now");
        return $dt->format('Y-m-d H:i:s');
    }

    public static function getVInfo($url, $key)
    {
        $headers = [];

        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Pragma: no-cache';
        
        session_write_close();
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, self::getUserAgent());
        curl_setopt($ch, CURLOPT_COOKIEFILE, ROOT  . '/data/cookiz/gdrive~'.$key.'.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 8192);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_exec($ch);
        $fsize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $ftype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return [
            'fsize' => $fsize,
            'ftype' => $ftype
        ];

    }

    public static function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public static function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }


    public static function getReqData($req)
    {
        $resp = '';
        if(self::isPost())
        {
            if(isset($_POST[$req]))
            {
                $resp = $_POST[$req];
            }
        }
        else if(self::isGet())
        {
            if(isset($_GET[$req]))
            {
                $resp = $_POST[$req];
            }
        }

        return !is_array($resp) ? self::clean($resp) : $resp;
    }

    

    public static function isValidHost($host)
    {
        $hosts = [
            GDRIVE_IDENTIFY => 'gdrive',
            '__002' => 'gphoto'
        ];

        if(array_key_exists($host, $hosts))
        {
            return true;
        }
        return false;

    }


    public static function formatDT($dt, $r = true)
    {
        if($r)
        {
            return date("F jS, Y - h:i A", strtotime($dt));
        }
        else
        {
            return date("F jS, Y", strtotime($dt));
        }
        
    }

    
    public static function redirect($url = '', $fullurl = FALSE, $message = array(), $header = "") {
        if (!empty($message)) {
            $_SESSION["msg"] = self::clean("{$message[0]}::{$message[1]}", 2);
        }
        switch ($header) {
            case '301':
                header('HTTP/1.1 301 Moved Permanently');
            break;
            case '404':
                header('HTTP/1.1 404 Not Found');
            break;
            case '503':
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 60');
            break;
        }
        if ($fullurl) {
            header("Location: $url");
            exit;
        }
        header("Location: " . PROOT . "/$url");
        exit;
    }

    public static function getDriveId($url) 
    {
        $path = explode('/', parse_url($url) ['path']);
        return (isset($path[3]) && !empty($path[3])) ? $path[3] : '';
    }

    public static function getOS() { 

        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    
        $os_platform  = "Unknown OS Platform";
    
        $os_array     = array(
                              '/windows nt 10/i'      =>  'Windows 10',
                              '/windows nt 6.3/i'     =>  'Windows 8.1',
                              '/windows nt 6.2/i'     =>  'Windows 8',
                              '/windows nt 6.1/i'     =>  'Windows 7',
                              '/windows nt 6.0/i'     =>  'Windows Vista',
                              '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                              '/windows nt 5.1/i'     =>  'Windows XP',
                              '/windows xp/i'         =>  'Windows XP',
                              '/windows nt 5.0/i'     =>  'Windows 2000',
                              '/windows me/i'         =>  'Windows ME',
                              '/win98/i'              =>  'Windows 98',
                              '/win95/i'              =>  'Windows 95',
                              '/win16/i'              =>  'Windows 3.11',
                              '/macintosh|mac os x/i' =>  'Mac OS X',
                              '/mac_powerpc/i'        =>  'Mac OS 9',
                              '/linux/i'              =>  'Linux',
                              '/ubuntu/i'             =>  'Ubuntu',
                              '/iphone/i'             =>  'iPhone',
                              '/ipod/i'               =>  'iPod',
                              '/ipad/i'               =>  'iPad',
                              '/android/i'            =>  'Android',
                              '/blackberry/i'         =>  'BlackBerry',
                              '/webos/i'              =>  'Mobile'
                        );
    
        foreach ($os_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $os_platform = $value;
    
        return $os_platform;
    }
    


}