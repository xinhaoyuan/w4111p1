<?php
require_once "bin/backend.php";
$b = Backend::instance();
$b->get(array("reset" => "yespleasedoit"));

/* USER */

/* $r = $b->dispatch("/user/")->post( */
/*     ["email" => "a@b.com", */
/*      "name"  => "test", */
/*      "password" => "test", */
/*      "address" => "dont know", */
/*      "phone" => "1284432"]); */

/* var_dump($r); echo "<br />"; */

/* $r = $b->dispatch("/user/")->post( */
/*     ["email" => "b@b.com", */
/*      "name"  => "test", */
/*      "password" => "test", */
/*      "address" => "haha", */
/*      "phone" => "111"]); */

/* var_dump($r); echo "<br />"; */

$r = $b->dispatch("/session/")->post(
    ["email" => "a@b.com",
     "password" => "t"]);

var_dump($r); echo "<br />";

$r = $b->dispatch("/session/")->post(
    ["email" => "a@b.com",
     "password" => "test"]);

$sk = $r["session_key"];

var_dump($r); echo "<br />";

$r = $b->dispatch("/session/")->post(
    ["email" => "b@b.com",
     "password" => "test"]);

$sk2 = $r["session_key"];

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

/* $r = $b->dispatch("/group/")->post( */
/*     ["gname" => "g", */
/*      "gdesc" => "desc"]); */

/* var_dump($r); echo "<br />"; */

$r = $b->dispatch("/group/g/")->get(
    []);

var_dump($r); echo "<br />";

/* $r = $b->dispatch("/group/g/")->post( */
/*     ["email" => "a@b.com", */
/*      "session_key" => $sk]); */

/* var_dump($r); echo "<br />"; */

/* $r = $b->dispatch("/group/g/")->get( */
/*     []); */

/* var_dump($r); echo "<br />"; */

$r = $b->dispatch("/group/")->get(
    array("email" => "a@b.com",
          "session_key" => $sk));

var_dump($r); echo "<br />";


/* ITEMS */

$r = $b->dispatch("/item/")->get(
    array("email" => "a@b.com",
          "session_key" => $sk));

var_dump($r); echo "<br />";

/* $r = $b->dispatch("/item/")->post( */
/*     array("email" => "a@b.com", */
/*           "session_key" => $sk, */
/*           "iname" => "test_item", */
/*           "idesc" => "this is a item for test", */
/*           "price" => "9.08")); */

/* var_dump($r); echo "<br />"; */

/* $r = $b->dispatch("/item/")->get( */
/*     array("email" => "a@b.com", */
/*           "session_key" => $sk)); */

/* var_dump($r); echo "<br />"; */

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

/* TRANSACTION */

$r = $b->dispatch("/transaction/")->get(
    array("email" => "b@b.com",
          "session_key" => $sk2));

var_dump($r); echo "<br />";

$r = $b->dispatch("/transaction/")->post(
    array("email" => "b@b.com",
          "session_key" => $sk2,
          "item_id" => $item_id));

$trans_id = $r["trans_id"];

var_dump($r); echo "<br />";

$r = $b->dispatch("/transaction/")->get(
    array("email" => "b@b.com",
          "session_key" => $sk2));

var_dump($r); echo "<br />";

$r = $b->dispatch("/transaction/")->get(
    array("email" => "a@b.com",
          "session_key" => $sk));

var_dump($r); echo "<br />";

$r = $b->dispatch("/transaction/$trans_id/")->get(
    array("email" => "a@b.com",
          "session_key" => $sk));

var_dump($r); echo "<br />";

$r = $b->dispatch("/transaction/$trans_id/")->post(
    array("email" => "a@b.com",
          "session_key" => $sk,
          "content" => "Hello!"));

var_dump($r); echo "<br />";

$r = $b->dispatch("/transaction/$trans_id/")->get(
    array("email" => "a@b.com",
          "session_key" => $sk));

var_dump($r); echo "<br />";

$r = $b->dispatch("/transaction/$trans_id/")->put(
    array("email" => "a@b.com",
          "session_key" => $sk,
          "price" => "7.7"));

var_dump($r); echo "<br />";

$r = $b->dispatch("/transaction/$trans_id/")->put(
    array("email" => "b@b.com",
          "session_key" => $sk2,
          "price" => "7.7"));

var_dump($r); echo "<br />";

$r = $b->dispatch("/transaction/$trans_id/")->get(
    array("email" => "a@b.com",
          "session_key" => $sk));

var_dump($r); echo "<br />";

?>
