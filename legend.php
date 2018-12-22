<?php

require_once("config.php");
require_once("lib.php");
Header("Content-Type: text/html; charset=$site_charset\n");

dbconn();
?>
<style>a { text-decoration: none; }</style>
<h1><a href="./">Единая сетевая разметка</a></h1>
<h2>Легенда</h2>
<h3>Типы станций ЕСР</h3>
<ul>
<li><img src="st0.png" /> неизвестный</li>
<?php
  $res = mysql_query("SELECT id,name FROM station_types");
  while($row = mysql_fetch_row($res))
    print "<li><img src=\"st".$row[0].".png\"> ".$row[1]."</li>\n";
  mysql_free_result($res);
?>
</ul>
<h3>Типы объектов</h3>
<ul>
<li><img src="node.png" /> точка</li>
<li><img src="way.png" /> линия</li>
<li><img src="relation.png" /> отношение</li>
</ul>
<h3>Типы станций OSM</h3>
<ul>
<li><img src="station.png" /> railway=station</li>
<li><img src="halt.png" /> railway=halt</li>
</ul>
<h3>Источники</h3>
<ul>
<li><b>РЖД</b> - сайт РЖД (Российских Железных Дорог)</li>
<li><b>УЗ</b> - сайт Укрзализныця (Украинских Железных Дорог)</li>
<li><b>ТР4</b> - тарифное руководство № 4</li>
<li><b>tr4.info</b> - тарифное руководство № 4 на сайте <a href="https://tr4.info/" target="_blank">tr4.info</a></li>
<li><b>ЯР</b> - Яндекс.Расписания</li>
<li><b>ЭТП</b> - сайт ЭТП РЖД (справочник НСИ)</li>
<li><b>3ty.ru</b> - сайт <a href="http://3ty.ru" target="_blank">3ty.ru</a>
<li><b>railwayz.info</b> - сайт <a href="http://railwayz.info" target="_blank">Фотолинии</a>
<li><b>unla.webservis.ru</b> - сайт <a href="http://unla.webservis.ru/" target="_blank">unla.webservis.ru</a>
<li><b>ОСЖД</b> - сайт <a href="http://osjd.org/" target="_blank">Организации сотрудничества железных дорог</a>
</ul>
<h3>Другое</h3>
<ul>
<li><img src="edit.png"> ссылка на JOSM Remote Control</li>
</ul>
