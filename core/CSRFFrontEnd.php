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
            print_r($_SESSION);
            die("CSRF protection");
        }
        ;
    }
    public function checkGets()
    {
        $firephp = FirePHP::getInstance(true);
        //if it's a POST request stop
        if (isPost())
        {
            $firephp->log('it\'s a post!');
            return;
        }
        //if the request have a valid token then use it and stop
        if (isset($_GET['csrftoken']) && $this->tokenManager->useToken($_GET['csrftoken'],getRequestUrlWithoutKey(array('csrftoken','csrftokenAjax')),'GET'))
        {
            $firephp->log('valid token');
            return;
        }
        //if is the main page then stop
        if (empty($_GET) && $this->tokenManager->isFirstVisit())
        {
            $firephp->log('first visit');
            return;
        }
        //otherwise call function error
        $firephp->log('something wrong!');
        $firephp->groupEnd();
        call_user_func($this->errorFunction);
    }
    public function checkPosts()
    {
        $firephp = FirePHP::getInstance(true);
        //if it's a GET request stop
        if (isGet())
        {
            $firephp->log('it\'s a get!');
            return;
        }
        //if the request have a valid token then use it and stop
        if (isset($_POST['csrftoken']) && $this->tokenManager->useToken($_POST['csrftoken'],getRequestUrlWithoutKey(array('csrftoken','csrftokenAjax')),'POST'))
        {
            $firephp->log('valid token');
            return;
        }
        //if is the main page then stop
        if (empty($_POST) && $this->tokenManager->isFirstVisit())
        {
            $firephp->log('first visit');
            return;
        }
        //otherwise call function error
        $firephp->log('something wrong!');
        $firephp->groupEnd();
        call_user_func($this->errorFunction);
    }
    
    /**
     * CSRFFrontEnd::checkUser()
     * Check if the time elapsed from last request is enought
     * @return boolean
     */
    public function checkUser()
    {
        $firephp = FirePHP::getInstance(true);
        if ($this->tokenManager->isAcceptedClick())
        {
            $firephp->log('user click accpeted');
            return;
        }
        $firephp->log('user click not accpeted');
        $firephp->groupEnd();
        call_user_func($this->errorFunction);
    }
    
    /**
     * CSRFFrontEnd::isAjax()
     * Check if the current request is Ajax
     * @return boolean
     */
    public static function isAjax()
    {
        //ajax request accepted must contain this argument
        return isset($_GET['csrftokenAjax']);
    }
}

?>