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
class TokenManager
{
    private $tokenFunction;
    private $maxTime;

    public function __construct(callable $tokenFunction = null, $maxTime = 120)
    {
        $this->tokenFunction = ($tokenFunction != null) ? $tokenFunction : function ()
        {
            return "_".mt_rand(1, 20) . mt_rand(1, 20) . mt_rand(1, 20);
        }
        ;
        $this->maxTime = (int)$maxTime;
    }

    public function applyNewToken()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
        $token = call_user_func($this->tokenFunction);
        while (isset($_SESSION[$token]))
        {
            $token = call_user_func($this->tokenFunction);
        }
        $_SESSION[$token] = time() + $this->maxTime;
        return $token;
    }

    public function useToken($token)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
        if (isset($_SESSION[$token]) && $_SESSION[$token] >= time())
        {
            unset($_SESSION[$token]);
            return true;
        }
        elseif (isset($_SESSION[$token]))
        {
            unset($_SESSION[$token]);
        }
        return false;
    }
    public function isFirstVisit()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
        return empty($_SESSION); 
    }

}

?>