<?php
/**
 * DZCP - deV!L`z ClanPortal 1.6 Final
 * http://www.dzcp.de
 */

if (defined('_Forum')) {
    if ($do == "anonym") {
        $get = db("SELECT * FROM `" . $db['f_threads'] . "` WHERE `id` = " . (int)($_GET['id']) . ";", false, true);
        if (($get['t_reg'] >= 1 && $get['t_reg'] == $userid) || permission('forum')) {
            if (!db("SELECT `id` FROM `" . $db['f_posts'] . "` WHERE `sid` = " . $get['id'] . ";", true)) {
                //Delete Thread, no Posts!
                db("DELETE FROM `" . $db['f_threads'] . "` WHERE `id` = " . $get['id'] . ";");
                db("DELETE FROM `" . $db['f_abo'] . "` WHERE `fid` = " . $get['id'] . ";");
            } else {
                //Suche Zitate und anonymisieren
                $qry = db("SELECT `text`,`id` FROM `" . $db['f_posts'] . "` WHERE `sid` = " . $get['id'] . ";");
                while ($get_post = _fetch($qry)) {
                    $text = re($get_post['text']);
                    $text = str_replace(array(re(data('nick', $get['t_reg'])), utor($get['t_reg'])), __dsgvo_deleted_user, $text);
                    db("UPDATE `" . $db['f_posts'] . "` SET `text` = '" . up($text) . "' WHERE `id` = " . $get_post['id'] . ";");
                }

                //Anonym User for Thread
                db("UPDATE `" . $db['f_threads'] . "` SET " .
                    "`t_nick` = '', " .
                    "`t_reg` = 0, " .
                    "`t_email` = '', " .
                    "`t_text` = '', " .
                    "`edited` = '', " .
                    "`t_hp` = '', " .
                    "`ip` = '', " .
                    "`dsgvo` = 1, " .
                    "WHERE `id` = " . $get['id'] . ";");
            }
        }

        header("Location: ../forum/?action=showthread&id=" . (int)($_GET['id']));
        exit();
    } else if ($do == "edit") {
        $get = db("SELECT * FROM " . $db['f_threads'] . " WHERE id = '" . (int)($_GET['id']) . "'", false, true);
        if ($get['t_reg'] == $userid || permission("forum")) {
            if (permission("forum")) {
                $sticky = $get['sticky'] ? 'checked="checked"' : "";
                $global = $get['global'] ? 'checked="checked"' : "";
                $admin = show($dir . "/form_admin", array("adminhead" => _forum_admin_head,
                    "addsticky" => _forum_admin_addsticky,
                    "sticky" => $sticky,
                    "addglobal" => _forum_admin_addglobal,
                    "global" => $global));
            }

            if ($get['t_reg'] != 0) {
                $form = show("page/editor_regged", array("nick" => autor($get['t_reg']),
                    "von" => _autor));

            } else {
                $form = show("page/editor_notregged", array("nickhead" => _nick,
                    "emailhead" => _email,
                    "hphead" => _hp));
            }

            $getv = db("SELECT * FROM " . $db['votes'] . " WHERE id = '" . $get['vote'] . "'", false, true);
            $fget = db("SELECT s1.intern,s2.id FROM " . $db['f_kats'] . " AS s1
                    LEFT JOIN " . $db['f_skats'] . " AS s2 ON s2.`sid` = s1.id
                    WHERE s2.`id` = '" . (int)($get['kid']) . "'", false, true);

            $intern = '';
            $intern_kat = '';
            $isclosed = '';
            $display = '';
            $toggle = 'collapse';
            $internVisible = '';
            if ($getv['intern'])
                $intern = 'checked="checked"';

            if ($fget['intern']) {
                $intern = 'checked="checked"';
                $internVisible = 'style="display:none"';
            }

            if ($getv['closed']) {
                $isclosed = 'checked="checked"';
                $display = 'none';
                $toggle = 'expand';
            }

            if (empty($get['vote'])) {
                $vote = show($dir . "/form_vote", array("head" => _votes_admin_head,
                    "value" => _button_value_add,
                    "what" => "&amp;do=add",
                    "closed" => "",
                    "question1" => "",
                    "a1" => "",
                    "a2" => "",
                    "a3" => "",
                    "a4" => "",
                    "a5" => "",
                    "a6" => "",
                    "a7" => "",
                    "error" => "",
                    "br1" => "<!--",
                    "br2" => "-->",
                    "display" => "none",
                    "a8" => "",
                    "a9" => "",
                    "a10" => "",
                    "intern" => "",
                    "tgl" => "expand",
                    "vote_del" => _forum_vote_del,
                    "interna" => _votes_admin_intern,
                    "question" => _votes_admin_question,
                    "answer" => _votes_admin_answer));
            } elseif (!empty($get['vote'])) {
                $vote = show($dir . "/form_vote", array("head" => _votes_admin_edit_head,
                    "id" => $getv['id'],
                    "what" => '',
                    "value" => _button_value_edit,
                    "br1" => "",
                    "br2" => "",
                    "tgl" => $toggle,
                    "display" => $display,
                    "question1" => re($getv['titel']),
                    "a1" => voteanswer("a1", $getv['id']),
                    "a2" => voteanswer("a2", $getv['id']),
                    "a3" => voteanswer("a3", $getv['id']),
                    "a4" => voteanswer("a4", $getv['id']),
                    "a5" => voteanswer("a5", $getv['id']),
                    "a6" => voteanswer("a6", $getv['id']),
                    "a7" => voteanswer("a7", $getv['id']),
                    "error" => "",
                    "a8" => voteanswer("a8", $getv['id']),
                    "a9" => voteanswer("a9", $getv['id']),
                    "a10" => voteanswer("a10", $getv['id']),
                    'intern_kat' => $internVisible,
                    "intern" => $intern,
                    "isclosed" => $isclosed,
                    "vote_del" => _forum_vote_del,
                    "closed" => _votes_admin_closed,
                    "interna" => _votes_admin_intern,
                    "question" => _votes_admin_question,
                    "answer" => _votes_admin_answer));

            }
            $dowhat = show(_forum_dowhat_edit_thread, array("id" => $_GET['id']));
            $index = show($dir . "/thread", array("titel" => _forum_edit_thread_head,
                "nickhead" => _nick,
                "topichead" => _forum_topic,
                "subtopichead" => _forum_subtopic,
                "emailhead" => _email,
                "form" => $form,
                "reg" => $get['t_reg'],
                "id" => "",
                "security" => _register_confirm,
                "preview" => _preview,
                "ip" => _iplog_info,
                "bbcodehead" => _bbcode,
                "eintraghead" => _eintrag,
                "what" => _button_value_edit,
                "dowhat" => $dowhat,
                "error" => "",
                "posttopic" => re($get['topic']),
                "postsubtopic" => re($get['subtopic']),
                "postnick" => re($get['t_nick']),
                "postemail" => $get['t_email'],
                "posthp" => $get['t_hp'],
                "admin" => $admin,
                "vote" => $vote,
                "posteintrag" => bbcode(re($get['t_text']), false, true)));
        } else {
            $index = error(_error_wrong_permissions, 1);
        }
    } elseif ($do == "editthread") {
        $qry = db("SELECT * FROM " . $db['f_threads'] . "
               WHERE id = '" . (int)($_GET['id']) . "'");
        $get = _fetch($qry);

        if ($get['t_reg'] == $userid || permission("forum")) {
            if ($get['t_reg'] != 0 || permission('forum')) {
                $toCheck = empty($_POST['eintrag']);
            } else {
                $toCheck = empty($_POST['topic']) || empty($_POST['nick']) || empty($_POST['email']) || empty($_POST['eintrag']) || $_POST['secure'] != $_SESSION['sec_' . $dir] || empty($_SESSION['sec_' . $dir]);
            }

            if ($toCheck) {
                if ($get['t_reg'] != 0) {
                    if (empty($_POST['eintrag'])) $error = _empty_eintrag;
                    $form = show("page/editor_regged", array("nick" => autor($get['t_reg']),
                        "von" => _autor));

                } else {
                    if (($_POST['secure'] != $_SESSION['sec_' . $dir]) || empty($_SESSION['sec_' . $dir])) $error = _error_invalid_regcode;
                    elseif (empty($_POST['topic'])) $error = _empty_topic;
                    elseif (empty($_POST['nick'])) $error = _empty_nick;
                    elseif (empty($_POST['email'])) $error = _empty_email;
                    elseif (!check_email(re($_POST['email'], true))) $error = _error_invalid_email;
                    elseif (empty($_POST['eintrag'])) $error = _empty_eintrag;

                    $form = show("page/editor_notregged", array("nickhead" => _nick,
                        "emailhead" => _email,
                        "hphead" => _hp));
                }

                $error = show("errors/errortable", array("error" => $error));

                if (permission("forum")) {
                    if (isset($_POST['sticky'])) $sticky = "checked";
                    if (isset($_POST['global'])) $global = "checked";

                    $admin = show($dir . "/form_admin", array("adminhead" => _forum_admin_head,
                        "addsticky" => _forum_admin_addsticky,
                        "sticky" => $sticky,
                        "addglobal" => _forum_admin_addglobal,
                        "global" => $global));
                }
                $qryv = db("SELECT * FROM " . $db['votes'] . " WHERE id = '" . $get['vote'] . "'");
                $getv = _fetch($qryv);

                $fget = _fetch(db("SELECT s1.intern,s2.id FROM " . $db['f_kats'] . " AS s1
                         LEFT JOIN " . $db['f_skats'] . " AS s2 ON s2.`sid` = s1.id
                         WHERE s2.`id` = '" . (int)($_GET['kid']) . "'"));

                if ($_POST['intern']) $intern = 'checked="checked"';
                $intern = '';
                $intern_kat = '';
                $internVisible = '';
                if ($fget['intern'] == "1") {
                    $intern = 'checked="checked"';
                    $internVisible = 'style="display:none"';
                };
                if ($_POST['closed']) $closed = 'checked="checked"';

                if (empty($_POST['question'])) $display = "none";
                $display = "";

                $vote = show($dir . "/form_vote", array("head" => _votes_admin_head,
                    "value" => _button_value_add,
                    "what" => "&amp;do=add",
                    "question1" => re($_POST['question']),
                    "a1" => $_POST['a1'],
                    "closed" => $closed,
                    "tgl" => "expand",
                    "br1" => "<!--",
                    "br2" => "-->",
                    "display" => $display,
                    "a2" => $_POST['a2'],
                    "a3" => $_POST['a3'],
                    "a4" => $_POST['a4'],
                    "a5" => $_POST['a5'],
                    "a6" => $_POST['a6'],
                    "a7" => $_POST['a7'],
                    "error" => $error,
                    "a8" => $_POST['a8'],
                    "a9" => $_POST['a9'],
                    "a10" => $_POST['a10'],
                    'intern_kat' => $internVisible,
                    "intern" => $intern,
                    "vote_del" => _forum_vote_del,
                    "interna" => _votes_admin_intern,
                    "question" => _votes_admin_question,
                    "answer" => _votes_admin_answer));

                $dowhat = show(_forum_dowhat_edit_thread, array("id" => $_GET['id']));
                $index = show($dir . "/thread", array("titel" => _forum_edit_thread_head,
                    "nickhead" => _nick,
                    "subtopichead" => _forum_subtopic,
                    "topichead" => _forum_topic,
                    "ip" => _iplog_info,
                    "form" => $form,
                    "bbcodehead" => _bbcode,
                    "reg" => $_POST['reg'],
                    "preview" => _preview,
                    "emailhead" => _email,
                    "id" => "",
                    "security" => _register_confirm,
                    "what" => _button_value_edit,
                    "dowhat" => $dowhat,
                    "posthp" => re($_POST['hp']),
                    "postemail" => re($_POST['email']),
                    "postnick" => re($_POST['nick']),
                    "posteintrag" => re_bbcode(re($_POST['eintrag'], true)),
                    "posttopic" => re($_POST['topic']),
                    "postsubtopic" => re($_POST['subtopic']),
                    "error" => $error,
                    "admin" => $admin,
                    "vote" => $vote,
                    "eintraghead" => _eintrag));
            } else {
                $qryt = db("SELECT * FROM " . $db['f_threads'] . "
                    WHERE id = '" . (int)($_GET['id']) . "'");
                $gett = _fetch($qryt);
                if (!empty($gett['vote'])) {
                    $qryv = db("SELECT * FROM " . $db['vote_results'] . "
                   WHERE vid = '" . $gett['vote'] . "'");
                    $getv = _fetch($qryv);

                    $vid = $gett['vote'];

                    $upd = db("UPDATE " . $db['votes'] . "
                   SET `titel`  = '" . up($_POST['question']) . "',
                       `intern` = '" . ((int)$_POST['intern']) . "',
                       `closed` = '" . ((int)$_POST['closed']) . "'
                   WHERE id = '" . $gett['vote'] . "'");

                    $upd1 = db("UPDATE " . $db['vote_results'] . "
                    SET `sel` = '" . up($_POST['a1']) . "'
                    WHERE what = 'a1'
                    AND vid = '" . $gett['vote'] . "'");

                    $upd2 = db("UPDATE " . $db['vote_results'] . "
                    SET `sel` = '" . up($_POST['a2']) . "'
                    WHERE what = 'a2'
                    AND vid = '" . $gett['vote'] . "'");

                    for ($i = 3; $i <= 10; $i++) {
                        if (!empty($_POST['a' . $i . ''])) {
                            if (cnt($db['vote_results'], " WHERE vid = '" . $gett['vote'] . "' AND what = 'a" . $i . "'") != 0) {
                                $upd = db("UPDATE " . $db['vote_results'] . "
                         SET `sel` = '" . up($_POST['a' . $i . '']) . "'
                         WHERE what = 'a" . $i . "'
                         AND vid = '" . $gett['vote'] . "'");
                            } else {
                                $ins = db("INSERT INTO " . $db['vote_results'] . "
                         SET `vid` = '" . $gett['vote'] . "',
                             `what` = 'a" . $i . "',
                             `sel` = '" . up($_POST['a' . $i . '']) . "'");
                            }
                        }

                        if (cnt($db['vote_results'], " WHERE vid = '" . $gett['vote'] . "' AND what = 'a" . $i . "'") != 0 && empty($_POST['a' . $i . ''])) {
                            $del = db("DELETE FROM " . $db['vote_results'] . "
                       WHERE vid = '" . $gett['vote'] . "'
                       AND what = 'a" . $i . "'");
                        }
                    }
                } elseif (empty($gett['vote']) && !empty($_POST['question'])) {
                    $qry = db("INSERT INTO " . $db['votes'] . "
                     SET `datum`  = '" . time() . "',
                         `titel`  = '" . up($_POST['question']) . "',
                         `intern` = '" . ((int)$_POST['intern']) . "',
                                     `forum`  = 1,
                         `von`    = '" . ((int)$userid) . "'");

                    $vid = mysqli_insert_id($mysql);

                    $qry = db("INSERT INTO " . $db['vote_results'] . "
                    SET `vid`   = '" . ((int)$vid) . "',
                        `what`  = 'a1',
                        `sel`   = '" . up($_POST['a1']) . "'");

                    $qry = db("INSERT INTO " . $db['vote_results'] . "
                     SET `vid`  = '" . ((int)$vid) . "',
                         `what` = 'a2',
                         `sel`  = '" . up($_POST['a2']) . "'");

                    if (!empty($_POST['a3'])) {
                        $qry = db("INSERT INTO " . $db['vote_results'] . "
                       SET `vid`  = '" . ((int)$vid) . "',
                           `what` = 'a3',
                           `sel`  = '" . up($_POST['a3']) . "'");
                    }
                    if (!empty($_POST['a4'])) {
                        $qry = db("INSERT INTO " . $db['vote_results'] . "
                       SET `vid`  = '" . ((int)$vid) . "',
                           `what` = 'a4',
                           `sel`  = '" . up($_POST['a4']) . "'");
                    }
                    if (!empty($_POST['a5'])) {
                        $qry = db("INSERT INTO " . $db['vote_results'] . "
                       SET `vid`  = '" . ((int)$vid) . "',
                           `what` = 'a5',
                           `sel`  = '" . up($_POST['a5']) . "'");
                    }
                    if (!empty($_POST['a6'])) {
                        $qry = db("INSERT INTO " . $db['vote_results'] . "
                       SET `vid`  = '" . ((int)$vid) . "',
                           `what` = 'a6',
                           `sel`  = '" . up($_POST['a6']) . "'");
                    }
                    if (!empty($_POST['a7'])) {
                        $qry = db("INSERT INTO " . $db['vote_results'] . "
                       SET `vid`  = '" . ((int)$vid) . "',
                           `what` = 'a7',
                           `sel`  = '" . up($_POST['a7']) . "'");
                    }
                    if (!empty($_POST['a8'])) {
                        $qry = db("INSERT INTO " . $db['vote_results'] . "
                       SET `vid`  = '" . ((int)$vid) . "',
                           `what` = 'a8',
                           `sel`  = '" . up($_POST['a8']) . "'");
                    }
                    if (!empty($_POST['a9'])) {
                        $qry = db("INSERT INTO " . $db['vote_results'] . "
                       SET `vid`  = '" . ((int)$vid) . "',
                           `what` = 'a9',
                           `sel`  = '" . up($_POST['a9']) . "'");
                    }
                    if (!empty($_POST['a10'])) {
                        $qry = db("INSERT INTO " . $db['vote_results'] . "
                       SET `vid`  = '" . ((int)$vid) . "',
                           `what` = 'a10',
                           `sel`  = '" . up($_POST['a10']) . "'");
                    }
                } else {
                    $vid = "";
                }

                if ($_POST['vote_del'] == 1) {
                    $qry = db("DELETE FROM " . $db['votes'] . "
                   WHERE id = '" . $gett['vote'] . "'");

                    $qry = db("DELETE FROM " . $db['vote_results'] . "
                   WHERE vid = '" . $gett['vote'] . "'");

                    setIpcheck("vid_" . $gett['vote'], false);
                    $vid = "";
                }

                $editedby = show(_edited_by, array("autor" => autor($userid), "time" => date("d.m.Y H:i", time()) . _uhr));

                db("UPDATE " . $db['f_threads'] . "
                             SET `topic`    = '" . up($_POST['topic']) . "',
                       `subtopic` = '" . up($_POST['subtopic']) . "',
                       `t_nick`   = '" . up($_POST['nick']) . "',
                       `t_email`  = '" . up($_POST['email']) . "',
                       `t_hp`     = '" . links(re($_POST['hp'], true)) . "',
                       `t_text`   = '" . up($_POST['eintrag']) . "',
                       `sticky`   = '" . ((int)$_POST['sticky']) . "',
                       `global`   = '" . ((int)$_POST['global']) . "',
                                            `vote`     = '" . $vid . "',
                       `edited`   = '" . addslashes($editedby) . "'
                   WHERE id = '" . (int)($_GET['id']) . "'");

                $checkabo = db("SELECT s1.`user`,s1.`fid`,s2.`nick`,s2.`id`,s2.`email` FROM `" . $db['f_abo'] . "` AS `s1` " .
                    "LEFT JOIN " . $db['users'] . " AS `s2` ON s2.`id` = s1.`user` " .
                    "WHERE s1.`fid` = " . ((int)$_GET['id']) . " AND s2.`dsgvo_lock` != 1;");
                while ($getabo = _fetch($checkabo)) {
                    if ($userid != $getabo['user']) {
                        $topic = db("SELECT topic FROM " . $db['f_threads'] . " WHERE id = '" . (int)($_GET['id']) . "'");
                        $gettopic = _fetch($topic);

                        $subj = show(re(settings('eml_fabo_tedit_subj')), array("titel" => $title));

                        $message = show(bbcode_email(settings('eml_fabo_tedit')), array("nick" => re($getabo['nick']),
                            "postuser" => fabo_autor($userid),
                            "topic" => $gettopic['topic'],
                            "titel" => $title,
                            "domain" => $httphost,
                            "id" => (int)($_GET['id']),
                            "entrys" => "1",
                            "page" => "1",
                            "text" => bbcode($_POST['eintrag']),
                            "clan" => settings('clanname')));

                        sendMail(re($getabo['email']), $subj, $message);
                    }
                }

                $index = info(_forum_editthread_successful, "?action=showthread&amp;id=" . $gett['id'] . "");

            }
        } else $index = error(_error_wrong_permissions, 1);
    } elseif ($do == "add") {
        if (settings("reg_forum") && !$chkMe) {
            $index = error(_error_unregistered, 1);
        } else if (HasDSGVO()) {
            if (!ipcheck("fid(" . $_GET['kid'] . ")", config('f_forum'))) {
                if (permission("forum")) {
                    $admin = show($dir . "/form_admin", array("adminhead" => _forum_admin_head,
                        "addsticky" => _forum_admin_addsticky,
                        "sticky" => "",
                        "addglobal" => _forum_admin_addglobal,
                        "global" => ""));
                } else {
                    $admin = "";
                }

                $internVisible = '';
                $fget = _fetch(db("SELECT s1.intern,s2.id FROM " . $db['f_kats'] . " AS s1
                       LEFT JOIN " . $db['f_skats'] . " AS s2 ON s2.`sid` = s1.id
                       WHERE s2.`id` = '" . (int)($_GET['kid']) . "'"));
                $intern = '';
                $intern_kat = '';
                $internVisible = '';
                if ($fget['intern'] == "1") {
                    $intern = 'checked="checked"';
                    $internVisible = 'style="display:none"';
                };

                if ($userid >= 1) {
                    $form = show("page/editor_regged", array("nick" => autor($userid),
                        "von" => _autor));
                } else {
                    $form = show("page/editor_notregged", array("nickhead" => _nick,
                        "emailhead" => _email,
                        "hphead" => _hp));
                }

                $vote = show($dir . "/form_vote", array("head" => _votes_admin_head,
                    "value" => _button_value_add,
                    "what" => "&amp;do=add",
                    "closed" => "",
                    "question1" => "",
                    "tgl" => "expand",
                    "a1" => "",
                    "a2" => "",
                    "a3" => "",
                    "a4" => "",
                    "a5" => "",
                    "a6" => "",
                    "a7" => "",
                    "error" => "",
                    "br1" => "<!--",
                    "br2" => "-->",
                    "display" => "none",
                    "a8" => "",
                    "a9" => "",
                    "a10" => "",
                    'intern_kat' => $internVisible,
                    "intern" => $intern,
                    "vote_del" => _forum_vote_del,
                    "interna" => _votes_admin_intern,
                    "question" => _votes_admin_question,
                    "answer" => _votes_admin_answer));

                $dowhat = show(_forum_dowhat_add_thread, array("kid" => $_GET['kid']));

                $index = show($dir . "/thread", array("titel" => _forum_new_thread_head,
                    "nickhead" => _nick,
                    "topichead" => _forum_topic,
                    "subtopichead" => _forum_subtopic,
                    "emailhead" => _email,
                    "id" => $_GET['kid'],
                    "bbcodehead" => _bbcode,
                    "reg" => "",
                    "security" => _register_confirm,
                    "ip" => _iplog_info,
                    "preview" => _preview,
                    "form" => $form,
                    "eintraghead" => _eintrag,
                    "what" => _button_value_add,
                    "dowhat" => $dowhat,
                    "error" => "",
                    "posttopic" => "",
                    "postsubtopic" => "",
                    "posthp" => "",
                    "postnick" => "",
                    "postemail" => "",
                    "admin" => $admin,
                    "vote" => $vote,
                    "posteintrag" => ""));
            } else {
                $index = error(show(_error_flood_post, array("sek" => config('f_forum'))), 1);
            }
        }
    } elseif ($do == "addthread") {
        if (_rows(db("SELECT id FROM " . $db['f_skats'] . " WHERE id = '" . (int)($_GET['kid']) . "'")) == 0) {
            $index = error(_id_dont_exist, 1);
        } else {
            if ((settings("reg_forum") && !$chkMe) || !HasDSGVO()) {
                $index = error(_error_have_to_be_logged, 1);
            } else {
                if ($userid >= 1)
                    $toCheck = empty($_POST['eintrag']) || empty($_POST['topic']);
                else
                    $toCheck = empty($_POST['topic']) || empty($_POST['nick']) || empty($_POST['email']) || empty($_POST['eintrag']) || !check_email($_POST['email']) || $_POST['secure'] != $_SESSION['sec_' . $dir] || empty($_SESSION['sec_' . $dir]);
                if ($toCheck) {
                    if ($userid >= 1) {
                        if (empty($_POST['eintrag'])) $error = _empty_eintrag;
                        elseif (empty($_POST['topic'])) $error = _empty_topic;
                    } else {
                        if (($_POST['secure'] != $_SESSION['sec_' . $dir]) || empty($_SESSION['sec_' . $dir])) $error = _error_invalid_regcode;
                        elseif (empty($_POST['topic'])) $error = _empty_topic;
                        elseif (empty($_POST['nick'])) $error = _empty_nick;
                        elseif (empty($_POST['email'])) $error = _empty_email;
                        elseif (!check_email($_POST['email'])) $error = _error_invalid_email;
                        elseif (empty($_POST['eintrag'])) $error = _empty_eintrag;
                    }

                    $error = show("errors/errortable", array("error" => $error));

                    if (permission("forum")) {
                        if (isset($_POST['sticky'])) $sticky = "checked";
                        if (isset($_POST['global'])) $global = "checked";

                        $admin = show($dir . "/form_admin", array("adminhead" => _forum_admin_head,
                            "addsticky" => _forum_admin_addsticky,
                            "sticky" => $sticky,
                            "addglobal" => _forum_admin_addglobal,
                            "global" => $global));
                    } else {
                        $admin = "";
                    }

                    if ($userid >= 1) {
                        $form = show("page/editor_regged", array("nick" => autor($userid),
                            "von" => _autor));
                    } else {
                        $form = show("page/editor_notregged", array("nickhead" => _nick,
                            "emailhead" => _email,
                            "hphead" => _hp));
                    }

                    $fget = _fetch(db("SELECT s1.intern,s2.id FROM " . $db['f_kats'] . " AS s1
                                                 LEFT JOIN " . $db['f_skats'] . " AS s2 ON s2.`sid` = s1.id
                                                 WHERE s2.`id` = '" . (int)($_GET['kid']) . "'"));

                    if ($_POST['intern']) $intern = 'checked="checked"';
                    $intern = '';
                    $intern_kat = '';
                    $internVisible = '';
                    if ($fget['intern'] == 1) {
                        $intern = 'checked="checked"';
                        $internVisible = 'style="display:none"';
                    };
                    if ($_POST['closed']) $closed = 'checked="checked"';

                    if (!empty($_POST['question'])) $display = "";
                    $display = "none";

                    $vote = show($dir . "/form_vote", array("head" => _votes_admin_head,
                        "value" => _button_value_add,
                        "what" => "&amp;do=add",
                        "question1" => re($_POST['question']),
                        "a1" => $_POST['a1'],
                        "closed" => $closed,
                        "br1" => "<!--",
                        "br2" => "-->",
                        "tgl" => "expand",
                        "display" => $display,
                        "a2" => $_POST['a2'],
                        "a3" => $_POST['a3'],
                        "a4" => $_POST['a4'],
                        "a5" => $_POST['a5'],
                        "a6" => $_POST['a6'],
                        "a7" => $_POST['a7'],
                        "error" => $error,
                        "a8" => $_POST['a8'],
                        "a9" => $_POST['a9'],
                        "a10" => $_POST['a10'],
                        "vote_del" => _forum_vote_del,
                        'intern_kat' => $internVisible,
                        "intern" => $intern,
                        "interna" => _votes_admin_intern,
                        "question" => _votes_admin_question,
                        "answer" => _votes_admin_answer));

                    $dowhat = show(_forum_dowhat_add_thread, array("kid" => $_GET['kid']));
                    $index = show($dir . "/thread", array("titel" => _forum_new_thread_head,
                        "nickhead" => _nick,
                        "reg" => "",
                        "subtopichead" => _forum_subtopic,
                        "topichead" => _forum_topic,
                        "form" => $form,
                        "bbcodehead" => _bbcode,
                        "emailhead" => _email,
                        "id" => $_GET['kid'],
                        "security" => _register_confirm,
                        "what" => _button_value_add,
                        "preview" => _preview,
                        "dowhat" => $dowhat,
                        "posthp" => $_POST['hp'],
                        "postemail" => $_POST['email'],
                        "postnick" => re($_POST['nick']),
                        "ip" => _iplog_info,
                        "posteintrag" => re_bbcode(re($_POST['eintrag'], true)),
                        "posttopic" => re($_POST['topic']),
                        "postsubtopic" => re($_POST['subtopic']),
                        "error" => $error,
                        "admin" => $admin,
                        "vote" => $vote,
                        "eintraghead" => _eintrag));
                } else {
                    if (!empty($_POST['question'])) {
                        $fgetvote = _fetch(db("SELECT s1.intern,s2.id FROM " . $db['f_kats'] . " AS s1
                                                                     LEFT JOIN " . $db['f_skats'] . " AS s2 ON s2.`sid` = s1.id
                                                                     WHERE s2.`id` = '" . (int)($_GET['kid']) . "'"));

                        if ($fgetvote['intern'] == 1) $ivote = "`intern` = '1',";
                        else $ivote = "`intern` = '" . ((int)$_POST['intern']) . "',";

                        $qry = db("INSERT INTO " . $db['votes'] . "
                                             SET `datum`  = '" . time() . "',
                                                     `titel`  = '" . up($_POST['question']) . "',
                                                     " . $ivote . "
                                                     `forum`  = 1,
                                                     `von`    = '" . ((int)$userid) . "'");

                        $vid = mysqli_insert_id($mysql);

                        $qry = db("INSERT INTO " . $db['vote_results'] . "
                                            SET `vid`   = '" . ((int)$vid) . "',
                                                    `what`  = 'a1',
                                                    `sel`   = '" . up($_POST['a1']) . "'");

                        $qry = db("INSERT INTO " . $db['vote_results'] . "
                                             SET `vid`  = '" . ((int)$vid) . "',
                                                     `what` = 'a2',
                                                     `sel`  = '" . up($_POST['a2']) . "'");

                        if (!empty($_POST['a3'])) {
                            $qry = db("INSERT INTO " . $db['vote_results'] . "
                                                 SET `vid`  = '" . ((int)$vid) . "',
                                                         `what` = 'a3',
                                                         `sel`  = '" . up($_POST['a3']) . "'");
                        }
                        if (!empty($_POST['a4'])) {
                            $qry = db("INSERT INTO " . $db['vote_results'] . "
                                                 SET `vid`  = '" . ((int)$vid) . "',
                                                         `what` = 'a4',
                                                         `sel`  = '" . up($_POST['a4']) . "'");
                        }
                        if (!empty($_POST['a5'])) {
                            $qry = db("INSERT INTO " . $db['vote_results'] . "
                                                 SET `vid`  = '" . ((int)$vid) . "',
                                                         `what` = 'a5',
                                                         `sel`  = '" . up($_POST['a5']) . "'");
                        }
                        if (!empty($_POST['a6'])) {
                            $qry = db("INSERT INTO " . $db['vote_results'] . "
                                                 SET `vid`  = '" . ((int)$vid) . "',
                                                         `what` = 'a6',
                                                         `sel`  = '" . up($_POST['a6']) . "'");
                        }
                        if (!empty($_POST['a7'])) {
                            $qry = db("INSERT INTO " . $db['vote_results'] . "
                                                 SET `vid`  = '" . ((int)$vid) . "',
                                                         `what` = 'a7',
                                                         `sel`  = '" . up($_POST['a7']) . "'");
                        }
                        if (!empty($_POST['a8'])) {
                            $qry = db("INSERT INTO " . $db['vote_results'] . "
                                                 SET `vid`  = '" . ((int)$vid) . "',
                                                         `what` = 'a8',
                                                         `sel`  = '" . up($_POST['a8']) . "'");
                        }
                        if (!empty($_POST['a9'])) {
                            $qry = db("INSERT INTO " . $db['vote_results'] . "
                                                 SET `vid`  = '" . ((int)$vid) . "',
                                                         `what` = 'a9',
                                                         `sel`  = '" . up($_POST['a9']) . "'");
                        }
                        if (!empty($_POST['a10'])) {
                            $qry = db("INSERT INTO " . $db['vote_results'] . "
                                                 SET `vid`  = '" . ((int)$vid) . "',
                                                         `what` = 'a10',
                                                         `sel`  = '" . up($_POST['a10']) . "'");
                        }
                    } else {
                        $vid = "";
                    }

                    $qry = db("INSERT INTO " . $db['f_threads'] . "
                                 SET     `kid`      = '" . ((int)$_GET['kid']) . "',
                                                `t_date`   = '" . time() . "',
                                                `topic`    = '" . up($_POST['topic']) . "',
                                                `subtopic` = '" . up($_POST['subtopic']) . "',
                                                `t_nick`   = '" . up($_POST['nick']) . "',
                                                `t_email`  = '" . up($_POST['email']) . "',
                                                `t_hp`     = '" . up(links(re($_POST['hp'], true))) . "',
                                                `t_reg`    = '" . ((int)$userid) . "',
                                                `t_text`   = '" . up($_POST['eintrag']) . "',
                                                `sticky`   = '" . ((int)$_POST['sticky']) . "',
                                                `global`   = '" . ((int)$_POST['global']) . "',
                                                `ip`       = '" . $userip . "',
                                                `lp`       = '" . time() . "',
                                                `vote`     = '" . $vid . "',
                                                `first`    = '1'");
                    $thisFID = mysqli_insert_id($mysql);
                    setIpcheck("fid(" . $_GET['kid'] . ")");

                    $update = db("UPDATE " . $db['userstats'] . "
                                            SET `forumposts` = forumposts+1
                                            WHERE `user`       = '" . $userid . "'");

                    $index = info(_forum_newthread_successful, "?action=showthread&amp;id=" . $thisFID . "#p1");
                }
            }
        }
    }
}