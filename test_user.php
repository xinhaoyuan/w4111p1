<?php

require_once "bin/backend.php";

$b = Backend::instance();
$r = $b->dispatch("/user/")->post(
    ["email" => "a@b.com",
     "name"  => "test",
     "password" => "test",
     "address" => "dont know",
     "phone" => "1284432"]);

var_dump($r); echo "<br />";

$r = $b->dispatch("/session/")->post(
    ["email" => "a@b.com",
     "password" => "t"]);

var_dump($r); echo "<br />";

$r = $b->dispatch("/session/")->post(
    ["email" => "a@b.com",
     "password" => "test"]);

$sk = $r["session_key"];

var_dump($r); echo "<br />";

$r = $b->dispatch("/user/a@b.com/")->get(
    ["email" => "a@b.com",
     "session_key" => "HAHAHA"]);

var_dump($r); echo "<br />";

$r = $b->dispatch("/user/a@b.com/")->get(
    []);

var_dump($r); echo "<br />";

$r = $b->dispatch("/user/a@b.com/")->get(
    ["email" => "a@b.com",
     "session_key" => $sk]);

var_dump($r); echo "<br />";

$r = $b->dispatch("/user/a@b.com/")->put(
    ["email" => "a@b.com",
     "session_key" => $sk,
     "address" => "i wont tell u even i know"]);

var_dump($r); echo "<br />";


$r = $b->dispatch("/user/a@b.com/")->get(
    ["email" => "a@b.com",
     "session_key" => $sk]);

var_dump($r); echo "<br />";

$r = $b->dispatch("/user/a@b.com/")->put(
    ["email" => "a@b.com",
     "session_key" => $sk,
     "address" => "dont know"]);

var_dump($r); echo "<br />";


?>
