<?php

require ('libs/Utils.php');
require ('libs/FirePHPCore/FirePHP.class.php');
require ("core/TokenManager.php");
require ("core/CSRFBackEnd.php");
require ("core/CSRFFrontEnd.php");
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
class CSRFProtector
{
    private $tokenManager;
    private $frontEnd;
    private $backEnd;
    private $errorFunction;

    public function __construct($args = array())
    {
        $errorFunction = function ()
        {
            print_r($_SESSION);
            die("CSRF protection");
        }
        ;
        $tokenFunction = function ()
        {
            return md5(mt_rand(1, 60));
        }
        ;
        $jsPath = "";
        $maxTime = 120;
        $minSecondBeforeNextClick = 1;
        $debug = false;
        $globalToken = false;
        extract($args);
        
        $this->errorFunction = $errorFunction;
        
        $this->tokenManager = new TokenManager($tokenFunction, $maxTime, $minSecondBeforeNextClick, $globalToken);
        $this->frontEnd = new CSRFFrontEnd($this->tokenManager);
        $this->backEnd = new CSRFBackEnd($this->tokenManager, $jsPath, $this->frontEnd);
        FirePHP::getInstance(true)->setEnabled = $debug;
    }

    public function run($autoProtect = true)
    {
        ob_start();
        $firephp = FirePHP::getInstance(true);
        $firephp->log('Application Initialized');
        $this->csrfFrontEnd();
        //ob_start(array(&$this, 'csrfBackEnd'));
        if ($autoProtect)
        {
            header_register_callback(array(&$this->backEnd, 'protectHeaderRedirect'));
            register_shutdown_function(array(&$this, 'csrfBackEnd'));
        }
    }

    /**
     * CSRFProtector::csrfFrontEnd()
     * Run a set of checks before any user code
     * @return void
     */
    private function csrfFrontEnd()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->log('Running FrontEnd');

        if (!$this->frontEnd->checkGets() || !$this->frontEnd->checkPosts() || !$this->frontEnd->checkUser())
        {
            call_user_func($this->errorFunction);
        }

    }

    /**
     * CSRFProtector::csrfBackEnd()
     * Run a set of task after all user code
     * @return void
     */
    public function csrfBackEnd()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->log('Running BackEnd');

        $this->backEnd->loadObContents();
        $this->backEnd->addHistoryScript();
        $this->backEnd->protectMetaRedirect();
        $this->backEnd->protectForms();
        $this->backEnd->protectLinks();
        $this->backEnd->protectAjax();
        $this->backEnd->saveObContents();
    }

    public function applyNewToken()
    {
        return $this->tokenManager->applyNewToken();
    }

    public function useToken($token)
    {
        return $this->tokenManager->useToken($token);
    }

    public function protectUrl($url)
    {
        return $this->backEnd->protectUrl($url);
    }

    public function getFormHiddenComponent()
    {
        $token = $this->applyNewToken();
        return "<input type=\"hidden\" name=\"csrftoken\" value=\"$token\"></input>";
    }

    public function isAjax()
    {
        return CSRFFrontEnd::isAjax();
    }

}

?>