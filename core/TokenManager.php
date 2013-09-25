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
class TokenManager
{
    private $tokenFunction;
    private $maxTime;
    private $minSecondBeforeNextClick;
    private $globalToken;

    /**
     * TokenManager::__construct()
     * Build a new TokenManager
     * @param callable $tokenFunction
     * @param integer $maxTime
     * @param integer $minSecondBeforeNextClick
     * @param integer $globalToken
     * @return
     */
    public function __construct(callable $tokenFunction = null, $maxTime = 120, $minSecondBeforeNextClick = 1, $globalToken = false)
    {
        $this->tokenFunction = ($tokenFunction != null) ? $tokenFunction : function ()
        {
            return md5(mt_rand(1, 60));
        }
        ;
        $this->maxTime = $maxTime;
        $this->minSecondBeforeNextClick = $minSecondBeforeNextClick;
        $this->globalToken = $globalToken;
    }


    /**
     * TokenManager::applyNewToken()
     * This function return a new usable token 
     * 
     * @param string $url
     * @param string $type: can be GET or POST or other explicative usages
     * @return string
     */
    public function applyNewToken($url, $type)
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }
        //build the token
        $token = call_user_func($this->tokenFunction);
        //initializations
        if (!isset($_SESSION['csrf']))
        {
            $_SESSION['csrf'] = array();
        }
        if (!isset($_SESSION['csrf'][$url]))
        {
            $_SESSION['csrf'][$url] = array();
        }
        //fill
        $_SESSION['csrf'][$url][$type]['token'] = $token;
        $_SESSION['csrf'][$url][$type]['time'] = ($this->maxTime == 0) ? 0 : time() + $this->maxTime;
        $_SESSION['csrf']['nextClick'] = ($this->minSecondBeforeNextClick == 0) ? 0 : time() + $this->minSecondBeforeNextClick;
        return $token;
    }

    /**
     * TokenManager::useToken()
     * This function return true if the current token is valid
     * 
     * @param string $token
     * @param strong $url
     * @return boolean
     */
    public function useToken($token, $url, $type)
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }
        if($this->globalToken && CSRFFrontEnd::isAjax())
        {
            if (isset($_SESSION['csrf']) && isset($_SESSION['csrf'][$url]) && isset($_SESSION['csrf'][$url][$type]))
            {
                //trying to use global while exist other aviable tokens: attack!
                return false;
            }
            $url = "global";
            $type = "ajax";    
        }
        //structure conditions
        if (!isset($_SESSION['csrf']) || !isset($_SESSION['csrf'][$url]) || !isset($_SESSION['csrf'][$url][$type]))
        {
            return false;
        }
        //check if the token is still valid,if yes use and drop it.
        $return = $_SESSION['csrf'][$url][$type]['time'] >= time() || $_SESSION['csrf'][$url][$type]['time'] == 0;
        unset($_SESSION['csrf'][$url][$type]);
        return $return;
    }
    /**
     * TokenManager::isFirstVisit()
     * Return true if is the first user visit 
     * @return boolean
     */
    public function isFirstVisit()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }
        return empty($_SESSION);
    }
    /**
     * TokenManager::isAcceptedClick()
     * Return true if the request can be accepted
     * @return boolean
     */
    public function isAcceptedClick()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }
        return !isset($_SESSION['csrf']['nextClick']) || $_SESSION['csrf']['nextClick'] <= time() || $_SESSION['csrf']['nextClick'] == 0;
    }

    public function getAllTokens()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }
        if (!isset($_SESSION['csrf']))
            return array();
        return $_SESSION['csrf'];
    }

    public function getNextClikAccepted()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }
        if (!isset($_SESSION['csrf']))
            return time();
        return $_SESSION['csrf']['nextClick'];
    }
    
    public function globalMode()
    {
        return $this->globalToken;
    }

}

?>