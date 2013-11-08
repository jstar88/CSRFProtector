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
class CSRFBackEnd
{
    private $dom;
    private $tokenManager;
    private $jsPath;
    private $frontEnd;

    public function __construct(TokenManager $tokenManager, $jsPath, CSRFFrontEnd $frontEnd)
    {
        $this->dom = new DOMDocument();
        $this->tokenManager = $tokenManager;
        $this->jsPath = $jsPath;
        $this->frontEnd = $frontEnd;
    }


    public function loadObContents()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->group(__FUNCTION__);
        @$this->dom->loadHTML(ob_get_clean());
        ob_start();
        $firephp->groupEnd();
    }

    public function saveObContents()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->group(__FUNCTION__);
        $firephp->log('allTokens', $this->tokenManager->getAllTokens());
        $firephp->log('nextAcceptedClick', $this->tokenManager->getNextClikAccepted());
        echo $this->dom->saveHTML();
        $firephp->groupEnd();
    }

    /**
     * CSRFBackEnd::protectLinks()
     * Protect the <a tag elements in the DOM
     * @return void
     */
    public function protectLinks()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->group(__FUNCTION__);
        foreach ($this->dom->getElementsByTagName("a") as $domNode)
        {
            $href = $this->protectUrl($domNode->getAttribute('href'), 'GET');
            if ($href == false)
            {
                $firephp->log('external GET found:' . $domNode->getAttribute('href'));
                continue;
            }
            $domNode->setAttribute('href', $href);
            $firephp->log('internal GET found:' . $href);
        }
        $firephp->groupEnd();
    }

    /**
     * CSRFBackEnd::protectForms()
     * protect all forms adding a token
     * @return void
     */
    public function protectForms()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->group(__FUNCTION__);
        foreach ($this->dom->getElementsByTagName("form") as $domNode)
        {
            $action = $domNode->getAttribute('action');
            //if it's a GET form
            if (strtolower($domNode->getAttribute("method")) == "get")
            {
                $href = $this->protectUrl($action, 'GET');
                if ($href == false)
                {
                    $firephp->log('external GET found:' . $action);
                    continue;
                }
                $domNode->setAttribute('action', $href);
                $firephp->log('internal GET found:' . $href);
            }
            else // post form
            {
                if (!isHrefToThisServer($action))
                {
                    $firephp->log('external POST found:' . $action);
                    continue;
                }
                $token = $this->tokenManager->applyNewToken($action, 'POST');
                $element = $this->dom->createElement('input', '');
                $element->setAttribute('type', 'hidden');
                $element->setAttribute('name', 'csrftoken');
                $element->setAttribute('value', $token);
                $domNode->appendChild($element);
                $firephp->log('internal POST found:' . $action . '  token =' . $token);
            }
        }
        $firephp->groupEnd();
    }


    /**
     * CSRFBackEnd::protectUrl()
     * Return the protected url
     * @param string $href
     * @param string $type
     * @return
     */
    public function protectUrl($href, $type)
    {
        if (!isHrefToThisServer($href))
        {
            return false;
        }
        $token = $this->tokenManager->applyNewToken($href, $type);
        $href = (strpos($href, '?') !== false) ? "$href&" : "$href?";
        $href .= "csrftoken=$token";
        return $href;
    }

    public function addHistoryScript()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->group(__FUNCTION__);
        if (CSRFFrontEnd::isAjax())
        {
            $firephp->groupEnd();
            return;
        }
        $token = $this->tokenManager->applyNewToken(getRequestUrlWithoutKey(array('csrftoken')), 'GET');

        //$history = $this->dom->createElement("script");
        //$history->setAttribute('src', $this->jsPath . '/native.history.js');

        $titleElement = $this->dom->getElementsByTagName('title');
        $title = ($titleElement->length > 0) ? $titleElement->item(0)->nodeValue : null;

        /**
        * $scriptText = "window.onload=function(){
        *             (function(window,undefined){               
        *                 History.pushState({state:1}, '$title', '?csrftoken=$token');
        *             })(window);
        *         };";
        */
        $scriptText = "
        history.pushState({state:1}, '$title', '?csrftoken=$token');
        window.setTimeout(function(){
            window.onpopstate = function(event){
                document.location.reload(true);
            };
        }, 1);";
        $script = $this->dom->createElement("script");
        $script->appendChild($this->dom->createTextNode($scriptText));

        $body = $this->dom->getElementsByTagName("body")->item(0);
        //$body->appendChild($history);
        $body->appendChild($script);
        $firephp->log('token for next refresh= ' . $token);
        $firephp->groupEnd();
    }

    /**
     * CSRFBackEnd::protectAjax()
     * Protect ajax requests dinamically updating javascript variables
     * @return void
     */
    public function protectAjax()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->group(__FUNCTION__);
        $server = $_SERVER["HTTP_HOST"];
        $firephp->log('server is = ' . $server);
        $body = $this->dom->getElementsByTagName("body")->item(0);
        //initialization
        if (!CSRFFrontEnd::isAjax())
        {
            //lib script
            $csrfScript = $this->dom->createElement("script");
            $csrfScript->setAttribute('src', $this->jsPath . '/csrf.protector.js');
            $body->appendChild($csrfScript);
            //initialization script
            $scriptText = "csrftoken = new Array(); csrftoken['global'] = '';";
            $script = $this->dom->createElement("script");
            $script->appendChild($this->dom->createTextNode($scriptText));
            $body->appendChild($script);
        }
        //global token is regenerated anyway to each ajax call to increase security. 
        if ($this->tokenManager->globalMode())
        {
            $globalToken = $this->tokenManager->applyNewToken("global", "ajax");
            $scriptUpdaterText = "csrftoken['global'] = '$globalToken'; server = '$server';";
            $firephp->log('created global token');
        }
        //standard mode: refresh the token for the next call of this address.
        else
        {
            $url = getRequestUrlWithoutKey(array('csrftoken', 'csrftokenAjax'));
            $token = $this->tokenManager->applyNewToken($url, $_SERVER['REQUEST_METHOD']);
            $scriptUpdaterText = "csrftoken['$url'] = '$token'; server = '$server';";
            $firephp->log('created token for future ajax call = ' . $token);    
        }
        //updater script
        $scriptUpdater = $this->dom->createElement("script");
        $scriptUpdater->appendChild($this->dom->createTextNode($scriptUpdaterText));
        $scriptUpdater->setAttribute('id', 'csrftokenUpdater');
        $body->appendChild($scriptUpdater);
        $firephp->groupEnd();
    }

    public function protectHeaderRedirect()
    {
        //clean redirect called by header() function
        foreach (apache_response_headers() as $key => $value)
        {

            if (strtolower($key) == "location")
            {
                $href = $this->protectUrl($value, 'GET');

                if ($href !== false)
                {
                    header("$key: $href");
                    return;
                }
            }
        }
    }
    public function protectMetaRedirect()
    {
        $firephp = FirePHP::getInstance(true);
        $firephp->group(__FUNCTION__);
        //clean redirect called by meta tag
        $metaElements = $this->dom->getElementsByTagName('meta');
        foreach ($metaElements as $metaElement)
        {
            $httpeq = $metaElement->getAttribute("http-equiv");
            $content = $metaElement->getAttribute("content");
            if (!empty($httpeq) && strtolower($httpeq) == "refresh" && !empty($content))
            {
                $a = split(";", $content);
                $seconds = $a[0];
                if (count($a) > 1)
                {
                    $url = $a[1];
                    if (!empty($url))
                    {
                        $url = substr($url, strpos($url, "=") + 1);
                        $url = $this->protectUrl($url, 'GET');
                        $metaElement->setAttribute('content', "$seconds;URL=$url");
                        $firephp->log('Redirect found:' . $url);
                    }
                }
                break;
            }
        }
        $firephp->groupEnd();
    }
}

?>