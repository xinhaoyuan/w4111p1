<?php
require_once "backend.php";

$method = $_SERVER["REQUEST_METHOD"];
$path   = $_REQUEST["REST_PATH"];

$o = Backend::instance()->dispatch($path);
$r = NULL;

if ($o instanceof IREST) {
    switch ($method) {
    case "GET":
        $r = $o->get($_REQUEST);
        break;
    case "POST":
        $r = $o->post($_REQUEST);
        break;
    case "PUT":
        $r = $o->put($_REQUEST);
        break;
    }
} else $r = NULL;

if (!($r === NULL)) {
    if (is_string($r)) {
        /* assume the header is given in the rest call */
        echo $r;
    } else {
        header("Content-type: text/x-json");
        echo(json_encode($r));
    }
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    echo "Something went wrong when requesting REST object " . $path;
}
?>
