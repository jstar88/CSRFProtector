<?php

/**
 * CSFRProtector
 * A class usefull to avoid CSFR attacks
 * @package   
 * @author Jstar
 * @copyright Jstar
 * @version 2013
 * @access public
 * @license GNU v3
 */
class CSFRFrontEnd
{
    private $errorFunction;
    private $tokenManager;

    public function __construct(TokenManager $tokenManager, callable $errorFunction = null)
    {
        $this->tokenManager = $tokenManager;
        $this->errorFunction = ($errorFunction != null) ? $errorFunction : function ()
        {
            die("CSFR protection");
        }
        ;
    }
    public function checkGets()
    {
        if (isset($_GET['csrftoken']) && $this->tokenManager->useToken($_GET['csrftoken']))
        {
            return;
        }
        
        if(empty($_GET) && $this->tokenManager->isFirstVisit())
        {
            return;
        }
        call_user_func($this->errorFunction);
    }
    public function checkPosts()
    {
        if (isset($_POST['csrftoken']) && $this->tokenManager->useToken($_POST['csrftoken']))
        {
            return;
        }
        if(empty($_POST) && $this->tokenManager->isFirstVisit())
        {
            return;
        }
        call_user_func($this->errorFunction);
    }
}

?>