<?php


function refine_post($data)
{
        $data = $_POST[$data];
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
}
function js_alert($msg){
        echo "<script type='text/javascript'>\n";
        echo "alert('". $msg . "');\n";
        echo "</script>";
}

?>
