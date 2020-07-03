<?php
/**
* Erweiterte Jabber-Integration für MyBB
* Webseite: http://www.sebijk.com
* (c) 2012 Home of the Sebijk.com
* Lizenz: GPL
**/
/***************************************************************************
 *   ProfileJabber Copyright (C) 2008 by Ingo Malchow                      *
 *   ingomalchow@googlemail.com                                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 *   or see <http://www.gnu.org/licenses/>                                 *
 ***************************************************************************/

if(!defined("IN_MYBB"))
{
  die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/** Hooks einbinden **/
$plugins->add_hook("datahandler_user_update", "jabber_profileupdate");
$plugins->add_hook("misc_start", "jabber_imcenter");
//$plugins->add_hook("pre_output_page", "enhancedjabber");

/** Infos über enhancedjabber Abfrage **/
function jabber_info()
{
	return array(
		"name"			=> "Erweiterte Jabber-Integration",
		"description"	=> "Jabber IM-Feld für das MyBB und erweitert das MyBB mit Jabberfunktionen",
		"website"		=> "http://www.sebijk.com",
		"author"		=> "Home of the Sebijk.com",
		"authorsite"	=> "http://www.sebijk.com",
		"version"		=> "1.0.0 Beta 1",
		"guid" 			=> "",
    "compatibility" => "16*" 
	);
}

function jabber_install()
{
  global $db, $mybb, $lang;

  if(!$db->field_exists('jabber', "users")) $db->query("ALTER TABLE ".TABLE_PREFIX."users ADD jabber VARCHAR(200) NOT NULL AFTER msn");

  $db->write_query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN(
                  'jabber_hostnamejabber_jid',
                  'jabber_port',
                  'jabber_password',
                  'jabber_newpostnotification',
                  'jabber_canguestsendmessage',
                  'jabber_show_guest'
                  )");
  $db->delete_query("settinggroups", "name = 'jabber_settings'");
                
                
  $query = $db->simple_select("settinggroups", "COUNT(*) as rows");
  $rows = $db->fetch_field($query, "rows");

  $jabber_group = array(
      "gid"			=> "NULL",
      "name"			=> "jabber_settings",
      "title"			=> "Jabber-Optionen",
      "description"	=> "Einstellungen für die erweiterte Jabber-Integration",
      "disporder" => $rows+1,
      "isdefault"		=> "no",
    );
	
    $db->insert_query("settinggroups", $jabber_group);
    $gid = $db->insert_id();
	
	
    $jabber_setting_0 = array(
    "sid"			=> "NULL",
    "name"			=> "jabber_hostname",
    "title"			=> "Hostname",
    "description"	=> "Bitte geben Sie hier den Hostnamen an:",
		"optionscode"	=> "text",
		"value"			=> 'localhost',
		"disporder" => 0,
		"gid"			=> intval($gid),
    );
	
	$jabber_setting_1 = array(
		"sid"			=> "NULL",
		"name"			=> "jabber_jid",
		"title"			=> "Bildformat",
		"description"	=> "Bitte geben Sie hier Ihre vollständige Jabber-ID ein (bsp. username@example.com):",
		"optionscode"	=> "text",
		"value"			=> "username@example.com",
		"disporder" => 1,
		"gid"			=> intval($gid),
    );
	
	$jabber_setting_2 = array(
		"sid"			=> "NULL",
		"name"			=> "jabber_password",
		"title"			=> "Jabber-Kennwort",
		"description"	=> "Hier geben Sie Ihr Kennwort zu Ihre Jabber-ID ein:",
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> 2,
		"gid"			=> intval($gid),
    );
	
	$jabber_setting_3 = array(
		"sid"			=> "NULL",
		"name"			=> "jabber_port",
		"title"			=> "Portnummer",
		"description"	=> "Bitte geben Sie hier die Portnummer ein. Standardm&auml;&szlig;ig ist 5222.",
		"optionscode"	=> "text",
		"value"			=> "5222",
		"disporder"		=> 3,
		"gid"			=> intval($gid),
    );
	
	$jabber_setting_4 = array(
		"sid"			=> "NULL",
		"name"			=> "jabber_newpostnotification",
		"title"			=> "Über neue Beiträge per Jabber benachrichtigen",
		"description"	=> "Sollen Benutzern über neue Beiträge per Jabber benachrichtigt werden? (Falls der Benutzer die Abbonierung aktiviert hat und auch eine Jabber-ID eingegeben hat).",
		"optionscode"	=> "onoff",
		"value"			=> "on",
		"disporder"		=> 4,
		"gid"			=> intval($gid),
    );
	
	$jabber_setting_5 = array(
		"sid"			=> "NULL",
		"name"			=> "jabber_canguestsendmessage",
		"title"			=> "Gäste erlauben, Jabber-Nachrichten zu senden",
		"description"	=> "Wenn Sie verhindern wollen, dass Gäste Jabber-Nachrichten senden können, müssen Sie diese Option deaktivieren. Diese Option setzt vorraus, dass Jabber-Nachrichten über das Forum senden aktiviert ist.",
		"optionscode"	=> "onoff",
		"value"			=> "off",
		"disporder"		=> 5,
		"gid"			=> intval($gid),
    );
	
	$jabber_setting_6 = array(
		"sid"			=> "NULL",
		"name"			=> "jabber_show_guest",
		"title"			=> "Jabber-ID für Gäste anzeigen",
		"description"	=> "Dürfen Gäste die Jabber-ID der Benutzer sehen?<br /><b>Hinweis: Die Jabber-ID werden Gäste generell nicht angezeigt, wenn Sie Benutzerprofile ansehen für Gäste ausgeschaltet haben.</b>",
		"optionscode"	=> "onoff",
		"value"			=> "off",
		"disporder"		=> 6,
		"gid"			=> intval($gid),
    );

    $db->insert_query("settings", $jabber_setting_0);
    $db->insert_query("settings", $jabber_setting_1);
    $db->insert_query("settings", $jabber_setting_2);
    $db->insert_query("settings", $jabber_setting_3);
    $db->insert_query("settings", $jabber_setting_4);
    $db->insert_query("settings", $jabber_setting_5);
    $db->insert_query("settings", $jabber_setting_6);
    rebuild_settings();
}

function jabber_is_installed()
{
	global $db;
	
	if($db->field_exists('jabber', "users"))
	{
		return true;
	}
	
	return false;
}

function jabber_uninstall()
{
global $db;  
	
	$db->write_query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN(
                  'jabber_hostnamejabber_jid',
                  'jabber_port',
                  'jabber_password',
                  'jabber_newpostnotification',
                  'jabber_canguestsendmessage',
                  'jabber_show_guest'
                  )");
  $db->delete_query("settinggroups", "name = 'jabber_settings'");
	rebuild_settings();
}
function jabber_activate()
{
global $db, $mybb;
  
  /* TODO: Language support? */
  require_once MYBB_ROOT."inc/adminfunctions_templates.php";

  /* Insert fields into usercp for editing */
  find_replace_templatesets('usercp_profile','#{\$user\[\'yahoo\'\]\}\" /\></td>#',
         '{$user[\'yahoo\']}" /></td>
</tr>
<tr>
<td><span class="smalltext">Jabber-ID:</span></td>
</tr>
<tr>
<td><input type="text" class="textbox" name="jabber" size="25" value="{$user[\'jabber\']}" /></td>
</tr>');

  /* Insert fields into member profile */
  find_replace_templatesets('member_profile','#{\$memprofile\[\'msn\'\]\}</a></td>#',
        '{$memprofile[\'msn\']}</a></td>
</tr>
<tr>
<td class="trow2"><strong>Jabber-ID:</strong></td>
<td class="trow2"><a href="javascript://Jabber" onclick="MyBB.popupWindow(\'misc.php?action=imcenter&amp;imtype=jabber&amp;uid={$uid}\', \'imcenter\', 450, 300);">{$memprofile[\'jabber\']}</td>
</tr>');
}

function jabber_deactivate()
{
  require_once MYBB_ROOT."inc/adminfunctions_templates.php";

  find_replace_templatesets('usercp_profile',
      preg_quote('#{$user[\'yahoo\']}" /></td>
</tr>
<tr>
<td><span class="smalltext">Jabber-ID:</span></td>
</tr>
<tr>
<td><input type="text" class="textbox" name="jabber" size="25" value="{$user[\'jabber\']}" /></td>
</tr>#'),
      '{$user[\'yahoo\']}" /></td>',0);

   find_replace_templatesets('member_profile',
        preg_quote('#{$memprofile[\'msn\']}</a></td>
</tr>
<tr>
<td class="trow2"><strong>Jabber-ID:</strong></td>
<td class="trow2"><a href="javascript://Jabber" onclick="MyBB.popupWindow(\'misc.php?action=imcenter&amp;imtype=jabber&amp;uid={$uid}\', \'imcenter\', 450, 300);">{$memprofile[\'jabber\']}</td>
</tr>#'),
      '{$memprofile[\'msn\']}</a></td>',0);
}

function jabber_profileupdate($jabber)
{
  global $mybb;

  if (isset($mybb->input['jabber']))
   {
      $jabber->user_update_data['jabber'] = $mybb->input['jabber'];
   }
}

function jabber_imcenter()
{
global $mybb, $uid, $lang, $templates;
$lang->load("jabber");
if($mybb->input['action'] == "imcenter" && $mybb->input['imtype'] == "jabber")
{
	$uid = $mybb->input['uid'];
	$user = get_user($uid);

	if(!$user['username'])
	{
		error($lang->error_invaliduser);
	}
	
	// build im navigation bar
	$navigationbar = $navsep = '';
	if($user['jabber'])
	{
		$navigationbar .= "$navsep<a href=\"misc.php?action=imcenter&amp;imtype=jabber&amp;uid=$uid\">$lang->jabber_im</a>";
	}
	//$lang->jabber_address_is = $lang->sprintf($lang->jabber_address_is, $user['username']);
	$lang->send_y_message = $lang->sprintf($lang->send_y_message, $user['username']);
	$lang->view_y_profile = $lang->sprintf($lang->view_y_profile, $user['username']);
	
	$imtemplate = "misc_imcenter_jabber";
	eval("\$imcenter = \"".$templates->get($imtemplate)."\";");
	output_page($imcenter);
}
}
?>