<?php

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

    public function __construct($jsPath ="" ,callable $errorFunction = null, callable $tokenFunction = null, $maxTime = 120, $minSecondBeforeNextClick = 1)
    {
        $this->tokenManager = new TokenManager($tokenFunction, $maxTime, $minSecondBeforeNextClick);
        $this->frontEnd = new CSRFFrontEnd($this->tokenManager, $errorFunction);
        $this->backEnd = new CSRFBackEnd($this->tokenManager,$jsPath);
    }

    public function run($autoProtect = true)
    {
        ob_start();
        $this->csrfFrontEnd();
        //ob_start(array(&$this, 'csrfBackEnd'));
        if($autoProtect)
        {   
            register_shutdown_function(array(&$this, 'csrfBackEnd'));
        }
    }

    private function csrfFrontEnd()
    {
        $this->frontEnd->checkGets();
        $this->frontEnd->checkPosts();
        $this->frontEnd->checkUser();
    }

    public function csrfBackEnd()
    {
        $this->backEnd->loadObContents();
        $this->backEnd->addHistoryScript();
        $this->backEnd->protectForms();
        $this->backEnd->protectLinks();
        $this->backEnd->protectRedirect();
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

}

?>