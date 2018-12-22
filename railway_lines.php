<?php
#Header("HTTP/1.0 504 Temporary disabled");
require_once("config.php");
require_once("lib.php");

//////////////////////// ************* MODEL ***************** ///////////////////////////////
$output = array();
Header("Content-Type: text/html; charset=$site_charset\n"); 
date_default_timezone_set("Europe/Moscow");
setlocale(LC_ALL, $site_locale);
addhiddenframe();

dbconn();

$ext = isset($_GET["ext"])?1:0;
$id = intval($_GET["id"]);

$query = "
  SELECT DISTINCT
    id,
    name,
    url,
    map_url
  FROM
    railways
  WHERE
    id = $id
";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

if (mysql_num_rows($res)<1) { 
  print "Net takoi dorogi";
  exit;
}

$r = mysql_fetch_row($res);

$output["railway"] = $r[1];
$output["url"] = $r[2];
$output["map_url"] = $r[3];

$query = "
  SELECT
    divisions.name,
    divisions.map_url,
    division_types.name
  FROM
    divisions
    LEFT JOIN division_types ON division_type_id=division_types.id
  WHERE
    railway_id = $id AND
    divisions.name <> ''
";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

$divisions = array();
while ($r = mysql_fetch_row($res)) {
  if (!isset($divisions[$r[2]])) 
    $divisions[$r[2]] = array();
  $divisions[$r[2]][] = array("name" => $r[0], "map_url" => $r[1]);
}

$query = "
  SELECT DISTINCT
    id,
    name,
    source,
    country,
    iso3166
  FROM
    regions
";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

$regions = array();
while ($r = mysql_fetch_row($res))
{
  $reg = array();
  $reg["id"] = $r[0];
  $reg["name"] = $r[1];
  $reg["source"] = $r[2];
  $reg["country"] = $r[3];
  $reg["iso3166"] = $r[4];
  $regions[$r[0]] = $reg;
}
mysql_free_result($res);

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
    stations.name_rzd0,
    stations.name_tr4k1,
    stations.name_rwua,
    stations.gdevagon_lat,
    stations.gdevagon_lon,
    stations.express_code,
    stations.name_yarasp,
    stations.yarasp_lat,
    stations.yarasp_lon,
    stations.yarasp_id,
    station_types.name,
    stations.railway_id,
    `lines`.comment,
    lines_end1.name,
    lines_end2.name,
    stations.closed,
    express.tutu_lat,
    express.tutu_lon,
    stations.station_type_id,
    stations.express_code,
    stations.name_nsi,
    trim(concat(rec3ty.status, ' ', rec3ty.name)),
    rec3ty.code,
    rec3ty.lat,
    rec3ty.lon,
    `lines`.id
  FROM
    `lines`
    LEFT JOIN stations_of_lines ON `lines`.id = stations_of_lines.line_id
    LEFT JOIN stations ON stations_of_lines.esr = stations.esr
    LEFT JOIN regions ON stations.region_id = regions.id
    LEFT JOIN railways ON stations.railway_id = railways.id
    LEFT JOIN divisions ON stations.division_id = divisions.id
    LEFT JOIN station_types ON stations.station_type_id = station_types.id
    LEFT JOIN stations AS lines_end1 ON lines_end1.esr = `lines`.esr1
    LEFT JOIN stations AS lines_end2 ON lines_end2.esr = `lines`.esr2
    LEFT JOIN express ON express.express_code=stations.express_code
    LEFT JOIN rec3ty ON rec3ty.esr = stations.esr
  WHERE 
    stations.railway_id = $id
  ORDER BY
    IF(`lines`.sidx<0,100000,`lines`.sidx),
    stations_of_lines.sidx
";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

$output["rows"] = array();
$ord = 0;
$esr_rows = array();
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
  if ($r[28] != "") 
    $output_row["names"]["nsi"] = $r[28];
  if ($r[29] != "")
    $output_row["names"]["3ty"] = $r[29];

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
  $output_row["closed"] = $r[23];
  
  $output_row["3ty"] = array();
  $output_row["3ty"]["code"] = $r[30];
  $output_row["3ty"]["lat"] = $r[31];
  $output_row["3ty"]["lon"] = $r[32];

  $line_part = array();
  if ($r[21] != "") 
    $line_part[] = $r[21];
  if ($r[20] != "") 
    $line_part[] = $r[20];
  if ($r[22] != "") 
    $line_part[] = $r[22];
  $output_row["line"] = implode(" -- ", $line_part);
  $output_row["line_id"] = $r[33];

  $output_row["tutu"] = array();
  $output_row["tutu"]["lat"] = $r[24];
  $output_row["tutu"]["lon"] = $r[25];

  $output_row["station_type_id"] = $r[26];
  
  $output_row["express"] = $r[27];

  $output["rows"][$ord] = $output_row;

  if (!isset($esr_rows[$esr]))
    $esr_rows[$esr] = array();
  $esr_rows[$esr][] = $ord;

  $ord++;
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
  $tmp["region_name"] = $r[2];
  $tmp["region_code"] = $r[3]; 
  $tmp["region_id"] = $r[4];
  foreach ($esr_rows[$r[0]] as $row)
    $output["rows"][$row]["neighbour"][] = $tmp;
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

  foreach ($esr_rows[$r[0]] as $row)
    $output["rows"][$row]["osmnodes"][] = $osmnode;
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
    FALSE
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

$output_rows = $output["rows"];
$output["rows"] = array();
foreach ($output_rows as $k => $row) {
  $curline = $row["line"];
  unset ($row["line"]);
  if (!isset($output["rows"][$curline]))
    $output["rows"][$curline] = array();
  $output["rows"][$curline][] = $row;
}
unset($output_rows);


//////////////////////// ************* VIEW ***************** ///////////////////////////////
?>
<style>a { text-decoration: none; }</style>
<h1><a href="./">Единая сетевая разметка</a></h1>
<?php
echo "<h2>".$output["railway"]." ж.д.</h2>\n";

echo "<table border='0'>";
if ($output["url"] != '')
  echo "<tr><td>Официльный сайт</td><td>&nbsp;</td><td><a href='".$output["url"]."'>".$output["url"]."</td></tr>";
if ($output["map_url"] != '')
  echo "<tr><td>Схема дороги</td><td>&nbsp;</td><td><a href='".$output["map_url"]."'>скачать</a></td></tr>";
echo "</table>";

foreach ($divisions as $cat => $divs) {
  echo "<h4>".$cat."</h4>\n<ul>\n";
  foreach ($divs as $div) {
    echo "<li>".$div["name"];
    if ($div["map_url"] != '') {
      echo " (";
      echo "<a href='".$div["map_url"]."'>схема</a>";
      echo ")";
    }
    echo "</li>\n";
  }
  echo "</ul>\n";
}

?>
<a href="legend">Легенда</a><p>
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
    <th>
      Источник
    </th>
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
<?php
  $colspan = 8;
  if (!$output["single_region"])
    $colspan = 9;
  foreach ($output["rows"] as $line_name => $output_line) { 
    if ($line_name != "")
      echo "<tr><td colspan='$colspan' bgcolor='#999999'><b>$line_name</b></td></tr>\n";
    foreach ($output_line as $output_row) {
?>
  <tr>
    <td style='background-color: <?php echo $color[$output_row["status"]]; ?>'>
      <?php 
        $tmp = $output_row["esr"];
        if ($output_row["dup_esr"] != "")
	  $tmp = "<strike>$tmp</strike>";
        echo "<a name='".$output_row["esr"]."'></a>";
        echo "<a name='".$output_row["line_id"].$output_row["esr"]."'></a>";
        echo "<a href=\"./esr:".$output_row["esr"]."\">".$tmp."</a>";
        if ($output_row["express"])
          echo "<br/><font size=-1><a href=\"./express:".$output_row["express"]."\">".$output_row["express"]."</a></font>";
        if ($ext) print "<br/><a href=cnsi/cnsi_node.php?esr=".$output_row["esr"].">CNSI</a>";
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
	    $tmp = "<a href=\"./region:".$neighbour["region_code"].":l#".$neighbour["esr"]."\">".$tmp."</a>";
	    #$tmp .= " (<a href=\"./region:";
	    #$tmp .= $neighbour["region_code"].":l\">".$neighbour["region_name"]."</a>)";
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
	if (isset($output_row["names"]["nsi"])) 
	  $tmp[] = "ЭТП";
	
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
	  $tmp2 = "<a href='http://3ty.ru/rasp/";
	  $tmp2 .= $output_row["3ty"]["code"].".html'>3ty.ru</a>";
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
