CSRFProtector
==============

Protect against CSRF attack.              **PHP >= 5.4**

## Introduction
Cross-site request forgery, also known as a one-click attack or session riding and abbreviated as CSRF (sometimes pronounced sea-surf) or XSRF, is a type of malicious exploit of a website whereby unauthorized commands are transmitted from a user that the website trusts.   
Unlike cross-site scripting (XSS), which exploits the trust a user has for a particular site, CSRF exploits the trust that a site has in a user's browser.   
This class can be usefull to also avoid some sort of javascript scripts that attemps a human simulation or a DOS attack.  

---

## Why I should use this class?

* Automatic CSRF protection
* Strong CSRF protection
* Bot scripts protection
* Race conditions
* No cookie or database used
* Ajax 


#### Automatic CSRF protection
Most of others PHP scripts require that you manually edit link and form one by one.  
In medium and big size application, this is not only stressful but also dangerous because as human you can do mistakes.  
**CSRFProtector**, instead, do the job automatically!  

#### Strong CSRF protection
Most of csrf php libs protect only forms: anyway is a common practice to assign an action to an link.   
Infact suppose to have a link like `admin.php?action=dropDB`, this script can protect you while others can't.   
Also, often in others protection classes token are persistent. Instead here at each request the corrispective token is destroyed.

#### Bot scripts protection
CSRFProtector rewritting urls can avoid some type of javascript with the purpuse to simulate humans.

#### Race conditions
Sometimes there are too many click at second from the same browser,with CSRFProtector you can choose the time between script end execution and next request incoming.

#### No cookie or database used
CSRFProtector want to be full unintrusive.

#### Ajax
Coming soon

---

## Inside
Just before the end of the scripts, it search in the output buffered each links and forms. Then, they are modified adding a speacial randomic token:
tokens are then saved in sessions to create a white list.  
When a web request come to your server, CSFRProtector check if the associated token is in the permitted list: if yes then the script can continue, otherwise a error is shown.  
Not only: it also add a flag in session with the end time of script execution and you can choose when the next request is accepted.  


To do:

* Enable ajax
* Enable javascript redirect

---

## Installation
First off all, download and unzip all the contents in a folder in your server. Let's suppose is *libs*.   
At the begin of your main script, add this code

```php
  
require ("libs/CSRFProtector-master/CSRFProtector.php");
$jsPath = "CSRFProtector"; // path where is native.history.js
$csrf = new CSRFProtector($jsPath);
$csrf->run();
  
```

That is all! Anyway it's more powerfull than what might seem.  

#### Advanced configurations

The construct can take three optional arguments:

1. A string path where is located native.history.js (browser will search for {yourpath}/native.history.js)
2. A [callable](http://php.net/manual/en/language.types.callable.php) function that will be called when CSRF attack are discovered (standard action is to end the script and display "CSFR protection")
3. A [callable](http://php.net/manual/en/language.types.callable.php) function that generate the token(by default is a composition of 3 randomic value)
4. The maximum life time of tokens in seconds(default is 120 seconds)
5. The minimum time requested between the current script end time and the next request(default is 1 second) 

```php

$error = function(){
  die("Nice try dude");  
};

$token = function(){
    return "_".md5(mt_rand(2,100)).time().microtime(); // univoque for better security
};

$time = 30; //in seconds
$min = 0; // in seconds
$jsPath = "CSRFProtector"; // path where is native.history.js

$csrf = new CSRFProtector($jsPath,$error,$token,$time,$min);
$csrf->run();

```



It's also possible to manually protect GET and POST data using fews function:
```php

$auto = false;
$jsPath = "CSRFProtector";
$csrf = new CSRFProtector($jsPath);
$csrf->run($auto);

<html>
  <body>
    <a href="<?php echo $csrf->protectUrl("index.php"); ?>">a link</a>
    
    <form action="form.php" method="post">
      <?php echo $csrf->getFormHiddenComponent(); ?>
    </form> 
  </body>
</html>


