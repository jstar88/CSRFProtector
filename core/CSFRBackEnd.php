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
class CSFRBackEnd
{
    private $dom;
    private $tokenManager;

    public function __construct(TokenManager $tokenManager)
    {
        $this->dom = new DOMDocument();
        $this->tokenManager = $tokenManager;
    }


    public function loadObContents()
    {
        $this->dom->loadHTML(ob_get_clean());
    }

    public function saveObContents()
    {
        ob_start();
        echo $this->dom->saveHTML();
    }

    public function protectLinks()
    {
        foreach ($this->dom->getElementsByTagName("a") as $domNode)
        {
            $href = $this->protectUrl($domNode->getAttribute('href'));
            if ($href == false)
            {
                continue;
            }
            $domNode->setAttribute('href', $href);
        }
    }

    public function protectForms()
    {
        foreach ($this->dom->getElementsByTagName("form") as $domNode)
        {
            $action = $domNode->getAttribute('action');
            if (strtolower($domNode->getAttribute("method")) == "get")
            {
                $href = $this->protectUrl($action);
                if ($href == false)
                {
                    continue;
                }
                $domNode->setAttribute('action', $href);
            } else
            {
                $element = $this->dom->createElement('input', '');
                $element->setAttribute('type', 'hidden');
                $element->setAttribute('name', 'csrftoken');
                $element->setAttribute('value', $this->tokenManager->applyNewToken());
                $domNode->appendChild($element);
            }
        }
    }

    public function protectUrl($href)
    {
        if (parse_url($href, PHP_URL_HOST) != $_SERVER["HTTP_HOST"] && parse_url($href, PHP_URL_HOST) !== null)
        {
            return false;
        }
        $token = $this->tokenManager->applyNewToken();
        $href = (strpos($href, '?') !== false) ? "$href&" : "$href?";
        $href .= "csrftoken=$token";
        return $href;
    }
}

?>