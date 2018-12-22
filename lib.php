<?php

require_once('mysql_compat.php');

function dbconn() {
  include("config.php");
  if (!mysql_connect($mysql_host, $mysql_login, $mysql_password)) 
    die ("Couldn't connect to database: ".mysql_error()."\n");
  if (!mysql_select_db($mysql_db))
    die ("Couldn't connect to database: ".mysql_error()."\n");
  @mysql_query("SET NAMES $mysql_charset");
}

function osmdataurl($type,$id,$name,$lat,$lon,$railway="station") {
  $types = array(
    0 => "node",
    1 => "way",
    2 => "relation"
  );

  $link = "";
  if($id<0) { $id = -$id; $type = 2; }
  if($name == "" || !isset($name)) $name = "(без названия)";
  if($type == 0 && $lat>0 && $lon>0) {
    $left = $lon-0.002;
    $right = $lon+0.002;
    $bottom = $lat-0.0012;
    $top = $lat+0.0012;
    $url = "http://127.0.0.1:8111/load_and_zoom?left=$left&right=$right&top=$top&bottom=$bottom&select=node$id";
    $url = preg_replace("/,/",".",$url);
    $link = "&nbsp;<a href=\"$url\" target=\"_josmremote\"><img border=0 src=\"edit.png\"/></a>";
  } elseif ($type == 1) {
    $url = "http://127.0.0.1:8111/import?url=http://www.openstreetmap.org/api/0.6/way/$id/full";
    $link = "&nbsp;<a href=\"$url\" target=\"_josmremote\"><img border=0 src=\"edit.png\"/></a>";
  } elseif ($type == 2) {
    $url = "http://127.0.0.1:8111/import?url=http://www.openstreetmap.org/api/0.6/relation/$id/full";
    $link = "&nbsp;<a href=\"$url\" target=\"_josmremote\"><img border=0 src=\"edit.png\"/></a>";
  }
  return "<img src=\"".$types[$type].".png\"><img src=\"$railway.png\">&nbsp;<a href=\"http://www.openstreetmap.org/browse/".$types[$type]."/$id\">".$name."</a>$link\n";
}

function addhiddenframe() {
  ?>
<div style="display: none">
<iframe name="_josmremote">
</iframe>
</div>
  <?php
}

function calc_esr($r) {
  if(preg_match("/^\\d{6}$/", $r)) return $r;
  if(preg_match("/^-\\d{1,4}$/", $r)) return $r;
  if(!preg_match("/^\\d{5}$/", $r)) return null;

  $c = $r[0] + $r[1] * 2 + $r[2] * 3 + $r[3] * 4 + $r[4] * 5;
  $o = $c % 11;
  if ($o == 10) {
    $c = $r[0] * 3 + $r[1] * 4 + $r[2] * 5 + $r[3] * 6 + $r[4] * 7;
    $o = $c % 11;
    if ($o == 10) 
      $o = 0;
  }
  return $r.$o; 
}

function uncalc_esr($r) {
  if(preg_match("/^-\\d{1,4}$/", $r)) return $r;
  if(preg_match("/^\\d{1,5}$/", $r)) return $r;

  $r = substr($r,0,5);
  $r = preg_replace("/^0+/","",$r);
  return $r;
}

if (!function_exists("mb_strlen")) {
  function mb_strlen($s) { return strlen($s); }
}

?>
