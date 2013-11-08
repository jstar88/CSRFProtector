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
    private $tokenManager;

    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }
    public function checkGets()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->group('checkGets');
        //if it's a POST request stop
        if (isPost())
        {
            $firephp->log('it\'s a post!');
            return true;
        }
        //if the request have a valid token then use it and stop
        if (isset($_GET['csrftoken']) && $this->tokenManager->useToken($_GET['csrftoken'],getRequestUrlWithoutKey(array('csrftoken','csrftokenAjax')),'GET'))
        {
            $firephp->log('valid token');
            return true;
        }
        //if is the main page then stop
        if (empty($_GET) && $this->tokenManager->isFirstVisit())
        {
            $firephp->log('first visit');
            return true;
        }
        //otherwise call function error
        $firephp->log('something wrong!');
        $firephp->groupEnd();
        return false;
    }
    public function checkPosts()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->group('checkPosts');
        //if it's a GET request stop
        if (isGet())
        {
            $firephp->log('it\'s a get!');
            return true;
        }
        //if the request have a valid token then use it and stop
        if (isset($_POST['csrftoken']) && $this->tokenManager->useToken($_POST['csrftoken'],getRequestUrlWithoutKey(array('csrftoken','csrftokenAjax')),'POST'))
        {
            $firephp->log('valid token');
            return true;
        }
        //if is the main page then stop
        if (empty($_POST) && $this->tokenManager->isFirstVisit())
        {
            $firephp->log('first visit');
            return true;
        }
        //otherwise call function error
        $firephp->log('something wrong!');
        $firephp->groupEnd();
        return false;
    }
    
    /**
     * CSRFFrontEnd::checkUser()
     * Check if the time elapsed from last request is enought
     * @return boolean
     */
    public function checkUser()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->group('checkUser');
        if ($this->tokenManager->isAcceptedClick())
        {
            $firephp->log('user click accpeted');
            return true;
        }
        $firephp->log('user click not accpeted');
        $firephp->groupEnd();
        return false;
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