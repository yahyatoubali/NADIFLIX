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


class Cache
{

    /**
     * Cache path
     * @since 2.2
     **/
    protected $path;

    /**
     * Cache file
     * @since 2.2
     **/
    protected $file;

    /**
     * Cache data
     * @since 2.2
     **/
    protected $data;

    /**
     * Cache key
     * @since 2.2
     **/
    protected $key;

    /**
     * Cache error
     * @since 2.2
     **/
    protected $error;


    public function __construct($k = '')
    {
        if(!empty($k))
        {
            $this->key = $k;
        }
        $this->path = ROOT . '/data/cache/';
    }


    /**
     * Save cache data
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    public function save($data)
    {
      
        if(!$this->hasError())
        {
            if(is_array($data))
            {
                $data = json_encode($data);
            }

            @file_put_contents($this->getFile(), serialize($data));
            return true;
        }
        
        return false;
    }


    /**
     * Get cache data
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    public function get($id = '')
    {
        if(!empty($id))
        {
            $this->key = $id;
        }

        if($this->isExist())
        {
            $data = @file_get_contents($this->getFile());
            if(!empty($data))
            {
                $data =  unserialize($data);
                
                if(Helper::isJson($data))
                {
                    $data = json_decode($data, true);
                }

                return $data;
            }
        }
        
        return '';
    }

    public function cr()
    {
        $this->error = '';
    }


    /**
     * delete cache
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    public function delete($id = '')
    {
        if(!empty($id))
        {
            $this->key = $id;
        }
        if($this->isExist())
        {
            unlink($this->getFile());
        }
        return true;
    }


    /**
     * Get cache file path
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    protected function getFile()
    {
        $this->k();
        return $this->path . $this->file;
    }


    /**
     * Set cache key
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    public function setKey($k)
    {
        $this->key = $k;
    }


    /**
     * Check cache error
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    protected function hasError()
    {
        if(!empty($this->error))
        {
            return true;
        }
        return false;
    }


    /**
     * Check cache file exist or not
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    protected function isExist()
    {
        return file_exists($this->getFile()) && !is_dir($this->getFile());
    }


    /**
     * Get cache error
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    public function getError()
    {
        return $this->error;
    }


    /**
     * Set file name
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    protected function k()
    {
        if(!empty($this->key) && empty($this->file))
        {
            $this->file = 'gdrive~'.Helper::e($this->key).'.txt';
        }
    }


    /**
     * Clear all cache files
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    public function clearAll()
    {
        $files = glob($this->path); 
        foreach($files as $file){
            if(is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }


    /**
     * Check cache dir
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    protected function check()
    {
        if(!file_exists($this->path))
        {
            if($this->makeDir())
            {
                $this->error = 'Cache folder does not exist !';
            }
        }
    }


    /**
     * Make directory
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    function makeDir()
    {
        $ret = @mkdir($this->path);
        return $ret === true || is_dir($this->path);
    }


    public function __destruct()
    {
        $this->key = NULL;
        $this->data = NULL;
    }



}
