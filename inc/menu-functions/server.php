<?php
/**
 * DZCP - deV!L`z ClanPortal 1.6 Final
 * http://www.dzcp.de
 * Menu: Gameserver
 * @param int $serverID
 * @return string
 * @throws \phpFastCache\Exceptions\phpFastCacheInvalidArgumentException
 */
function server($serverID = 0)
{
    global $db, $cache;

    header('Content-Type: text/html; charset=utf-8');
    if (!fsockopen_support()) return _fopen;

    $servernavi = '';
    if (empty($serverID)) {
        $qry = db("SELECT `id` FROM " . $db['server'] . " WHERE `navi` = '1'");
        $st = 0;
        while ($get = _fetch($qry)) {
            $st++;
            $servernavi .= '
            <div class="navGameServer" id="navGameServer_' . $get['id'] . '">
              <div style="width:100%;padding:10px 0;text-align:center"><img src="../inc/images/ajax_loading.gif" alt="" /></div>
              <script language="javascript" type="text/javascript">
                <!--
                  DZCP.initGameServer(' . $get['id'] . ');
                //-->
              </script>
            </div>
          ';
        }
    } else {
        $get = db("SELECT `id`,`ip`,`port`,`qport`,`status` FROM " . $db['server'] . " WHERE `navi` = '1' AND `id` = '" . (int)($serverID) . "'", false, true);
        if (!function_exists('server_query_' . $get['status']) && file_exists(basePath . '/inc/server_query/' . strtolower($get['status']) . '.php')) {
            include(basePath . '/inc/server_query/' . strtolower($get['status']) . '.php');
        }

        $CachedString = $cache->getItem('nav_server_' . $serverID);
        if (is_null($CachedString->get())) {
            $server = gs_normalise(@call_user_func('server_query_' . $get['status'], $get['ip'], $get['port'], $get['qport'], 'info'));
            $player_list = call_user_func('server_query_' . $get['status'], $get['ip'], $get['port'], $get['qport'], 'players');
            $CachedString->set(serialize(array('server' => $server, 'players' => $player_list)))->expiresAfter(config('cache_server'));
            $cache->save($CachedString);
            unset($server_cache);
        } else {
            $server_cache = unserialize($CachedString->get());
            $server = gs_normalise($server_cache['server']);
            $player_list = $server_cache['players'];
            unset($server_cache);
        }

        $server["mapname"] = preg_replace("/[^A-Za-z0-9 \&\_\-]/", "_", $server["mapname"]);
        $map_low = str_replace(' ', '_', strtolower($server["mapname"]));

        $server["gamemod"] = preg_replace("/[^A-Za-z0-9 \&\_\-]/", "_", $server["gamemod"]);

        $server["hostname"] = htmlentities($server["hostname"], ENT_QUOTES);
        $game_icon = file_exists(basePath . '/inc/images/gameicons/' . $get['status'] . '/' . $server['gamemod'] . '.gif')
            ? '<img src="../inc/images/gameicons/' . $get['status'] . '/' . $server['gamemod'] . '.gif" alt="" />' : '';

        if (!$server) {
            $image_map = "../inc/images/maps/no_map.gif";
            $server["hostname"] = "Unknown";
            $server["mapname"] = "unknown";
            $server["players"] = "0";
            $server["maxplayers"] = "0";
        } else {
            $server['gamemod'] = strtolower((empty($server['gamemod']) ? $get['status'] : $server['gamemod']));
            $image_map = "../inc/images/maps/" . $get['status'] . "/" . $server['gamemod'] . "/" . $map_low . ".jpg";

            if (!file_exists($image_map)) $image_map = "../inc/images/maps/no_map.gif";
        }

        $pwd_txt = '';
        $pwd_info = '';
        $pwd = '';
        if (!empty($get['pwd']) && permission("gs_showpw")) {
            $pwd = show(_server_pwd, array("pwd" => $get['pwd']));
            $pwd_info = "Passwort";
        }

        if (!empty($server_name_config[$server['gamemod']])) $server_name_short = $server_name_config[$server['gamemod']][1];

        $players = "";
        $count = 0;
        if (!empty($player_list)) {
            foreach ($player_list as $key => $player) {
                $players .= str_replace("'", '', htmlentities($player['name'])) . ', ';
                $count++;
            }
        }

        if ($count == 0)
            $players = 'no players';

        $servername = up(re(cut($server['hostname'], config('l_servernavi'), true, false)));
        $servernameout = (!empty($servername)) ? $servername : "no name available";
        $info = 'onmouseover="DZCP.showInfo(\'' . $servernameout . '\', \'IP/Port;' . $pwd_info . ';Game;Map;Players Online;On the Game\', \'' . $get['ip'] . ':' . $get['port'] . ';' . $pwd_txt . ';' . up(re($game_icon)) . '' . $server_name_short . ';' . (empty($server['mapname']) ? '-' : re($server['mapname'])) . ';' . $server['players'] . ' / ' . $server['maxplayers'] . ';' . $players . '\')" onmouseout="DZCP.hideInfo()"';

        $servernavi .= show("menu/server", array("host" => re(cut($server['hostname'], config('l_servernavi'), true, false)),
            "ip" => $get['ip'],
            "map" => (empty($server['mapname']) ? '-' : re($server['mapname'])),
            "mappic" => $image_map,
            "data_gamemod" => $server_name_short,
            "icon" => $game_icon,
            "pwd" => $pwd,
            "port" => $get['port'],
            "aktplayers" => $server['players'],
            "info" => $info,
            "maxplayers" => $server['maxplayers']));
    }

    return empty($servernavi) ? '<div style="text-align:center;margin:15px 5px 0px 15px">' . _no_server_navi . '</div>' : (empty($st) ? '<table class="navContent" cellspacing="0">' . $servernavi . '</table>' : $servernavi);
}