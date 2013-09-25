<?php

require ("../CSRFProtector.php");



$time = 12000; //in seconds
$min = 0; //in seconds

$jsPath = "";


$csrf = new CSRFProtector($jsPath, null, null, $time, $min,true,true);
$csrf->run();
if($csrf->isAjax())
{
    echo "Hello World!";
    die();
}
echo '
<html>
    <body>
        <a href="#" onclick="ajax()" >click me</a>
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