<?php
require_once("config.php");
require_once("lib.php");

//////////////////////// ************* MODEL ***************** ///////////////////////////////
$output = array();
Header("Content-Type: text/html; charset=$site_charset\n"); 
date_default_timezone_set("Europe/Moscow");
setlocale(LC_ALL, $site_locale);
setlocale(LC_NUMERIC, "C");
addhiddenframe();

dbconn();

$region = $_GET["region"];
$region_sql = "'".mysql_real_escape_string($region)."'";
if ("'".$region."'" != $region_sql)
  die("Injection attempt");
$output["region_code"] = $region;

$query = "
  SELECT
    id,
    q_stations,
    q_found,
    q_uniq,
    q_nonuniq,
    q_esrnf,
    updated
  FROM
    regions
  WHERE
    source = $region_sql
";
if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

if (mysql_num_rows($res)!=1) 
  $output["single_region"] = FALSE;
else 
  $output["single_region"] = TRUE;

$regions_ids = array();
while ($r = mysql_fetch_row($res))
{
  $region_ids[] = $r[0];
  $q_stations = $r[1];
  $q_found = $r[2];
  $q_uniq = $r[3];
  $q_nonuniq = $r[4];
  $q_esrnf = $r[5];
  $updated = $r[6];
}
$ids_list = implode(",", $region_ids);
mysql_free_result($res);

$query = "
  SELECT DISTINCT
    name
  FROM
    regions
  WHERE
    source = $region_sql
";
if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

$region_names = array();
while ($r = mysql_fetch_row($res))
{
  $region_names[] = trim($r[0]);
}
$names_list = implode(", ", $region_names);
mysql_free_result($res);

$query = "
  SELECT DISTINCT
    esr_name
  FROM
    regions
  WHERE
    source = $region_sql
";
if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

$region_esr_names = array();
while ($r = mysql_fetch_row($res))
{
  $tmp = explode(' - ', trim($r[0]));
  if ($tmp[0] != "") 
    $region_esr_names[] = $tmp[0];
}
$esr_names_list = implode(", ", $region_esr_names);
mysql_free_result($res);

$output["region_name"] = "$names_list";
$output["short_region_name"] = $output["region_name"];
if (strtoupper($names_list) != strtoupper($esr_names_list)) 
{
  $output["region_name"] .= " ($esr_names_list)";
}

$query = "
  SELECT
    stations.name,
    regions.esr_name,
    stations.esr,
    1,
    railways.name,
    divisions.name,
    railways.map_url,
    stations.dup_esr,
    name_rzd0,
    name_tr4k1,
    name_rwua,
    stations.gdevagon_lat,
    stations.gdevagon_lon,
    stations.express_code,
    name_yarasp,
    yarasp_lat,
    yarasp_lon,
    yarasp_id,
    station_types.name,
    stations.railway_id,
    stations.closed,
    express.tutu_lat,
    express.tutu_lon,
    station_type_id,
    stations.express_code,
    trim(concat(rec3ty.status, ' ', rec3ty.name)),
    rec3ty.code,
    rec3ty.lat,
    rec3ty.lon
  FROM
    stations
    LEFT JOIN regions ON stations.region_id = regions.id
    LEFT JOIN railways ON stations.railway_id = railways.id
    LEFT JOIN divisions ON stations.division_id = divisions.id
    LEFT JOIN station_types ON stations.station_type_id = station_types.id
    LEFT JOIN express ON stations.express_code = express.express_code
    LEFT JOIN rec3ty ON rec3ty.esr = stations.esr
  WHERE 
    stations.region_id in ($ids_list)
  ORDER BY
    stations.name
";
if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

$output["rows"] = array();
while ($r = mysql_fetch_row($res))
{
  $output_row = array();
  
  $esr = $r[2];
  $output_row["esr"] = $esr;
  $output_row["name"] = $r[0];
  $tmp = explode (' - ', $r[1]);
  $output_row["subregion"] = $tmp[0];

   $output_row["neighbour"] = array();

  $output_row["osmnodes"] = array();

  $output_row["railway"] = $r[4];
  $output_row["division"] = $r[5];
  $output_row["railway_map_url"] = $r[6];
  $output_row["dup_esr"] = $r[7];

  $output_row["names"] = array();
  if ($r[8] != "") 
    $output_row["names"]["rzd0"] = $r[8];
  if ($r[9] != "") 
    $output_row["names"]["tr4"] = $r[9];
  if ($r[10] != "") 
    $output_row["names"]["rwua"] = $r[10];
  if ($r[14] != "") 
    $output_row["names"]["yarasp"] = $r[14];
  if ($r[25] != "")
    $output_row["names"]["3ty"] = $r[25];

  $output_row["gdevagon"] = array();
  $output_row["gdevagon"]["lat"] = $r[11];
  $output_row["gdevagon"]["lon"] = $r[12];

  $output_row["yarasp"] = array();
  $output_row["yarasp"]["lat"] = $r[15];
  $output_row["yarasp"]["lon"] = $r[16];
  $output_row["yarasp"]["id"] = $r[17];

  $output_row["express_code"] = $r[13];
  $output_row["station_type"] = $r[18];
  $output_row["railway_id"] = $r[19];
  $output_row["closed"] = $r[20];

  $output_row["tutu"] = array();
  $output_row["tutu"]["lat"] = $r[21];
  $output_row["tutu"]["lon"] = $r[22];

  $output_row["station_type_id"] = $r[23];
  
  $output_row["express"] = $r[24];
  
  $output_row["3ty"] = array();
  $output_row["3ty"]["code"] = $r[26];
  $output_row["3ty"]["lat"] = $r[27];
  $output_row["3ty"]["lon"] = $r[28];

  $output["rows"][$esr] = $output_row;
  $esrs[] = $esr;
}
unset($output_row);
mysql_free_result($res);

if (count($esrs) < 1) {
  print "В регионе нет станций!";
  exit;
}

$esr_list = implode(",", $esrs);

$query = "
  SELECT 
    neighb_stations.station_esr,
    stations.name,
    regions.name,
    regions.source,
    stations.region_id,
    stations.esr
  FROM 
    stations
    LEFT JOIN regions ON regions.id=stations.region_id,
    neighb_stations
  WHERE 
    neighb_stations.station_esr in ($esr_list) AND
    stations.esr = neighb_stations.neighb_esr
";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

while ($r = mysql_fetch_row($res))
{
  $tmp = array();
  $tmp["esr"] = $r[5];
  $tmp["name"] = $r[1];
  if ($r[3] != $region) {
    $tmp["region_name"] = $r[2];
    $tmp["region_code"] = $r[3]; 
    $tmp["region_id"] = $r[4];
  }
  $output["rows"][$r[0]]["neighbour"][] = $tmp;
}

mysql_free_result($res);

$query = "
  SELECT 
    osm2esr.esr,
    osmdata.type,
    osmdata.osm_id,
    osmdata.name,
    osm2esr.status,
    lat,
    lon,
    railway
  FROM
    osm2esr,
    osmdata
  WHERE
    osm2esr.esr in ($esr_list) AND
    osmdata.id = osm2esr.osmdata_id
";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

while ($r = mysql_fetch_row($res))
{
  $osmnode["type"] = $r[1];
  $osmnode["osm_id"] = $r[2];
  $osmnode["name"] = $r[3];
  $osmnode["status"] = $r[4];
  $osmnode["lat"] = $r[5];
  $osmnode["lon"] = $r[6];
  $osmnode["railway"] = $r[7];
  $output["rows"][$r[0]]["osmnodes"][] = $osmnode;
}
mysql_free_result($res);

foreach ($output["rows"] as $k => $output_row) {
  $status = -1;
  foreach ($output_row["osmnodes"] as $osmnode) {
    if ($status == -1) {
      $status = $osmnode["status"];
    } else {
      if ($status == 1 && $osmnode["status"] != 1) 
        $status = 2;
      if ($status == 0 && $osmnode["status"] != 0)
        $status = 2;
    }
  }
  if ($status == -1)
    $status = 0;
  if (($status == 0 || $status == 1) && $output["rows"][$k]["closed"] != "")
    $status = 3;
  $output["rows"][$k]["status"] = $status;
}

$query = "
  SELECT
    osmdata.type,
    osmdata.osm_id,
    osmdata.name,
    lat,
    lon,
    railway
  FROM
    osmdata 
    LEFT JOIN osm2esr ON 
      osmdata.id=osmdata_id 
  WHERE
    osmdata_id IS NULL AND
    user!='0' AND
    source=$region_sql
";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

$output["not_found"] = array();
while ($r = mysql_fetch_row($res)) {
  $output_row = array();
  $output_row["type"] = $r[0];
  $output_row["osm_id"] = $r[1];
  $output_row["name"] = $r[2];
  $output_row["lat"] = $r[3];
  $output_row["lon"] = $r[4];
  $output_row["railway"] = $r[5];
  $output["not_found"][] = $output_row;
}
unset($output_row);
mysql_free_result($res);


//////////////////////// ************* VIEW ***************** ///////////////////////////////
?>
<style>a { text-decoration: none; }</style>
<h1><a href="./">Единая сетевая разметка</a></h1>
<h3><?php
  echo $output["region_name"];
?></h3>
<?php
  echo "<p>ЕСР (найдено/всего): ".$q_found."/".$q_stations." (".round($q_found*100./$q_stations)."%)</p>";
  echo "<p>OSM (однозначно/неоднозначно/не найдено): ".$q_uniq."/".$q_nonuniq."/".$q_esrnf."</p>";
  echo "<p>Обновлено: ".date("H:i:s d.m.Y",$updated)."</p>";
?>
<b>По алфавиту</b> | <a href='./region:<?php echo $output["region_code"]; ?>:l'>По участкам</a> | <a href="legend">Легенда</a><p>
<table border="1" cellspacing="0" cellpadding="0">
  <tr>
    <th>
      ЕСР/<br>Эксп.
    </th>
    <th>
      Станция
    </th>
    <th>
      OSM
    </th>
    <th>
      Соседние станции
    </th>
<?php if (0) { ?>
    <th>
      Источник
    </th>
<?php } ?>
<?php if (!$output["single_region"]) { ?>
    <th>
      Регион
    </th>
<?php } ?>
    <th>
      Подчинение
    </th>
    <th>
      Искать
    </th>
    <th>
      &nbsp;
    </th>
  </tr>
<?php $color = array(0 => "white", 1 => "lightgreen", 2 => "yellow", 3 => "#CCCCCC"); ?>
<?php foreach ($output["rows"] as $output_row) { ?>
  <tr>
    <td style='background-color: <?php echo $color[$output_row["status"]]; ?>'>
      <?php 
        $tmp = $output_row["esr"];
        if ($output_row["dup_esr"] != "")
	  $tmp = "<strike>$tmp</strike>";
        echo "<a name=\"".$output_row["esr"]."\"></a><a href=\"./esr:".$output_row["esr"]."\">".$tmp."</a>";
        if ($output_row["express"])
          echo "<br/><font size=-1><a href=\"./express:".$output_row["express"]."\">".$output_row["express"]."</a></font>";
      ?>
    </td>
    <td style='background-color: <?php echo $color[$output_row["status"]]; ?>'>
      <?php
        $tmp = "<img src=\"st".$output_row["station_type_id"].".png\" />&nbsp;".$output_row["name"];
        if ($output_row["dup_esr"] != "")
	  $tmp = "<strike>$tmp</strike>";
        echo $tmp;
      ?>
    </td>
    <td style='background-color: <?php echo $color[$output_row["status"]]; ?>'>
      <?php
        $osmnodes = array();
	foreach ($output_row["osmnodes"] as $osmnode) 
	{
	  $osmnodes[] = "<div style='background-color: ".$color[$osmnode["status"]]."'>".osmdataurl($osmnode["type"],$osmnode["osm_id"],$osmnode["name"],$osmnode["lat"],$osmnode["lon"],$osmnode["railway"])."</div>";
	}
	if (count($osmnodes) > 0) {
	  echo implode("\n", $osmnodes);
	} else {
          if ($output_row["dup_esr"] != "")
	    echo "<strike><a href=./esr:".$output_row["dup_esr"].">ЕСР: ".$output_row["dup_esr"]."</a></strike>";
	  else
	    echo "&nbsp;";
	  }
      ?>
    </td>
    <td style='background-color: <?php echo $color[$output_row["status"]]; ?>'>
      <?php
        $neighbours = array();
        foreach ($output_row["neighbour"] as $neighbour) {
	  $tmp = $neighbour["name"]; 
          if (isset($neighbour["region_code"]) && $neighbour["region_code"] != "") {
	    $tmp = "<a href=\"./region:".$neighbour["region_code"]."#".$neighbour["esr"]."\">".$tmp."</a>";
	    #$tmp .= " (<a href=\"./region:";
	    #$tmp .= $neighbour["region_code"]."\">".$neighbour["region_name"]."</a>)";
	    $tmp .= " (".$neighbour["region_name"].")";
	  } elseif (isset($neighbour["region_id"]) && $neighbour["region_id"] === "0") {
	    $tmp = "<a href=\"./region:".$neighbour["region_id"]."#".$neighbour["esr"]."\">".$tmp."</a>";
	    #$tmp .= " (<a href=\"./region:";
	    #$tmp .= $neighbour["region_id"]."\">???</a>)";
            $tmp .= " (???)";
	  } else {
	    $tmp = "<a href=\"#".$neighbour["esr"]."\">".$tmp."</a>";
	  }
	  $neighbours[] = $tmp;
	}
	if (count($neighbours)>0) 
	  echo implode (", ", $neighbours);
	else
	  echo "&nbsp;";
      ?>
    </td>
<?php if (0) { // Источник ?>
    <td style='background-color: <?php echo $color[$output_row["status"]]; ?>'>
      <?php
	$tmp = array();
	if (isset($output_row["names"]["rzd0"])) 
	  $tmp[] = "РЖД";
	if (isset($output_row["names"]["tr4"])) 
	  $tmp[] = "ТР4";
	if (isset($output_row["names"]["rwua"])) 
	  $tmp[] = "УЗ";
	if (isset($output_row["names"]["yarasp"])) 
	  $tmp[] = "ЯР";

        if (count($tmp) > 0) {
	  $tmp = implode(", ", $tmp);
          if ($output_row["dup_esr"] != "")
	    $tmp = "<strike>$tmp</strike>";
	  echo $tmp;
	} else {
	  echo "&nbsp;";
	}
      ?>
    </td>
<?php } ?>
<?php if (!$output["single_region"]) { ?>
    <td style='background-color: <?php echo $color[$output_row["status"]]; ?>'>
      <?php
        $tmp = $output_row["subregion"];
	if ($tmp == "") $tmp = "&nbsp;";
        if ($output_row["dup_esr"] != "")
	  $tmp = "<strike>$tmp</strike>";
        echo $tmp;
      ?>
    </td>
<?php } ?>
    <td style='background-color: <?php echo $color[$output_row["status"]]; ?>'>
      <?php
        $tmp = "";
//        if ($output_row["railway_map_url"] != '') $tmp .= "<a href='".$output_row["railway_map_url"]."'>";
        $tmp .= "<a href='./railway:".$output_row["railway_id"]."'>";
        $tmp .= $output_row["railway"]." ж.д.";
 //       if ($output_row["railway_map_url"] != '') $tmp .= "</a>";
        $tmp .= "</a>";
	if ($output_row["division"] != '') $tmp .= ", ".$output_row["division"]." отд."; 
        if ($output_row["dup_esr"] != "")
	  $tmp = "<strike>$tmp</strike>";
        echo $tmp;
      ?>
    </td>
    <td style='background-color: <?php echo $color[$output_row["status"]]; ?>'>
      <?php
        $tmp = array();
	if ($output_row["yarasp"]["lat"] != 0 && $output_row["yarasp"]["lon"] != 0) {
          $tmp2 = "<a href='http://rasp.yandex.ru/info/station/"; 
          $tmp2 .=  $output_row["yarasp"]["id"]."'>rasp.yandex.ru</a>";
	  $tmp[] = $tmp2;
	}
	if ($output_row["3ty"]["lat"] != 0 && $output_row["3ty"]["lon"] != 0) {
	  $tmp2 = "<a href='http://3ty.ru/rasp/".$output_row["3ty"]["code"];
	  $tmp2 .= ".html'>3ty.ru</a>";
	  $tmp[] = $tmp2;
	}
	if ($output_row["tutu"]["lat"] != 0 && $output_row["tutu"]["lon"] != 0) {
	  $tmp2 = "<a href='http://www.tutu.ru/poezda/station/map/";
	  $tmp2 .= $output_row["express_code"]."'>tutu.ru</a>";
	  $tmp[] = $tmp2;
	}
	if ($output_row["gdevagon"]["lat"] != 0 && $output_row["gdevagon"]["lon"] != 0) {
          $tmp2 = "<a href='http://www.gdevagon.ru/scripts/info/station_detail.php?stid="; 
          $tmp2 .= substr($output_row["esr"],0,5)."'>gdevagon.ru</a>";
	  $tmp[] = $tmp2;
	}
	$tmp2  = "";
	if ($output["short_region_name"] != "")
	  $tmp2  = $output["short_region_name"].", ";
	$tmp2 .= "станция ".$output_row["name"];
	$tmp2  = urlencode(iconv($site_charset, "windows-1251", $tmp2));
	$tmp2  = "<a href='http://maps.yandex.ru/?text=" . $tmp2;
	$tmp2 .= "'>maps.yandex.ru</a>";
	// $tmp[] = $tmp2;

        if (count($tmp) > 0) {
	  echo implode(",<br>", $tmp);
	} else {
	  echo "&nbsp;";
	}
      ?>
    </td>
    <td style='background-color: <?php echo $color[$output_row["status"]]; ?>'>
      <?php
        if ($output_row["closed"] != "") {
          echo "Закрыта (<a href='http://www.openstreetmap.org/user/".urlencode($output_row["closed"]);
          echo "'>".$output_row["closed"]."</a>)";
        } else
          echo "&nbsp;";
      ?>
    </td>
    </tr>
  <?php
}
?>
</table>
<?php
  $osmnodes = array();
  foreach ($output["not_found"] as $osmnode) {
    $osmnodes[] = osmdataurl($osmnode["type"],$osmnode["osm_id"],$osmnode["name"],$osmnode["lat"],$osmnode["lon"],$osmnode["railway"]);
  }
  if (count($osmnodes) > 0) { 
    echo "<h3>Найдено в OSM, не найдено в ЕСР</h3><ul><li>";
    echo implode("</li><li>\n", $osmnodes);
    echo "</li></ul>";
  }
?>
