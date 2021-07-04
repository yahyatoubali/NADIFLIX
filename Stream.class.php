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


 class Stream 
 {

        /**
         * Buffer size
         * @since 1.3
         **/
        protected $buffer = 256 * 1024;

        /**
         * Stream URL
         * @since 1.3
         **/
        protected $url;

        /**
         * Headers
         * @since 1.3
         **/
        protected $headers = [];

        /**
         * Status code
         * @since 1.3
         **/
        protected $statusCode;
        
        /**
         * Cache key
         * @since 1.3
         **/
        protected $key = NULL;

        /**
         * Streamable
         * @since 1.3
         **/
        protected $isHit = false;

        /**
         * Cookiz file
         * @since 2.2
         **/
        protected $cookizFile;

        /**
         * Meta data
         * @since 1.5
         **/
        protected $meta;
    
        /**
         * debug mode
         * @since 2.2
         **/
        protected $debug;

        /**
         * stream qulity
         * @since 2.2
         **/
        protected $q;

        /**
         * stream id
         * @since 2.2
         **/
        protected $id;

        /**
         * stream host
         * @since 2.2
         **/
        protected $host;

        /**
         * stream cache
         * @since 2.2
         **/
        protected $cache;

        /**
         * link object
         * @since 2.2
         **/
        protected $link;

        /**
         * link object
         * @since 2.2
         **/
        protected $error;

        /**
         * application db
         * @since 2.2
         **/
        protected $db;

        /**
         * application config
         * @since 2.2
         **/
        protected $config;

        


        protected $hs = false;
        protected $t = '_st2';
        protected $endpoint;
        protected $ios = false;


        public function __construct()
        {
            
            $this->debug = STREAM_DEBUG;
            $this->statusCode = 403;
            $this->endpoint = MAIN_DOMAIN . '/api/refresh';
            $this->setT();
        }

        public function run()
        {
            if(isset($_GET['a']) && !empty($_GET['a']))
            {
                $var = explode('/', $_GET['a']);
                if(!empty($var[0]) && !empty($var[1]))
                {
                    $this->q = Helper::clean(str_replace('.', '', $var[0]));
                    $id = Helper::clean($var[1]);
                    $this->id = preg_replace('/\\.[^.\\s]{3,4}$/', '', $id);
                    if(!empty($var[2]))
                    {
                        $host = Helper::clean(str_replace('.', '', $var[2]));
                    }
                    else
                    {
                        $host = GDRIVE_IDENTIFY;
                    }
                    $this->firewall();
                    $this->host = $host;
                    $this->init();
                }
            }
        }

        protected function init()
        {

            if(Helper::isValidHost($this->host))
            {
                if(is_numeric($this->q))
                {
                    
                    switch($this->host)
                    {
                        case GDRIVE_IDENTIFY:

                            $this->_driveST();

                        break;

                    }

                }
            }

        }

        protected function makeCookizFile($cookiz)
        {


            $ds = str_replace('DRIVE_STREAM=','',$cookiz[0]);
            $dsLine = "#HttpOnly_.docs.google.com	TRUE	/	TRUE	0	DRIVE_STREAM	{$ds}";

            $nid = str_replace('NID=','',$cookiz[1]);
            $nidLine = "#HttpOnly_.google.com	TRUE	/	TRUE	1626026661	NID {$nid}";

            $file = fopen($this->cookizFile,"w");
            fwrite($file,$nidLine.PHP_EOL);
            fwrite($file,$dsLine.PHP_EOL);
            fclose($file);

        }

        protected function refresh()
        {
            $resp = Helper::curl($this->endpoint.'/?id='.$this->id);

            if(!empty($resp) && Helper::isJson($resp))
            {
                 $resp = json_decode($resp, true);
 
                 if($resp['status'] == 'success')
                 {
                     return true;
                 }
            }
            return false;
        }


        protected function _driveST()
        {
            $isOk  = false;
            $i = 0;

            $this->initCache();
            $this->setKey();

            $sources = $this->cache->get();
            
            if(empty($sources))
            {
                $this->cache->cr();
                if($this->loadFromDB())
                {
                   
                    $sourcesData = $this->link->obj['data'];
                    
                    if(empty($sourcesData) && $this->refresh())
                    {
                        $this->link->reload();
                        $sourcesData = $this->link->obj['data'];
                    }

                    if(!empty($sourcesData))
                    {
                        
                        $sourcesData = json_decode($sourcesData, true);
                        $this->makeCookizFile($sourcesData['cookies']);
                        
                        if(isset($sourcesData['sources']))
                        {
                            $this->cache->save($sourcesData['sources']);
                            $sources = $sourcesData['sources'];
                            
                        }
                        else
                        {
                            //something went wrong
                            $this->error = 'something went wrong !';
                        }
                    }
            


                }
                else
                {
                    //link is does not exist
                    $this->error = 'link is does not exist';
                }
            }

            if(!empty($sources))
            {
                

                $file = array_key_exists($this->q, $sources) ? $sources[$this->q] : reset($sources);
                $this->url = $file['file'];
                $size = $file['size'];


                while($i < 3)
                {
                    $this->load();
                    
                    if(!$this->isOk())
                    {
                        
                        if(!$this->loadFromDB())
                        {
                            break;
                        }
                  
                        if($this->refresh())
                        {
                            $this->link->reload();
                            
                            if(!$this->link->isBroken())
                            {
                                if(!empty($this->link->obj['data']))
                                {
                                    $sourcesData = json_decode($this->link->obj['data'], true);
                                    $this->makeCookizFile($sourcesData['cookies']);
                                    if(isset($sourcesData['sources']))
                                    {
                                        $this->cache->save($sourcesData['sources']);
                                        $sources = $sourcesData['sources'];
                                    }
    
                                $file = array_key_exists($this->q, $sources) ? $sources[$this->q] : reset($sources);
                                    $this->url = $file['file'];
                                    $size = $file['size'];
                                    $isOk = true;
                                    break;
                                }
                                else
                                {
                                    $this->startFromDL();
                                }
                            }

                        }

                    }
                    else
                    {
                        $isOk = true;
                        break;
                    }
                    $i++;
                }

            }


            if($isOk)
            {
                if(!$this->isIOS()) 
                    $this->cros();

                if($this->isT1())
                {
                    if(empty($size))
                    {
                        $vInfo = Helper::getVInfo($this->url, $this->id);
                        if(!empty($vInfo['fsize']))
                        {
                            $sources[$this->q]['size'] = $size = $vInfo['fsize'];
                            $this->cache->save($sources);
                        }
                    }
                    
                    $this->setMeta(['fsize'=>$size]);
                }
                $this->start();
                exit;
            }
            else
            {
                //broken
                if($this->debug)
                {
                    if(!empty($this->error))
                    {
                        die($this->error);
                    }
                    else if(!empty($this->link->getError()))
                    {
                        die($this->link->getError());
                    }
                    else
                    {
                        die('Unknown Error !');
                    }
                }
                else
                {
                    $this->startDefault();
                }
            }
        }



        protected function startDefault()
        {
            if(!$this->isDBInit())
            {
                $this->initDB();
            }
            if(!empty($this->config['default_video']))
            {
                $this->start($this->config['default_video']);
            }
            else
            {
                $this->_404();
            }
        }


        protected function getDL()
        {
            if(!empty($this->link->obj['main_link']))
            {
                $gid = Helper::getDriveId($this->link->obj['main_link']);
                if(!empty($gid))
                {
                    return 'https://www.googleapis.com/drive/v2/files/'.$gid.'?key='.GDRIVE_API.'&alt=media';

                }
            }
            return '';
        }

        protected function startFromDL()
        {
            $this->start($this->getDL());
        }

        protected function loadFromDB()
        {

            if(!$this->isDBInit())
            {
                $this->initDB();
            }

            if(!$this->isLinkInit())
            {
                $this->initLink();
            }


            if($this->isExist())
            {
                return true;
            }

            return false;

        }

        protected function initLink()
        {
            $link = new Link($this->db, $this->config);

            if($link->load($this->id, 'slug'))
            {
                if($link->obj['type'] == 'GDrive')
                {
                    $this->link = $link;
                }
            }
        }

        protected function isExist()
        {
            if(isset($this->link) && !empty($this->link))
            {
                return true;
            }
            return false;
        }

        protected function isLinkInit()
        {
            if(isset($this->link) && !empty($this->link))
            {
                return true;
            }
            return false;
        }

        protected function initCache()
        {
            $this->cache = new Cache($this->id);
        }

        protected function initDB()
        {
            global $config;
            $db = new Database($config);
            $this->config = $db->get_config();
            $this->db = $db;
        }

        protected function isDBInit()
        {
            if(isset($this->db) && !empty($this->db))
            {
                return true;
            }
            return false;
        }



        /**
         * 404 page
         * @since 1.3
         **/
        protected function _404()
        {
            header('HTTP/1.1 404 Not Found');
            die('<h1>404 page not found !</h1>');
        }


        /**
         * Load gdrive file
         * @author CodySeller <https://codyseller.com>
         * @since 1.4
         */
        public function load($url = '')
        {
            if(!empty($url))
            {
                $this->url = $url;
            }
            if(!empty($this->url))
            {
                $this->checkDrive();
            }

                
        }


        /**
         * Get stream url status
         * @author CodySeller <https://codyseller.com>
         * @since 1.4
         */
        public function isOk()
        {
            if (!empty($this->statusCode) && $this->statusCode != 403) return true;
    
            return false;
        }


        /**
         * Check drive url status
         * @author CodySeller <https://codyseller.com>
         * @since 1.4
         */
        protected function checkDrive()
        {
            usleep(rand(900000, 1500000));
            $ch = curl_init($this->url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookizFile);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_exec($ch);
            $this->statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }


        /**
         * Send headers
         * @author CodySeller <https://codyseller.com>
         * @since 2.2
         */
        protected function sendHeader($header)
        {
            if ($this->debug) {
                var_dump($header);
            } else {
                header($header);
            }
        }


        /**
         * Set cache key
         * @author CodySeller <https://codyseller.com>
         * @since 1.3
         */
        public function setKey($k = '') 
        {
            // $this->key = Helper::e($k);
            if(empty($k)) $k = $this->id;
            $this->key = $k;
            $this->cookizFile = ROOT . '/data/cookiz/gdrive~'.$this->key.'.txt';
        }


        /**
         * Set meta data
         * @author CodySeller <https://codyseller.com>
         * @since 1.5
         */
        public function setMeta($d)
        {
            $this->meta = $d;
        }


        /**
         * Header call back
         * @author CodySeller <https://codyseller.com>
         * @since 2.2
         */
        public function headerCallback($ch, $data)
        {
            if (preg_match('/HTTP\/[\d.]+\s*(\d+)/', $data, $matches)) {
                $status_code = $matches[1];
    
                if ($status_code == 200 || $status_code == 206 || $status_code == 403 || $status_code == 404) {
                    $this->hs = true;
                    $this->sendHeader(rtrim($data));
                }
    
            } else {
    
                $forward = array('content-type', 'content-length', 'accept-ranges', 'content-range');
                $parts = explode(':', $data, 2);
                if ($this->hs && count($parts) == 2 && in_array(trim(strtolower($parts[0])), $forward)) {
                    $this->sendHeader(rtrim($data));
                }
            }
    
            return strlen($data);
        }
    

        /**
         * Body call back
         * @author CodySeller <https://codyseller.com>
         * @since 2.2
         */
        public function bodyCallback($ch, $data)
        {
            if (true) {
                echo $data;
                flush();
            }
    
            return strlen($data);
        }
    

        /**
         * Start stream
         * @author CodySeller <https://codyseller.com>
         * @since 2.2
         */
        public function start($url = '') 
        {

            if(empty($url))
            {
                $headers = [];

                header('Accept-Ranges: bytes');
                header('Developed-by: CodySeller');
    
                $headers[] = 'Connection: keep-alive';
                $headers[] = 'Cache-Control: no-cache';
                $headers[] = 'Pragma: no-cache';
    
                $range = !empty($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : '';
    
                if($this->isT1())
                {
    
                    $file = ['filesize' => $this->meta['fsize'], 'fileType' => 'video/mp4'];
    
                    if ($file)
                    {
                        $size = $file['filesize'];
                        header('Content-Type: ' . $file['fileType']);
                    }
                    else
                    {
                        http_response_code(404);
                        return;
                    }
                    
                    if (!empty($range))
                    {
                        list($start, $end) = explode('-', str_replace('bytes=', '', $range) , 2);
                        $length = intval($size) - intval($start);
        
                        header('HTTP/1.1 206 Partial Content');
                        header(sprintf('Content-Range: bytes %d-%d/%d', $start, ($size - 1) , $size));
                        header('Content-Length: ' . $length);
        
                        $headers[] = sprintf('Range: bytes=%d-', $start);
                    }
                    else
                    {
                        header('Content-Length: ' . $size);
                    }
    
                }
                else
                {
                    if(!empty($range)) $headers[] = 'Range: ' . $range;
                }
    
    
                // rand(900000, 1500000)
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->url);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookizFile);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_BUFFERSIZE, $this->buffer);
                curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, Helper::getUserAgent());
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_NOBODY, false);
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                if(CURL_MAX_SPEED != 0)  
                    curl_setopt($ch, CURLOPT_MAX_RECV_SPEED_LARGE, CURL_MAX_SPEED);

                
               
              
    
                if(!$this->isT1())
                {
                    curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'headerCallback']);
                }
    
                session_write_close();
            
                curl_exec($ch);
                curl_close($ch);
                return;
            }
            else
            {
                header('Location: ' . $url);
                exit;
            }
                


        }


        /**
         * ###???
         * @author CodySeller <https://codyseller.com>
         * @since 2.2
         */
        public function isT1()
        {
            return $this->t == '_st1';
        }


        /**
         * ###???
         * @author CodySeller <https://codyseller.com>
         * @since 2.2
         */
        public function cros()
        {
            if($this->isT1())
            {
                $this->t = '_st2';
            }
            else
            {
                $this->t = '_st1';
            }
        }

        protected function firewall()
        {
            if(FIREWALL && Helper::isUrl(MAIN_DOMAIN))
            {
                if (!isset($_SERVER["HTTP_REFERER"]))
                {
                    $this->_404();
                    exit;
                }
                $d = parse_url(MAIN_DOMAIN,PHP_URL_HOST);
                $referer = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST);
                if (empty($referer) || $referer != $d)
                {
                    $this->_404();
                    exit;
                }
            }

        }


        protected function setT()
        {
            $os = Helper::getOS();

            if (!(strpos($os, 'Windows') !== false || strpos($os, 'Android') !== false))
            {
               $this->ios = true;
            }
        }

        protected function isIOS()
        {
            return $this->ios;
        }


 }
