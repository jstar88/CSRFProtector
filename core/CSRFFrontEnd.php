<?php

/**
 * CSRFProtector
 * A class usefull to avoid CSRF attacks
 * @package   
 * @author Jstar
 * @copyright Jstar
 * @version 2013
 * @access public
 * @license GNU v3
 */
class CSRFFrontEnd
{
    private $errorFunction;
    private $tokenManager;

    public function __construct(TokenManager $tokenManager, callable $errorFunction = null)
    {
        $this->tokenManager = $tokenManager;
        $this->errorFunction = ($errorFunction != null) ? $errorFunction : function ()
        {
            die("CSRF protection");
        }
        ;
    }
    public function checkGets()
    {
        if (!empty($_POST))
        {
            return;
        }
        if (isset($_GET['csrftoken']) && $this->tokenManager->useToken($_GET['csrftoken']))
        {
            return;
        }

        if (empty($_GET) && $this->tokenManager->isFirstVisit())
        {
            return;
        }
        call_user_func($this->errorFunction);
    }
    public function checkPosts()
    {
        if (!empty($_GET))
        {
            return;
        }
        if (isset($_POST['csrftoken']) && $this->tokenManager->useToken($_POST['csrftoken']))
        {
            return;
        }
        if (empty($_POST) && $this->tokenManager->isFirstVisit())
        {
            return;
        }
        call_user_func($this->errorFunction);
    }
    public function checkUser()
    {
        if ($this->tokenManager->isAcceptedClick())
        {
            return;
        }
        call_user_func($this->errorFunction);
    }
    public function isAjax()
    {
        return isset($_GET['csrftokenAjax']);
    }
}

?>