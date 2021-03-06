<?php
  require_once("config.php");
  require_once("lib.php");
  Header("Content-Type: text/html; charset=$site_charset\n");
  addhiddenframe();
?>
<style>a { text-decoration: none; }</style>
<h1><a href='./'>Единая сетевая разметка</a></h1>
<?php

dbconn();

$esr = $_GET['esr'];
if(!preg_match("/^\d{6}$/",$esr))
  die;

$query = "SELECT id,name FROM station_types";
if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

$stypes = array(0=>"");
while($row = mysql_fetch_assoc($res))
  $stypes[$row["id"]]=$row["name"];

$query = "
  SELECT
    stations.esr AS esr,
    stations.express_code AS express_code,
    express.name AS express_name,
    stations.name AS name,
    stations.name_rzd0 AS name_rzd0,
    stations.name_tr4k1 AS name_tr4k1,
    stations.name_rwua AS name_rwua,
    stations.name_yarasp AS name_yarasp,
    stations.yarasp_id AS code_yarasp,
    stations.yarasp_addr AS yarasp_addr,
    regions.name AS region,
    regions.source AS region_code,
    railways.name AS railway,
    divisions.name AS division,
    railways.map_url AS map_url,
    station_type_id AS stype,
    stations.name_tr4k2 AS name_tr4k2,
    stations.name_nsi AS name_nsi,
    trim(concat(rec3ty.status, ' ', rec3ty.name)) AS name_3ty,
    rec3ty.code AS code_3ty,
    stations.comment AS comment,
    rw_st.id AS code_rw,
    rw_st.name AS name_rw,
    rw_st.express AS rw_express,
    stations.dup_esr,
    cnsi.class AS class,
    cnsi.name AS name_cnsi,
    unla2.name AS name_unla,
    unla.express AS unla_express,
    unla.year AS unla_year,
    name_en,
    name_osjd,
    name_osjd_en,
    wiki_cache.title AS name_wiki,
    stations.km AS km,
    tr4_st.name AS tr4_name,
    gioc_st.name AS gioc_name
  FROM
    stations
    LEFT JOIN regions ON stations.region_id = regions.id
    LEFT JOIN railways ON stations.railway_id = railways.id
    LEFT JOIN divisions ON stations.division_id = divisions.id
    LEFT JOIN express ON stations.express_code = express.express_code
    LEFT JOIN rec3ty ON rec3ty.esr = stations.esr
    LEFT JOIN rw_st ON rw_st.esr = stations.esr
    LEFT JOIN cnsi ON cnsi.esr = stations.esr
    LEFT JOIN unla ON unla.esr = stations.esr
    LEFT JOIN unla2 ON unla2.esr = stations.esr
    LEFT JOIN wiki_cache ON stations.id=wiki_cache.station_id
    LEFT JOIN tr4_st ON stations.esr = tr4_st.esr
    LEFT JOIN gioc_st ON stations.esr = gioc_st.esr
  WHERE
    stations.esr = '".mysql_real_escape_string($esr)."'
";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

if(mysql_num_rows($res)<=0)
  die;
$row = mysql_fetch_assoc($res);
// Dirty hack by Glad
// $row["name_tr4k2"] = "(данные еще не обработаны)";
// End dirty hack
print "<h2>".$row['esr'].": ".$row['name']."</h2>\n";

$fields=array(
  "esrn" => "Код ЕСР",
  "express_code" => "Код Экспресс-3",
  "region" => "Регион",
  "railway" => "Железная дорога",
  #"division" => "Отделение",
  "stype" => "Статус",
  "class" => "Класс",
  "lines" => "Линии",
  "km" => "Километраж",
  "name" => "Название",
  "name_en" => "Название (англ.)",
  "name_rzd0" => "Название (РЖД)",
  "name_nsi" => "Название (ЭТП РЖД)",
  "name_tr4k2" => "Название (Тарифное руководство N4)",
  "name_tr4k1" => "Название (ТР4, справочник тарифных расстояний)",
  "tr4_name" => "Название (tr4.info)",
  "name_rwua" => "Название (Укрзализныци old)",
  "gioc_name" => "Название (Укрзализныци)",
  "name_3ty" => "Название (3ty.ru)",
  "name_rw" => "Название (railwayz.info)",
  "rw_express" => "Экспресс-3 (railwayz.info)",
  "name_yarasp" => "Название (Яндекс.Расписания)",
  "yarasp_addr" => "Адрес (Яндекс.Расписания)",
  "name_unla" => "Название (unla.webservis.ru)",
  "unla_express" => "Экспресс-3 (unla.webservis.ru)",
  "unla_year" => "Год основания (unla.webservis.ru)",
  "name_osjd" => "Название (ОСЖД)",
  "name_osjd_en" => "Название (ОСЖД англ.)",
  "name_wiki" => "Wikipedia",
  "comment" => "Примечание",
);

$row["esrn"] = $row["esr"]." (".$row["name"].")";

if($row['express_code'])
  $row['express_code'] = "<a href=./express:".$row['express_code'].">".$row['express_code']."</a> (".$row['express_name'].")";

if($row['map_url'])
  $row['railway'] = "<a href=\"".$row["map_url"]."\">".$row['railway']."</a>";

$row["stype"] = $stypes[$row["stype"]];

$row["region"] = "<a href='/esr/region:" . $row["region_code"]
               . ":a#" . $esr ."'>" .$row["region"] . "</a>";

$row["name_yarasp"] = "<a href='http://rasp.yandex.ru/info/station/"
                    . $row["code_yarasp"] . "'>" . $row["name_yarasp"]
                    . "</a>";
                    
$row["name_3ty"] = "<a href='http://3ty.ru/rasp/" . $row["code_3ty"] 
                 . ".html'>" . $row["name_3ty"] . "</a>";

$row["name_rw"] = "<a href='https://railwayz.info/photolines/station/" . $row["code_rw"]
                . "'>" . $row["name_rw"] . "</a>"; 

$row["tr4_name"] = "<a href='https://tr4.info/station/" . $row["esr"]
                . "'>" . $row["tr4_name"] . "</a>"; 

$row["name_wiki"] = "<a href=\"https://ru.wikipedia.org/wiki/".urlencode(str_replace(" ","_",$row["name_wiki"]))."\" target=\"_new\">".$row["name_wiki"]."</a>";

if($row["class"]==6) $row["class"] = "внеклассный";
elseif($row["class"]==7) $row["class"] = "-";
elseif($row["class"]==0) $row["class"] = "";

$query = "
  SELECT
    esr,
    name,
    dup_esr
  FROM
    stations
  WHERE
    dup_esr='$esr'
";
if($row["dup_esr"])
  $query.= "OR esr='".$row["dup_esr"]."' OR (dup_esr='".$row['dup_esr']."' AND esr!='".$row['esr']."')";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

if (mysql_num_rows($res)>0) {
  print "Дублирующие коды<ul>\n";
  while($rrow = mysql_fetch_row($res))
    print "<li/><a href=./esr:".$rrow[0].">".($rrow[2]?"<s>":"").$rrow[0].": ".$rrow[1].($rrow[2]?"</s>":"")."</a>\n";
  $query = "
    SELECT
      old_esr,
      esr
    FROM
      crimea
    WHERE
      esr='$esr'
      OR old_esr='$esr'
";
  if (!($res = mysql_query($query)))
    die ("Error: ".mysql_error()."\n");

  if (mysql_num_rows($res)>0) {
    $rrow = mysql_fetch_row($res);
    if ($rrow[0] == $esr)
      print "<li/><a href=./esr:".$rrow[1].">".$rrow[1].": ".$row["name"]."</a> (Россия)\n";
    else
      print "<li/><a href=./esr:".$rrow[0]."><s>".$rrow[0].": ".$row["name"]."</s></a> (Украина)\n";
  }
  print "</ul>\n";
}

$query = "
  SELECT 
    line_id,
    end1.name,
    `lines`.comment,
    end2.name
  FROM 
    stations_of_lines,
    `lines`
    LEFT JOIN stations AS end1 ON end1.esr = `lines`.esr1
    LEFT JOIN stations AS end2 ON end2.esr = `lines`.esr2
  WHERE
    stations_of_lines.esr = '".mysql_real_escape_string($esr)."' AND
    `lines`.id = line_id 
";
if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

$lines = array();
while($rrow = mysql_fetch_row($res)) {
  $line = array("id" => $rrow[0]);
  $route_part = array();
  if ($rrow[1] != '') $route_part[] = $rrow[1];
  if ($rrow[2] != '') $route_part[] = $rrow[2];
  if ($rrow[3] != '') $route_part[] = $rrow[3];
  $line["route"] = implode(" -- ", $route_part);
  $lines[] = $line;
}

$lines_part = array();
foreach($lines as $line) {
  $lines_part[] = "<a href='/esr/region:" . $row["region_code"] . ":l#" 
                . $line["id"] . $esr . "'>" . $line["route"] . "</a>";
}

if (count($lines_part)>0) 
  $row["lines"] = implode("<br>", $lines_part);


print "<table border=0>\n";
print "<tr><td colspan=3><hr></td></tr>\n";

foreach($fields as $k=>$v) {
  print "<tr><td align=right><b>$v:</b></td><td>&nbsp;</td><td align=left>".$row[$k]."</td></tr>\n";
}

$query = "
  SELECT 
    neighb_esr,
    name
  FROM 
    stations,
    neighb_stations
  WHERE 
    neighb_stations.station_esr=$esr AND
    stations.esr = neighb_stations.neighb_esr
";
if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

$cnt = 0;
while($row = mysql_fetch_row($res)) {
  print "<tr><td align=right>".(!$cnt?"<b>Соседние станции (ТР4):</b>":"&nbsp;")."</td><td>&nbsp;</td>\n";
  print "<td align=left><a href=\"./esr:".$row[0]."\">".$row[0]."</a>&nbsp;".$row[1]."</td></tr>\n";
  $cnt++;
}

print "<tr><td colspan=3><hr></td></tr>\n";
print "</table>\n";

$query = "
  SELECT 
    osm2esr.esr AS esr,
    osmdata.type AS type,
    osmdata.osm_id AS osm_id,
    osmdata.lat AS lat,
    osmdata.lon AS lon,
    osmdata.name AS name,
    osmdata.alt_name AS alt_name,
    osmdata.railway AS railway,
    osm2esr.status AS status
  FROM
    osm2esr,
    osmdata
  WHERE
    osm2esr.esr = '".mysql_real_escape_string($esr)."' AND
    osmdata.id = osm2esr.osmdata_id
";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

print "<h3>Найдены в OSM:<h3>";
print("<table border=0>");
$types = array(0=>"node",1=>"way");

while($row = mysql_fetch_assoc($res))
  #print $row["lat"];
  print "<tr><td>".osmdataurl($row["type"],$row["osm_id"],$row["name"],$row["lat"],$row["lon"],$row["railway"])."</td></tr>";

print "</table>\n";

?>
