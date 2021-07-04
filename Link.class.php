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


class Link
{

    /**
     * Object data
     * @since 1.3
     **/
    public $obj = [];

    /**
     * Links table
     * @since 1.3
     **/    
    protected $tbl = 'links';

    /**
     * Blacklisted columns
     * @since 1.3
     **/    
    protected $blackListed = ['id','deleted','views','downloads'];

    /**
     * Database
     * @since 1.3
     **/
    protected $db;


    /**
     * Link error
     * @since 1.3
     **/
    protected $error = '';

    protected $t = false;


    public function __construct($db)
    {
        $this->db = $db;
        $this->initProperties();
    }


    public function isBroken()
    {
        if($this->isEdit())
        {
            if($this->obj['status'] == 2)
            {
                return true;
            }
        }
        return false;
    }



    /**
     * Get currect link id
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function getID()
    {
        if($this->isEdit())
        {
            return $this->obj['id'];
        }
        return false;
    }


    /**
     * Check is edit or not
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function isEdit()
    {
        if(!empty($this->obj['id']))
        {
            return true;
        }
        return false;
    }


 



    /**
     * Initialize properties
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function initProperties()
    {
        $dbColumns = $this->db->rawQuery("DESCRIBE " . $this->tbl);
        if (!empty($dbColumns)) {
            foreach ($dbColumns as $col) {
                $this->obj[$col['Field']] = NULL;
            }
        }
    }

    public function isExit($s , $ty = 'slug')
    {
        if($ty == 'slug')
        {
            if($link = $this->findBySlug($s))
            {
                if($link['slug'] != $this->obj['slug']) 
                {
                    return true;
                }
            }
        }

        if($ty == 'id')
        {
            if($this->findById($s))
            {
                return true;
            }
        }

        return false;
    }


    /**
     * Find by slug
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function findBySlug($s)
    {
        $this->db->where('slug', $s);
        $link = $this->db->getOne($this->tbl);
        if($this->db->count > 0)
        {
            return $link;
        }
        return false;

    }


    /**
     * Find by ID
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function findById($id)
    {
        $this->db->where('id', $id);
        $link = $this->db->getOne($this->tbl);
        if($this->db->count > 0)
        {
            return $link;
        }
        return false;
    }


    /**
     * Check error
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function hasError()
    {
        if(!empty($this->error))
        {
            return true; 
        }
        return false;
    } 


    /**
     * Get error
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function getError()
    {
        return $this->error;
    }


    public function reload()
    {
        if($this->isEdit())
        {
            return $this->load($this->getID());
        }
    }


    public function load($id, $t = 'id')
    {
        if($t == 'id')
        {
            $link = $this->findById($id);
        }
        else
        {
            $link = $this->findBySlug($id);
        }
      
        if($link)
        {
            foreach($link as $k => $v)
            {
                if(array_key_exists($k, $this->obj))
                {
                    $this->obj[$k] = $v;
                }
            }
            return true;
        }
        return false;

    }


    public function getObj()
    {
        return $this->obj;
    }

   


}