<?php
require ("core/TokenManager.php");
require ("core/CSFRBackEnd.php");
require ("core/CSFRFrontEnd.php");
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
class CSFRProtector
{
    private $tokenManager;
    private $frontEnd;
    private $backEnd;

    public function __construct(callable $errorFunction = null, callable $tokenFunction = null, $maxTime = 120)
    {
        $this->tokenManager = new TokenManager($tokenFunction, $maxTime);
        $this->frontEnd = new CSFRFrontEnd($this->tokenManager,$errorFunction);
        $this->backEnd = new CSFRBackEnd($this->tokenManager);
    }

    public function run()
    {
        ob_start();
        $this->csrfFrontEnd();
        //ob_start(array(&$this, 'csrfBackEnd'));
        register_shutdown_function(array(&$this, 'csrfBackEnd'));
    }

    private function csrfFrontEnd()
    {
        $this->frontEnd->checkGets();
        $this->frontEnd->checkPosts();
    }

    public function csrfBackEnd()
    {
        $this->backEnd->loadObContents();
        $this->backEnd->protectForms();
        $this->backEnd->protectLinks();
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
    
}

?>