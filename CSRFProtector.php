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

    public function __construct($jsPath = "", callable $errorFunction = null, callable $tokenFunction = null, $maxTime = 120, $minSecondBeforeNextClick = 1, $debug = false, $globalToken = false)
    {
        $this->tokenManager = new TokenManager($tokenFunction, $maxTime, $minSecondBeforeNextClick, $globalToken);
        $this->frontEnd = new CSRFFrontEnd($this->tokenManager, $errorFunction);
        $this->backEnd = new CSRFBackEnd($this->tokenManager, $jsPath, $this->frontEnd);
        $firephp = FirePHP::getInstance(true);
        $firephp->setEnabled = $debug;
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
        $firephp->group('checkGets');
        $this->frontEnd->checkGets();
        $firephp->groupEnd();
        $firephp->group('checkPosts');
        $this->frontEnd->checkPosts();
        $firephp->groupEnd();
        $firephp->group('checkUser');
        $this->frontEnd->checkUser();
        $firephp->groupEnd();
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
        $firephp->group('loadObContents');
        $this->backEnd->loadObContents();
        $firephp->groupEnd();
        $firephp->group('addHistoryScript');
        $this->backEnd->addHistoryScript();
        $firephp->groupEnd();
        $firephp->group('protectMetaRedirect');
        $this->backEnd->protectMetaRedirect();
        $firephp->groupEnd();
        $firephp->group('protectForms');
        $this->backEnd->protectForms();
        $firephp->groupEnd();
        $firephp->group('protectLinks');
        $this->backEnd->protectLinks();
        $firephp->groupEnd();
        $firephp->group('protectAjax');
        $this->backEnd->protectAjax();
        $firephp->groupEnd();
        $firephp->group('saveObContents');
        $this->backEnd->saveObContents();
        $firephp->groupEnd();
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