<?php

require ("../CSRFProtector.php");

$csrf = new CSRFProtector(array(
    'maxTime' => 12000,
    'minSecondBeforeNextClick' => 0,
    'debug' => true,
    'globalToken' => true));
$csrf->run();
if ($csrf->isAjax())
{
    echo "Hello World!";
    die();
}
echo '
<html>
    <body>
        <input type="button" onclick="ajax()" value="click me" />
        <div id ="content"></div>
        <script>
            function ajax()
            {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "/examples/SampleAjax.php", true);
                xhr.onload = function() {
                    document.getElementById("content").innerHTML = xhr.responseText;
                };
                xhr.send();
            }
        
        </script>
    </body>
</html>';

?>