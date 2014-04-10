<?php

require_once "bin/backend.php";
$b = Backend::instance();

$r = $b->dispatch("/session/")->post(
    array("email" => "a@b.com",
          "password" => "test"));

$sk = $r["session_key"];

var_dump($r); echo "<br />";

$r = $b->dispatch("/item/")->get(
    array("email" => "a@b.com",
          "session_key" => $sk));

var_dump($r); echo "<br />";

$r = $b->dispatch("/item/")->post(
    array("email" => "a@b.com",
          "session_key" => $sk,
          "iname" => "test_item",
          "idesc" => "this is a item for test",
          "price" => "9.08"));

var_dump($r); echo "<br />";

$r = $b->dispatch("/item/")->get(
    array("email" => "a@b.com",
          "session_key" => $sk));

var_dump($r); echo "<br />";

$item_id = $r["items"][0]["item_id"];

$r = $b->dispatch("/item/$item_id/")->get(
    array());

var_dump($r); echo "<br />";

$r = $b->dispatch("/item/$item_id/photo/")->post(
    array("image_data" => "ABC"));

var_dump($r); echo "<br />";

$r = $b->dispatch("/item/$item_id/")->get(
    array());

var_dump($r); echo "<br />";

?>
