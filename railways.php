<?php
  require_once("config.php");
  require_once("lib.php");
  Header("Content-Type: text/html; charset=$site_charset\n");
  date_default_timezone_set("Europe/Moscow");
?>
<style>a { text-decoration: none; }</style>
<h1>Единая сетевая разметка</h1>
<p>Единая сетевая разметка (ЕСР) — система цифрового обозначения станций на территории 
стран бывшего СССР.</p>
<p><a href="http://forum.openstreetmap.org/viewtopic.php?id=9084">Обсуждение на форуме OpenStreetMap</a>.</p>
<p>Данные в CSV: <a href="esr.csv">ЕСР</a>, <a href="osm2esr.csv">OSM-ЕСР</a>, <a href="express.csv">Экспресс</a>.<p>

<p>
<form action="search" method="post">
Поиск: <input type=text name=q value="" size=32>
<input type=submit value=Поиск>
</form>
</p>
<p><img valign=center src=14px-Smiley.svg.png /> <b>Экватор пройден (50% распознаётся).</b></p>
<p><a href=./>По регионам</a> | <b>По железным дорогам</b></p>
<?php

dbconn();

$query = "
  SELECT
    railway_id,
    railways.name,
    COUNT(stations.id),
    express_railway
  FROM
    stations
    LEFT JOIN railways ON railways.id=railway_id
  WHERE
    dup_esr=''
  GROUP BY railway_id
  ORDER BY IF(railway_id=0,1000,railway_id)
";

if (!($res = mysql_query($query)))
  die ("Error: ".mysql_error()."\n");

echo "<table border=1 cellspacing=0>\n<tr><td align=center><b>Э-3</b></td><td align=center><b>Дорога</b></td><td align=center><b>Станций</b></tr>\n";
while ($r = mysql_fetch_row($res)) {
  print "<tr><td>".($r[3]?$r[3]:"&nbsp;")."</td><td><a href=./railway:".$r[0].">".($r[0]?$r[1]:"*** не определено ***")."</a></td><td>".$r[2]."</td></tr>\n";
}
print "</table>";
