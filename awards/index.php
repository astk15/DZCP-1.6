<?php
/**
 * DZCP - deV!L`z ClanPortal 1.6 Final
 * http://www.dzcp.de
 */

## OUTPUT BUFFER START ##
include("../inc/buffer.php");

## INCLUDES ##
include(basePath . "/inc/debugger.php");
include(basePath . "/inc/config.php");
include(basePath . "/inc/bbcode.php");

## SETTINGS ##
$where = _site_awards;
$title = $pagetitle . " - " . $where . "";
$dir = "awards";

## SECTIONS ##
switch ($action):
    default:
        $qry = db("SELECT * FROM `" . $db['squads'] . "` ORDER BY `pos`;");
        while ($get = _fetch($qry)) {
            if (!empty($_GET['showsquad']) || !empty($_GET['show'])) {
                if ($_GET['showsquad'] == $get['id'] || $_GET['show'] == $get['id']) {
                    $shown = show(_klapptext_show, array("id" => $get['id']));
                    $display = "";
                } else {
                    $shown = show(_klapptext_dont_show, array("id" => $get['id']));
                    $display = "none";
                }
            } else {
                $shown = show(_klapptext_dont_show, array("id" => $get['id']));
                $display = "none";
            }

            $qrym = db("SELECT s1.`id`,s1.`squad`,s1.`date`,s1.`place`,s1.`prize`,s1.`url`,s1.`event`,s2.`icon`,s2.`name` FROM `" .
                $db['awards'] . "` AS `s1` LEFT JOIN `" . $db['squads'] . "` AS `s2` ON s1.`squad` = s2.`id` WHERE s1.`squad` = " .
                $get['id'] . " ORDER BY s1.`date` DESC LIMIT " . config('m_awards') . ";");

            $entrys = cnt($db['awards'], " WHERE `squad` = " . $get['id']);
            $i = $entrys - ($page - 1) * config('m_awards');
            $awards = "";
            $color = 0;
            while ($getm = _fetch($qrym)) {
                $class = ($color % 2) ? "contentMainSecond" : "contentMainFirst";
                $color++;

                if ($getm['place'] == "1")
                    $replace = _awards_erster_img;
                elseif ($getm['place'] == "2")
                    $replace = _awards_zweiter_img;
                elseif ($getm['place'] == "3")
                    $replace = _awards_dritter_img;
                else
                    $replace = $getm['place'];

                $event = show(_awards_event, array("event" => re($getm['event']), "url" => $getm['url']));
                $awards .= show($dir . "/awards_show", array("class" => $class,
                    "date" => date("d.m.Y", $getm['date']),
                    "place" => $replace,
                    "prize" => re($getm['prize']),
                    "event" => $event));
            }

            $show_all = "";
            if (cnt($db['awards'], " WHERE `squad` = " . $get['id']) > config('m_awards'))
                $show_all = show(_list_all_link, array("id" => $get['id']));

            $showawards = show($dir . "/awards", array("squad" => _awards_head_squad,
                "date" => _awards_head_date,
                "place" => _awards_head_place,
                "prize" => _awards_head_prize,
                "url" => _awards_head_link,
                "awards" => $awards,
                "show_all" => $show_all));

            if (cnt($db['awards'], " WHERE squad = " . $get['id']) != 0) {
                $img = show(_gameicon, array("icon" => re($get['icon'])));
                $show .= show($dir . "/squads_show", array("id" => $get['id'],
                    "shown" => $shown,
                    "display" => $display,
                    "awards" => $showawards,
                    "squad" => $squad . " (" . cnt($db['awards'], " WHERE `squad` = " . $get['id']) . ")",
                    "img" => $img));
            }
        }

        $qry = db("SELECT `game`,`icon` FROM `" . $db['squads'] . "` GROUP BY `game` ORDER BY `game` ASC");
        $legende = '';
        $color = 0;
        while ($get = _fetch($qry)) {
            $class = ($color % 2) ? "contentMainSecond" : "contentMainFirst";
            $color++;
            $legende .= show(_awards_legende, array("game" => re($get['game']), "img" => squad(re($get['icon'])), "class" => $class));
        }

        $legende = show($dir . "/legende", array("legende_head" => _awards_head_legende, "legende" => $legende));

        $anz_awards = cnt($db['awards']);
        $anz_place_1 = cnt($db['awards'], "WHERE `place` = 1");
        $anz_place_2 = cnt($db['awards'], "WHERE `place` = 2");
        $anz_place_3 = cnt($db['awards'], "WHERE `place` = 3");

        $place1_percent = $anz_awards >= 1 && $anz_place_1 >= 1 ? round($anz_place_1 * 100 / $anz_awards, 1) : 0;
        $place2_percent = $anz_awards >= 1 && $anz_place_2 ? round($anz_place_2 * 100 / $anz_awards, 1) : 0;
        $place3_percent = $anz_awards >= 1 && $anz_place_3 ? round($anz_place_3 * 100 / $anz_awards, 1) : 0;

        $place1_rawpercent = $anz_awards >= 1 && $anz_place_1 ? round($anz_place_1 * 100 / $anz_awards, 0) : 0;
        $place2_rawpercent = $anz_awards >= 1 && $anz_place_2 ? round($anz_place_2 * 100 / $anz_awards, 0) : 0;
        $place3_rawpercent = $anz_awards >= 1 && $anz_place_3 ? round($anz_place_3 * 100 / $anz_awards, 0) : 0;

        $place1_balken = show(_votes_balken, array("width" => ($anz_place_1 ? $place1_rawpercent : 1)));
        $place2_balken = show(_votes_balken, array("width" => ($anz_place_2 ? $place2_rawpercent : 1)));
        $place3_balken = show(_votes_balken, array("width" => ($anz_place_3 ? $place3_rawpercent : 1)));

        $anz_awards_out = show(_awards_stats, array("anz" => $anz_awards));
        $anz_place_1_out = show(_awards_stats_1, array("anz" => $anz_place_1));
        $anz_place_2_out = show(_awards_stats_2, array("anz" => $anz_place_2));
        $anz_place_3_out = show(_awards_stats_3, array("anz" => $anz_place_3));

        $stats = show($dir . "/stats", array("head_stats" => _head_stats,
            "stats" => $anz_awards_out,
            "icon_1" => _awards_erster_img,
            "icon_2" => _awards_zweiter_img,
            "icon_3" => _awards_dritter_img,
            "stats1" => $anz_place_1_out,
            "stats2" => $anz_place_2_out,
            "stats3" => $anz_place_3_out,
            "1_perc" => $place1_percent,
            "2_perc" => $place2_percent,
            "3_perc" => $place3_percent,
            "1_balken" => $place1_balken,
            "2_balken" => $place2_balken,
            "3_balken" => $place3_balken));

        $show = cnt($db['awards']) != 0 ? $show : show(_no_entrys_yet, array("colspan" => "10"));
        $index = show($dir . "/main", array("head" => _awards_head, "stats" => $stats, "legende" => $legende, "show" => $show));
        break;
    case 'showall';
        $qry = db("SELECT * FROM `" . $db['squads'] . "` WHERE `id` = " . (int)($_GET['id']) . ";");
        while ($get = _fetch($qry)) {
            if (isset($_GET['showsquad'])) {
                if ($_GET['showsquad'] == $get['id']) {
                    $shown = show(_klapptext_show, array("id" => $get['id']));
                    $display = "";
                } else {
                    $shown = show(_klapptext_dont_show, array("id" => $get['id']));
                    $display = "none";
                }
            } else {
                if ($get['shown'] == "1" || $get['shown'] == "0") {
                    $shown = show(_klapptext_show, array("id" => $get['id']));
                    $display = "";
                } else {
                    $shown = show(_klapptext_dont_show, array("id" => $get['id']));
                    $display = "none";
                }
            }

            $qrym = db("SELECT s1.`id`,s1.`squad`,s1.`date`,s1.`place`,s1.`prize`,s1.`url`,s1.`event`,s2.`icon`,s2.`name` FROM `" .
                $db['awards'] . "` AS `s1` LEFT JOIN `" . $db['squads'] . "` AS `s2` ON s1.`squad` = s2.`id` WHERE s1.`squad` = " .
                $get['id'] . " ORDER BY s1.`date` DESC LIMIT " . ($page - 1) * config('m_awards') . "," . config('m_awards') . ";");

            $entrys = cnt($db['awards'], " WHERE `squad` = " . $get['id']);
            $i = $entrys - ($page - 1) * config('m_awards');
            $awards = "";
            while ($getm = _fetch($qrym)) {
                if ($getm['place'] == 1)
                    $replace = _awards_erster_img;
                elseif ($getm['place'] == 2)
                    $replace = _awards_zweiter_img;
                elseif ($getm['place'] == 3)
                    $replace = _awards_dritter_img;
                else
                    $replace = $getm['place'];

                $event = show(_awards_event, array("event" => $getm['event'], "url" => $getm['url']));
                $class = ($color % 2) ? "contentMainSecond" : "contentMainFirst";
                $color++;
                $awards .= show($dir . "/awards_show", array("class" => $class,
                    "date" => date("d.m.Y", $getm['date']),
                    "place" => $replace,
                    "prize" => $getm['prize'],
                    "event" => $event));

            }

            $squad = show(_member_squad_squadlink, array("squad" => re($get['name']), "id" => $get['id'], "shown" => $shown));
            $img = show(_gameicon, array("icon" => re($get['icon'])));
            $nav = nav($entrys, config('m_awards'), "?action=showall&amp;id=" . $get['id'] . "");
            $showawards = show($dir . "/awards_show_all", array("squad" => _awards_head_squad,
                "date" => _awards_head_date,
                "place" => _awards_head_place,
                "prize" => _awards_head_prize,
                "url" => _awards_head_link,
                "nav" => $nav,
                "awards" => $awards));

            if (cnt($db['awards'], " WHERE `squad` = " . $get['id']) != 0) {
                $show .= show($dir . "/squads_show_all", array("id" => $get['id'],
                    "shown" => $shown,
                    "display" => $display,
                    "awards" => $showawards,
                    "squad" => $squad . " (" . cnt($db['awards'], " WHERE `squad` = " . $get['id']) . ")",
                    "img" => $img));
            }
        }

        $qry = db("SELECT `game`,`icon` FROM `" . $db['squads'] . "` GROUP BY `game` ORDER BY `game` ASC;");
        $legende = '';
        while ($get = _fetch($qry)) {
            $class = ($color % 2) ? "contentMainSecond" : "contentMainFirst";
            $color++;
            $img = squad($get['icon']);
            $legende .= show(_awards_legende, array("game" => re($get['game']), "img" => $img, "class" => $class));
        }

        $legende = show($dir . "/legende", array("legende_head" => _awards_head_legende, "legende" => $legende));
        $stats = show(_awards_stats, array("anz" => cnt($db['awards'])));
        $index = show($dir . "/main", array("head" => _awards_head, "stats" => $stats, "legende" => $legende, "show" => $show));
        break;
endswitch;

## INDEX OUTPUT ##
page($index, $title, $where);