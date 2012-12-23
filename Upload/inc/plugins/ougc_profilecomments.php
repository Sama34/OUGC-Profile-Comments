<?php

/***************************************************************************
 *
 *   OUGC Profile Comments plugin (/inc/plugins/ougc_profilecomments.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://community.mybb.com/user-25096.html
 *
 *   Allow your users to comment in other users profiles.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// Run the hooks.
if(!defined('IN_ADMINCP'))
{
	global $templatelist, $mybb;

	if($mybb->settings['ougc_profilecomments_alert'])
	{
		$templatelist .= ', ougc_profilecomments_alert';
	}
	if(THIS_SCRIPT == 'member.php' && $mybb->input['action'] == 'profile')
	{
		$templatelist .= ', ougc_profilecomments_box, ougc_profilecomments_comment, ougc_profilecomments_comment_edited, ougc_profilecomments_comment_ip, ougc_profilecomments_comment_options, ougc_profilecomments_comment_options_delete, ougc_profilecomments_comment_options_edit, ougc_profilecomments_empty, ougc_profilecomments_error, ougc_profilecomments_form, ougc_profilecomments_options, multipage, multipage_breadcrumb, multipage_end, multipage_nextpage, multipage_page, multipage_page_current, multipage_page_link_current, multipage_prevpage, multipage_start';

		if(isset($mybb->input['conversation']))
		{
			$templatelist .= ', ougc_profilecomments_conversation';
		}
		if(isset($mybb->input['process']) && $mybb->input['process'] == 'delete')
		{
			$templatelist .= ', ougc_profilecomments_confirm';
		}
		if(isset($mybb->input['process']) && $mybb->input['process'] == 'edit')
		{
			$templatelist .= ', ougc_profilecomments_edit';
		}
	}
	elseif(THIS_SCRIPT == 'usercp.php' && $mybb->input['action'] == 'options')
	{
		$templatelist .= ', ougc_profilecomments_usercp';
	}
	$plugins->add_hook('global_end', 'ougc_profilecomments_global');
	$plugins->add_hook('member_profile_end', 'ougc_profilecomments_profile', 9);
	$plugins->add_hook('usercp_options_end', 'ougc_profilecomments_usercp');
	$plugins->add_hook('usercp_do_options_end', 'ougc_profilecomments_do_usercp');
	define('OUGC_PROFILECOMMENTS_USEMYBBURLS', 1);
}

// Necessary plugin information for the ACP plugin manager.
function ougc_profilecomments_info()
{
	global $lang;
	isset($lang->ougc_profilecomments) or $lang->load('ougc_profilecomments');

	return array(
		'name'			=> 'OUGC Profile Comments',
		'description'	=> $lang->ougc_profilecomments_d,
		'website'		=> 'http://mods.mybb.com/profile/25096',
		'author'		=> 'Omar Gonzalez',
		'authorsite'	=> 'http://community.mybb.com/user-25096.html',
		'version'		=> '1.0',
		'compatibility'	=> '16*',
		'guid' 			=> '',
	);
}

// Activate the plugin.
function ougc_profilecomments_activate()
{
	global $db;
	ougc_profilecomments_deactivate();

	require_once MYBB_ROOT.'inc/functions_profilecomments.php';
	ougc_profilecomments_add_setting('activate', 'onoff', 1, 1);
	ougc_profilecomments_add_setting('limit', 'text', 10, 2);
	ougc_profilecomments_add_setting('minlength', 'text', 10, 3);
	ougc_profilecomments_add_setting('maxlength', 'text', 100, 4);
	ougc_profilecomments_add_setting('flood', 'text', 60, 5);
	ougc_profilecomments_add_setting('alert', 'yesno', 1, 6);
	ougc_profilecomments_add_setting('ignored', 'onoff', 0, 7);
	ougc_profilecomments_add_setting('useajax', 'yesno', 0, 9);
	ougc_profilecomments_add_setting('davatar', 'text', 'images/avatars/athlon.gif', 10);
	ougc_profilecomments_add_setting('davatardim', 'text', '73|73', 11);
	ougc_profilecomments_add_setting('maxdim', 'text', '35x35', 12);
	ougc_profilecomments_add_setting('allowhtml', 'yesno', 0, 13);
	ougc_profilecomments_add_setting('allosmilies', 'yesno', 1, 14);
	ougc_profilecomments_add_setting('allowbbcode', 'yesno', 1, 15);
	ougc_profilecomments_add_setting('allowimgcode', 'yesno', 0, 16);
	ougc_profilecomments_add_setting('allowvideocode', 'yesno', 0, 17);
	ougc_profilecomments_add_setting('filterbadwords', 'yesno', 1, 18);
	ougc_profilecomments_add_setting('bbeditor', 'yesno', 1, 19);
	ougc_profilecomments_add_setting('cansend', 'text', '2,3,4,6', 20);
	ougc_profilecomments_add_setting('canreceive', 'text', '2,3,4,6', 21);
	ougc_profilecomments_add_setting('canedit', 'text', '2,3,4,6', 22);
	ougc_profilecomments_add_setting('candelete', 'text', '3,4,6', 23);
	ougc_profilecomments_add_setting('canmanage', 'text', '4', 24);
	rebuild_settings();

	// Add templates
	ougc_profilecomments_add_template('alert', '<p class="pm_alert">
	<a href="{$mybb->settings[\'bburl\']}/{$profilelink}">{$lang->ougc_profilecomments_global_alert}</a>
</p><br />');
	ougc_profilecomments_add_template('usercp', '<fieldset class="trow2">
<legend><strong>{$lang->ougc_profilecomments_profile}</strong></legend>
<table cellspacing="0" cellpadding="2">
<tr>
	<td valign="top" width="1"><input type="checkbox" class="checkbox" name="alertprofilecomments" id="alertprofilecomments" value="1"{$alertcheck} /></td><td><span class="smalltext"><label for="alertprofilecomments">{$lang->ougc_profilecomments_ucp_alert}</label></span></td>
</tr>
<tr>
	<td colspan="2" class="smalltext"><label for="privacyprofilecomments">{$lang->ougc_profilecomments_ucp_privacy}</label></td>
</tr>
<tr>
	<td colspan="2"><select name="privacyprofilecomments" id="privacyprofilecomments">
		<option value="0"{$privacy_0}>{$lang->ougc_profilecomments_ucp_privacy_0}</option>
		<option value="1"{$privacy_1}>{$lang->ougc_profilecomments_ucp_privacy_1}</option>
		<option value="2"{$privacy_2}>{$lang->ougc_profilecomments_ucp_privacy_2}</option>
	</select></td>
</tr>
</table>
</fieldset><br />');
	ougc_profilecomments_add_template('box', '<br />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->ougc_profilecomments_profile}</strong></td>
</tr>
{$form}
{$comments}
</table>
{$multipage}');
	ougc_profilecomments_add_template('error', '<tr>
<td class="trow2" align="center" colspan="2">{$lang_val}</td>
</tr>');
	ougc_profilecomments_add_template('form', '<tr>
<td class="trow2" colspan="2">
<form action="{$profilelink}" method="post" enctype="multipart/form-data" name="input">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<input type="hidden" name="do" value="comments" />
<input type="hidden" name="process" value="send" />
<textarea name="message" id="message" rows="10" cols="60" tabindex="2">{$mybb->input[\'message\']}</textarea>{$codebuttons}
<div align="center"><input type="submit" class="button" name="submit" value="{$lang->ougc_profilecomments_submit}" tabindex="4" accesskey="s" /></div>
</form>
</td>
</tr>');
	ougc_profilecomments_add_template('empty', '<tr>
<td class="trow2" align="center" colspan="2">{$lang->ougc_profilecomments_empty}</td>
</tr>');
	ougc_profilecomments_add_template('comment', '<tr>
	<td class="{$trow}" rowspan="2" width="1">
		<a href="{$mybb->settings[\'bburl\']}/{$profilelink}{$string}do=comments&amp;conversation={$comment[\'sid\']}" title="{$lang->ougc_profilecomments_comment_conversation}" rel="nofollow"><img src="{$comment[\'avatar\']}" alt="{$award[\'name\']}" width="{$comment[\'width\']}" height="{$comment[\'height\']}" /></a>
	</td>
	<td class="{$trow} smalltext" >
		<span class="largetext">{$comment[\'username\']}</span>{$comment[\'options\']} <span style="float:right;">{$comment[\'date\']}</span>
	</td>
</tr>
<tr>
	<td class="{$trow}" >
		{$comment[\'message\']}<br class="clear" />{$comment[\'edited\']}{$comment[\'ip\']}
	</td>
</tr>');
	ougc_profilecomments_add_template('comment_ip', '<span style="float: right;">{$lang->ougc_profilecomments_comment_ip}: {$comment[\'ip\']}</span>');
	ougc_profilecomments_add_template('comment_options_edit', '<a href="{$profilelink}?do=comments&amp;process=edit&amp;cid={$comment[\'cid\']}&amp;my_post_key={$mybb->post_code}">{$lang->ougc_profilecomments_comment_edit}</a>');
	ougc_profilecomments_add_template('comment_options_delete', '<a href="{$profilelink}?do=comments&amp;process=delete&amp;cid={$comment[\'cid\']}&amp;my_post_key={$mybb->post_code}&amp;confirm=0" onclick="if(confirm(&quot;{$lang->ougc_profilecomments_comment_delete_confirm}&quot;))window.location=this.href.replace(\'confirm=0\', \'confirm=1\');return false;">{$lang->ougc_profilecomments_comment_delete}</a>');
	ougc_profilecomments_add_template('comment_options', '&nbsp;({$edit}{$sep}{$delete})');
	ougc_profilecomments_add_template('comment_edited', '{$lang->ougc_profilecomments_comment_edited}');
	ougc_profilecomments_add_template('edit', '<form action="{$profilelink}" method="post" enctype="multipart/form-data" name="input">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<input type="hidden" name="do" value="comments" />
<input type="hidden" name="process" value="edit" />
<input type="hidden" name="cid" value="{$mybb->input[\'cid\']}" />
<textarea name="message" id="message" rows="10" cols="60" tabindex="2">{$comment[\'message\']}</textarea>{$codebuttons}
<br /><input type="submit" class="button" name="submit" value="{$lang->ougc_profilecomments_submit}" tabindex="4" accesskey="s" />
</form>');
	ougc_profilecomments_add_template('confirm', '{$lang->ougc_profilecomments_comment_delete_confirm}<br />
<a href="{$profilelink}?do=comments&process=delete&cid={$comment[\'cid\']}&my_post_key={$mybb->post_code}&confirm=1"><input type="submit" class="button" name="submit" value="{$lang->yes}" tabindex="3" accesskey="n" /></a> <a href="javascript:history.go(-1);"><input type="submit" class="button" name="submit" value="{$lang->no}" tabindex="4" accesskey="n" /></a>');
	ougc_profilecomments_add_template('conversation', '<html>
<head>
<title>{$lang->profile} - {$mybb->settings[\'bbname\']}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->ougc_profilecomments_profile} {$lang->ougc_profilecomments_conversation}</strong></td>
</tr>
{$comments}
</table>
{$multipage}
{$footer}
</body>
</html>');

	// Add template variables
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('member_profile', '#'.preg_quote('{$adminoptions}').'#', '{$ougc_profilecomments}{$adminoptions}');
	find_replace_templatesets('header', '#'.preg_quote('{$pm_notice}').'#', '{$pm_notice}<!--comments_alert-->');
	find_replace_templatesets('usercp_options', '#'.preg_quote('<fieldset class="trow2">
<legend><strong>{$lang->messaging_notification}</strong></legend>').'#', '{$profilecomments_options}<fieldset class="trow2">
<legend><strong>{$lang->messaging_notification}</strong></legend>');
}

// Deactivate the plugin.
function ougc_profilecomments_deactivate()
{
	global $db;

	// Delete setting group.
	$q = $db->simple_select('settinggroups', 'gid', 'name="ougc_profilecomments"');
	$gid = intval($db->fetch_field($q, 'gid'));
	if($gid)
	{
		$db->delete_query('settings', "gid='{$gid}'");
		$db->delete_query('settinggroups', "gid='{$gid}'");
		rebuild_settings();
	}

	// Remove templates
	$db->delete_query('templates', "title IN('ougc_profilecomments_box', 'ougc_profilecomments_alert', 'ougc_profilecomments_usercp', 'ougc_profilecomments_error', 'ougc_profilecomments_form', 'ougc_profilecomments_empty', 'ougc_profilecomments_comment', 'ougc_profilecomments_comment_ip', 'ougc_profilecomments_comment_options_edit', 'ougc_profilecomments_comment_options_delete', 'ougc_profilecomments_comment_options', 'ougc_profilecomments_comment_edited', 'ougc_profilecomments_edit', 'ougc_profilecomments_confirm', 'ougc_profilecomments_conversation')");

	// Remove template variables
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('member_profile', '#'.preg_quote('{$ougc_profilecomments}').'#', '', 0);
	find_replace_templatesets('header', '#'.preg_quote('<!--comments_alert-->').'#', '', 0);
	find_replace_templatesets('usercp_options', '#'.preg_quote('{$profilecomments_options}').'#', '', 0);
}

// Install the plugin.
function ougc_profilecomments_install()
{
	global $db;
	ougc_profilecomments_uninstall();

	// Create our tables if none exists.
	$db->write_query("CREATE TABLE `".TABLE_PREFIX."ougc_profilecomments` (
			`cid` bigint(30) UNSIGNED NOT NULL AUTO_INCREMENT,
			`uid` bigint(30) NOT NULL DEFAULT '0',
			`sid` bigint(30) NOT NULL DEFAULT '0',
			`message` text NOT NULL,
			`ip` varchar(30) NOT NULL DEFAULT '0',
			`edituid` bigint(30) NOT NULL DEFAULT '0',
			`editdate` int(10) NOT NULL DEFAULT '0',
			`date` int(10) NOT NULL DEFAULT '0',
			PRIMARY KEY (`cid`)
		) ENGINE=MyISAM{$db->build_create_table_collation()};"
	);

	// Edit users table.
	$db->add_column('users', 'alertprofilecomments', "int(1) NOT NULL DEFAULT '1'");
	$db->add_column('users', 'newprofilecomments', "int(10) NOT NULL DEFAULT '0'");
	$db->add_column('users', 'privacyprofilecomments', "int(1) NOT NULL DEFAULT '0'");
}

// Uninstall the plugin.
function ougc_profilecomments_uninstall()
{
	global $db;

	// Remove table
	if($db->table_exists('ougc_profilecomments'))
	{
		$db->drop_table('ougc_profilecomments');
	}

	// Remove users colums
	if($db->field_exists('alertprofilecomments', 'users'))
	{
		$db->drop_column('users', 'alertprofilecomments');
	}
	if($db->field_exists('newprofilecomments', 'users'))
	{
		$db->drop_column('users', 'newprofilecomments');
	}
	if($db->field_exists('privacyprofilecomments', 'users'))
	{
		$db->drop_column('users', 'privacyprofilecomments');
	}
}

// Cheack if plugin is installed.
function ougc_profilecomments_is_installed()
{
	global $db;

	return ($db->table_exists('ougc_profilecomments'));
}

// Show alert bar
function ougc_profilecomments_global()
{
	global $mybb;

	if($mybb->user['newprofilecomments'] && $mybb->user['alertprofilecomments'] == 1 && $mybb->settings['ougc_profilecomments_alert'] = 1)
	{
		global $templates, $header, $lang;
		isset($lang->ougc_profilecomments) or $lang->load('ougc_profilecomments');
		if(defined('OUGC_PROFILECOMMENTS_USEMYBBURLS') && OUGC_PROFILECOMMENTS_USEMYBBURLS)
		{
			$profilelink = get_profile_link($mybb->user['uid']);
		}
		else
		{
			$profilelink = htmlspecialchars_uni(str_replace('{uid}', $mybb->user['uid'], PROFILE_URL));
		}
		$lang->ougc_profilecomments_global_alert = $lang->sprintf($lang->ougc_profilecomments_global_alert, my_number_format($mybb->user['newprofilecomments']));
		eval('$alert = "'.$templates->get('ougc_profilecomments_alert').'";');
		$header = str_replace('<!--comments_alert-->', $alert, $header);
	}
}

// User is trying to send a comment
function ougc_profilecomments_profile()
{
	global $mybb;

	// Profile Comments are deactivated
	if($mybb->settings['ougc_profilecomments_activate'] != 1)
	{
		return false;
	}

	global $lang, $memprofile, $db, $templates, $ougc_profilecomments, $theme, $parser;
	ougc_profilecomments_land_load();

	// Basics..
	$profilelink = $mybb->settings['bburl'].'/'.get_profile_link($memprofile['uid']);
	$cannotcomment = ougc_profilecomments_cancomment($memprofile['uid'], $memprofile['usergroup'], $memprofile['additionalgroups'], $memprofile['privacyprofilecomments'], $memprofile['buddylist'], $memprofile['ignorelist']);

	// Is this user trying to see a conversation?
	if($mybb->input['do'] == 'comments' && isset($mybb->input['conversation']))
	{
		// Multipage
		$to = intval($memprofile['uid']);
		$from = intval($mybb->input['conversation']);

		$query = $db->simple_select('ougc_profilecomments', 'COUNT(cid) AS comments', "(uid='{$to}' AND sid='{$from}') OR (sid='{$to}' AND uid='{$from}')");
		$num_comments = $db->fetch_field($query, 'comments');

		$per_page = intval($mybb->settings['ougc_profilecomments_limit']);
		if(isset($mybb->input['page']))
		{
			$page = intval($mybb->input['page']);
			if($page > 0)
			{
				$start = ($page-1)*$per_page;
			}
		}
		if(!$start)
		{
			$start = 0;
			$page = 1;
		}
		$multipage = multipage($num_comments, $per_page, $page, $profilelink.'?do=comments&conversation=1');

		// No comments? No conversation
		if($num_comments < 1)
		{
			error($lang->ougc_profilecomments_error_conversation);
		}

		// Get comments
		$query = $db->query("
			SELECT c.*, u.username, u.avatar, u.avatardimensions, u.usergroup, u.displaygroup, ue.username AS editusername
			FROM ".TABLE_PREFIX."ougc_profilecomments c
			LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=c.sid)
			LEFT JOIN ".TABLE_PREFIX."users ue ON (ue.uid=c.edituid)
			WHERE (c.uid='{$to}' AND c.sid='{$from}') OR (c.sid='{$to}' AND c.uid='{$from}')
			ORDER BY c.date DESC
			LIMIT {$start}, {$per_page}
		");

		// Format comments
		$comments = ougc_profilecomments_format_comments($query, $memprofile['uid']);

		// Output the page
		global $header, $footer, $headerinclude, $theme;
		reset_breadcrumb();
		add_breadcrumb($lang->nav_profile, $profilelink);
		add_breadcrumb($lang->sprintf($lang->ougc_profilecomments_conversation, $username), $profilelink);

		eval('$page = "'.$templates->get('ougc_profilecomments_conversation').'";');
		output_page($page);
		exit;
	}
	// Add comment
	elseif($mybb->input['do'] == 'comments' && $mybb->input['process'] == 'send')
	{
		// Whaaaat
		if($mybb->request_method != 'post')
		{
			error_no_permission();
		}

		// Verify incoming post
		verify_post_check($mybb->input['my_post_key']);

		// Check comment
		$checkcomment = ougc_profilecomments_checkcomment($mybb->input['message']);
		if($checkcomment)
		{
			$lang_val = 'ougc_profilecomments_error_send_'.$checkcomment;
			error($lang->$lang_val);
		}

		// Check permission
		if($cannotcomment)
		{
			$lang_val = 'ougc_profilecomments_error_'.$cannotcomment;
			error($lang->$lang_val);
		}

		// Check flood time
		$time = intval($mybb->settings['ougc_profilecomments_flood']);
		if($time > 0)
		{
			$time = TIME_NOW-$time;
			$query = $db->simple_select('ougc_profilecomments', 'cid,date', "date>='{$time}' AND sid='{$mybb->user['uid']}'", array('limit' => 1));
			$comment = $db->fetch_array($query);
			if($comment['cid'])
			{
				$waittime = ($comment['date']-$time);
				error($lang->sprintf($lang->ougc_profilecomments_error_flood, my_number_format($waittime)));
			}
		}

		// Try to check if this is a duplicated comment
		ougc_profilecomments_checkduplicate($mybb->user['uid'], $memprofile['uid'], $mybb->input['message']);

		// Insert the comment
		ougc_profilecomments_comment_add($memprofile['uid'], $mybb->user['uid'], $mybb->input['message'], $memprofile['newprofilecomments']);

		// Redirect back to profile
		redirect($profilelink, $lang->ougc_profilecomments_redirect_send);
	}
	// Edit comment
	elseif($mybb->input['do'] == 'comments' && $mybb->input['process'] == 'edit')
	{
		// Get the comment
		if(!($comment = ougc_profilecomments_getcomment($mybb->input['cid'])))
		{
			error($lang->ougc_profilecomments_error_invalidcomment);
		}

		// Check permissions
		if(!ougc_profilecomments_canmange(1, $comment['sid']))
		{
			error_no_permission();
		}

		// Verify incoming post
		verify_post_check($mybb->input['my_post_key']);

		if($mybb->request_method == 'post')
		{
			$errors = array();
			$comment['message'] = $mybb->input['message'];

			// Check comment
			$checkcomment = ougc_profilecomments_checkcomment($mybb->input['message']);
			if($checkcomment)
			{
				$lang_val = 'ougc_profilecomments_error_send_'.$checkcomment;
				$errors[] = $lang->$lang_val;
			}

			// Try to check if this is a duplicated comment
			if(ougc_profilecomments_checkduplicate($mybb->user['uid'], $memprofile['uid'], $mybb->input['message'], true))
			{
				$errors[] = $lang->ougc_profilecomments_error_send_duplicated;
			}

			// Show nice error messages
			if($errors)
			{
				$errors = inline_error($errors);
			}
			else
			{
				// Insert the comment
				ougc_profilecomments_comment_edit($comment['cid'], $mybb->input['message'], $mybb->user['uid']);

				// Redirect back to profile
				redirect($profilelink, $lang->ougc_profilecomments_redirect_edit);
			}
		}

		// Show the codebuttons?
		if($mybb->settings['ougc_profilecomments_bbeditor'] == 1 && $mybb->user['showcodebuttons'] == 1)
		{
			$codebuttons = build_mycode_inserter();
		}

		eval('$page = "'.$templates->get('ougc_profilecomments_edit').'";');
		error($page);
	}
	// Delete comment
	elseif($mybb->input['do'] == 'comments' && $mybb->input['process'] == 'delete')
	{
		// Get the comment
		if(!($comment = ougc_profilecomments_getcomment($mybb->input['cid'])))
		{
			error($lang->ougc_profilecomments_error_invalidcomment);
		}

		// Check permissions
		if(!ougc_profilecomments_canmange(2, $comment['sid']))
		{
			error_no_permission();
		}

		// Verify incoming post
		verify_post_check($mybb->input['my_post_key']);

		// Check confirmation
		if($mybb->input['confirm'] != 1)
		{
			eval('$page = "'.$templates->get('ougc_profilecomments_confirm').'";');
			error($page);
		}

		// Delete the comment
		ougc_profilecomments_comment_delete($comment['cid']);

		// Redirect back to profile
		redirect($profilelink, $lang->ougc_profilecomments_redirect_delete);
	}

	// Multipage
	$query = $db->simple_select('ougc_profilecomments', 'COUNT(cid) AS comments', "uid='{$memprofile['uid']}'");
	$num_comments = $db->fetch_field($query, 'comments');

	$per_page = intval($mybb->settings['ougc_profilecomments_limit']);
	if($mybb->input['view'] == 'comments')
	{
		$page = intval($mybb->input['page']);
		if($page > 0)
		{
			$start = ($page-1)*$per_page;
		}
	}
	if(!$start)
	{
		$start = 0;
		$page = 1;
	}
	$multipage = multipage($num_comments, $per_page, $page, $profilelink.'?view=comments');

	// Permissions
	if($cannotcomment)
	{
		$lang_val = 'ougc_profilecomments_error_'.$cannotcomment;
		$lang_val = $lang->$lang_val;
		eval('$form = "'.$templates->get('ougc_profilecomments_error').'";');
	}
	else
	{
		if($mybb->settings['ougc_profilecomments_bbeditor'] == 1 && $mybb->user['showcodebuttons'] == 1)
		{
			if(function_exists('ougc_build_mycode_inserter'))
			{
				$codebuttons = ougc_build_mycode_inserter();
			}
			else
			{
				$codebuttons = build_mycode_inserter();
			}
		}
		eval('$form = "'.$templates->get('ougc_profilecomments_form').'";');
	}

	// Get comments
	$query = $db->query("
		SELECT c.*, u.username, u.avatar, u.avatardimensions, u.usergroup, u.displaygroup, eu.username AS editusername
		FROM ".TABLE_PREFIX."ougc_profilecomments c
		LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=c.sid)
		LEFT JOIN ".TABLE_PREFIX."users eu ON (eu.uid=c.edituid)
		WHERE c.uid='{$memprofile['uid']}'
		ORDER BY c.date DESC
		LIMIT {$start}, {$per_page}
	");

	// Format comments
	$comments = ougc_profilecomments_format_comments($query, $memprofile['uid']);

	// Output the comments
	eval('$ougc_profilecomments = "'.$templates->get('ougc_profilecomments_box').'";');

	// Reset profile comments counter for current user.
	if($mybb->user['uid'] && ($mybb->user['uid'] == $memprofile['uid']) && $memprofile['newprofilecomments'])
	{
		$db->update_query('users', array('newprofilecomments' => 0), "uid='{$mybb->user['uid']}'", 1);
	}
}

// UserCP integration
function ougc_profilecomments_usercp()
{
	global $profilecomments_options, $templates, $lang, $mybb;
	ougc_profilecomments_land_load();

	// Profile Comments are deactivated
	if($mybb->settings['ougc_profilecomments_activate'] != 1)
	{
		return false;
	}

	$alertcheck = $privacy_0 = $privacy_1 = $privacy_2 = '';
	if($mybb->user['alertprofilecomments'] == 1)
	{
		$alertcheck = ' checked="checked"';
	}
	if($mybb->user['privacyprofilecomments'] == 1)
	{
		$privacy_1 = ' selected="selected"';
	}
	elseif($mybb->user['privacyprofilecomments'] == 2)
	{
		$privacy_2 = ' selected="selected"';
	}
	else
	{
		$privacy_0 = ' selected="selected"';
	}
	eval('$profilecomments_options = "'.$templates->get('ougc_profilecomments_usercp').'";');
}

// Update user options
function ougc_profilecomments_do_usercp()
{
	global $mybb, $db;

	// Profile Comments are deactivated
	if($mybb->settings['ougc_profilecomments_activate'] != 1)
	{
		return false;
	}
	$mybb->input['alertprofilecomments'] = intval($mybb->input['alertprofilecomments']);
	$mybb->input['privacyprofilecomments'] = intval($mybb->input['privacyprofilecomments']);

	$data = array(
		'alertprofilecomments'	=>	1,
		'privacyprofilecomments'	=>	0,
	);
	if(!$mybb->input['alertprofilecomments'])
	{
		$data['alertprofilecomments'] = 0;
	}
	if(in_array($mybb->input['privacyprofilecomments'], array('1', '2')))
	{
		$data['privacyprofilecomments'] = intval($mybb->input['privacyprofilecomments']);
	}
	$db->update_query('users', $data, "uid='{$mybb->user['uid']}'");
}

// Save us time when inserting our settings.
function ougc_profilecomments_add_setting($name, $type, $value, $order)
{
	global $db, $lang;
	ougc_profilecomments_land_load();

	static $ougc_profilecomments_settings_gid = null;
	if(!isset($ougc_profilecomments_settings_gid))
	{
		// Add our setting group.
		$gid = $db->insert_query('settinggroups', 
			array(
				'name'			=> 'ougc_profilecomments',
				'title'			=> $db->escape_string($lang->ougc_profilecomments_sg),
				'description'	=> $db->escape_string($lang->ougc_profilecomments_sg_d),
				'disporder'		=> 5,
				'isdefault'		=> 'no'
			)
		);
		$ougc_profilecomments_settings_gid = intval($gid);
	}
	
	$lang_val = 'ougc_profilecomments_s_'.$name;
	$lang_val_d = $lang_val.'_d';

	$db->insert_query('settings',
		array(
			'name'			=>	$db->escape_string('ougc_profilecomments_'.$name),
			'title'			=>	$db->escape_string($lang->$lang_val),
			'description'	=>	$db->escape_string($lang->$lang_val_d),
			'optionscode'	=>	$db->escape_string($type),
			'value'			=>	$db->escape_string($value),
			'disporder'		=>	intval($order),
			'gid'			=>	$ougc_profilecomments_settings_gid
		)
	);
}

// Save us time when inserting our templates.
function ougc_profilecomments_add_template($name, $content)
{
	global $db;

	$db->insert_query('templates', 
		array(
			'title'		=>	$db->escape_string('ougc_profilecomments_'.$name),
			'template'	=>	$db->escape_string($content),
			'sid'		=>	-1
		)
	);
}

// Parse comments
function ougc_profilecomments_parse_message(&$message, $username='')
{
	global $mybb, $parser;
	if(!is_object($parser))
	{
		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
	}

	// Sepup parser options
	static $ougc_profilecomments_parser_options = null;
	if(!isset($ougc_profilecomments_parser_options))
	{
		$ougc_profilecomments_parser_options = array(
			'allow_html'		=>	intval($mybb->settings['ougc_profilecomments_allowhtml']),
			'allow_smilies'		=>	intval($mybb->settings['ougc_profilecomments_allosmilies']),
			'allow_mycode'		=>	intval($mybb->settings['ougc_profilecomments_allowbbcode']),
			'allow_imgcode'		=>	intval($mybb->settings['ougc_profilecomments_allowimgcode']),
			'allow_videocode'	=>	intval($mybb->settings['ougc_profilecomments_allowvideocode']),
			'filter_badwords'	=>	intval($mybb->settings['ougc_profilecomments_filterbadwords']),
			'shorten_urls'		=>	1
		);
	}
	$ougc_profilecomments_parser_options['me_username'] = htmlspecialchars_uni($username);

	$message = $parser->parse_message($message, $ougc_profilecomments_parser_options);
}

// Check if user can comment in X profile
function ougc_profilecomments_cancomment($uid, $usergroup, $additionalgroups, $privacy=0, $buddylist='', $ignorelist='')
{
	global $mybb;

	// No self-commenting allowed
	if($mybb->user['uid'] == $uid)
	{
		return 'self';
	}

	// Current user can't send comments
	if(!$mybb->user['uid'] || !ougc_profilecomments_check_groups($mybb->user['usergroup'], $mybb->user['additionalgroups'], $mybb->settings['ougc_profilecomments_cansend']))
	{
		return 'cansend';
	}

	// Profile owner can't receive comments
	if(!$uid || !ougc_profilecomments_check_groups($usergroup, $additionalgroups, $mybb->settings['ougc_profilecomments_canreceive']))
	{
		return 'canreceive';
	}

	// Profile owner doesn't like comments at all
	if($privacy == 1)
	{
		return 'disabled';
	}

	// Profile owner only want budies comments
	if($privacy == 2)
	{
		if(!in_array($mybb->user['uid'], (explode(',', $buddylist))))
		{
			return 'buddies';
		}
	}

	// Ignored users cannot comment
	if($mybb->settings['ougc_profilecomments_ignored'] == 1 && !empty($ignorelist))
	{
		if(in_array($mybb->user['uid'], (explode(',', $ignorelist))))
		{
			return 'ignored';
		}
	}

	return false;
}

// Check group permissions from spesific user
function ougc_profilecomments_check_groups($usergroup, $additionalgroups, $groups, $empty=true)
{
	// Return true if there are no groups to check and empty is true
	if(empty($groups) && $empty == true)
	{
		return true;
	}

	// Get array of usergroups
	$usergroups = array();
	if(!empty($additionalgroups))
	{
		$usergroups = explode(',', $additionalgroups);
	}
	$usergroups[] = $usergroup;

	// Finelly check
	$groups = explode(',', $groups);
	foreach($usergroups as $gid)
	{
		if(in_array($gid, $groups))
		{
			// A group match, return true
			return true;
		}
	}

	// Nothing matchs, return false
	return false;
}

// Check if user can edit/delete X comment
function ougc_profilecomments_canmange($type=0, $uid=0)
{
	global $mybb;

	// Users = no
	if(!$mybb->user['uid'])
	{
		return false;
	}

	// Check if can edit own comments
	if($type == 1)
	{
		if($mybb->user['uid'] == $uid && ougc_profilecomments_check_groups($mybb->user['usergroup'], $mybb->user['additionalgroups'], $mybb->settings['ougc_profilecomments_canedit']))
		{
			return true;
		}
	}
	// Check if can delete own comments
	elseif($type == 2)
	{
		if($mybb->user['uid'] == $uid && ougc_profilecomments_check_groups($mybb->user['usergroup'], $mybb->user['additionalgroups'], $mybb->settings['ougc_profilecomments_candelete']))
		{
			return true;
		}
	}
	// Check if can manage any comment
	if(ougc_profilecomments_check_groups($mybb->user['usergroup'], $mybb->user['additionalgroups'], $mybb->settings['ougc_profilecomments_canmanage']))
	{
		return true;
	}

	return false;
}

// Check if comment is valid
function ougc_profilecomments_checkcomment($message)
{
	// No comment?
	if(!trim($message))
	{
		return 'empty';
	}

	global $mybb, $lang;

	// Check characters length
	$length = my_strlen($mybb->input['message']);
	$minlength = intval($mybb->settings['ougc_profilecomments_minlength']);
	$maxlength = intval($mybb->settings['ougc_profilecomments_maxlength']);
	if($minlength > 0 && $minlength > $length)
	{
		error($lang->sprintf($lang->ougc_profilecomments_error_send_minlength, $minlength));
	}
	if($maxlength > 0 && $maxlength < $length)
	{
		error($lang->sprintf($lang->ougc_profilecomments_error_send_maxlength, $maxlength));
	}

	return false;
}

// Check forduplicated comment
function ougc_profilecomments_checkduplicate($from, $to, $message, $inline=false)
{
	global $db;

	$query = $db->simple_select('ougc_profilecomments', 'cid', "uid='".intval($to)."' AND sid='".intval($from)."' AND message='{$db->escape_string($message)}'", array('limit' => 1));
	$comment = $db->fetch_field($query, 'cid');

	if($comment)
	{
		global $lang;
		ougc_profilecomments_land_load();

		if($inline == false)
		{
			error($lang->ougc_profilecomments_error_send_duplicated);
		}
		return true;
	}
	return false;
}

// Inser a new comment into the DB
function ougc_profilecomments_comment_add($to, $from, $message, $count)
{
	global $db, $plugins;

	// Set the array
	$insertdata = array(
		'uid'		=>	intval($to),
		'sid'		=>	intval($from),
		'message'	=>	$db->escape_string($message),
		'date'		=>	TIME_NOW,
		'ip'		=>	get_ip()
	);

	// Run our hook
	$plugins->run_hooks('ougc_profilecomments_comment_add');

	// Insert the comment
	$db->insert_query('ougc_profilecomments', $insertdata);

	// Update user comments count
	$db->update_query('users', array('newprofilecomments' => ++$count), "uid='".intval($to)."'", 1);
}

// Get comment from the DB
function ougc_profilecomments_getcomment($cid)
{
	global $db;

	// Query the comment
	$query = $db->simple_select('ougc_profilecomments', '*', "cid='".intval($cid)."'", array('limit' => 1));
	$comment = $db->fetch_array($query);

	// Return the comment if any
	if($comment['cid'])
	{
		return $comment;
	}
	return false;
}

// Edit a comment to the DB
function ougc_profilecomments_comment_edit($cid, $message, $euid)
{
	global $db, $plugins;

	// Run our hook
	$plugins->run_hooks('ougc_profilecomments_comment_edit');

	// Insert the comment
	$message = array('message' => $db->escape_string($message), 'edituid' => intval($euid), 'editdate' => TIME_NOW);
	$db->update_query('ougc_profilecomments', $message, "cid='".intval($cid)."'", 1);
}

// Delete a comment from the DB
function ougc_profilecomments_comment_delete($cid)
{
	global $db, $plugins;

	// Run our hook
	$plugins->run_hooks('ougc_profilecomments_comment_delete');

	// Insert the comment
	$db->delete_query('ougc_profilecomments', "cid='".intval($cid)."'", 1);
}

// Format the comments
function ougc_profilecomments_format_comments($query, $uid)
{
	global $db, $templates, $lang, $mybb;
	ougc_profilecomments_land_load();

	// Profile owner link
	if(OUGC_PROFILECOMMENTS_USEMYBBURLS == 1)
	{
		$profilelink = get_profile_link($uid);
		$string = '?';
	}
	else
	{
		$profilelink = htmlspecialchars_uni(str_replace('{uid}', $uid, PROFILE_URL));
		$string = '&amp;';
	}

	$comments = '';
	while($comment = $db->fetch_array($query))
	{
		// Background color
		$trow = alt_trow();

		// Managements options
		if(ougc_profilecomments_canmange())
		{
			eval('$comment[\'ip\'] = "'.$templates->get('ougc_profilecomments_comment_ip').'";');
		}
		else
		{
			$comment['ip'] = '';
		}

		// Edit / delete options
		if(ougc_profilecomments_canmange(1, $comment['sid']))
		{
			eval('$edit = "'.$templates->get('ougc_profilecomments_comment_options_edit').'";');
		}
		if(ougc_profilecomments_canmange(2, $comment['sid']))
		{
			eval('$delete = "'.$templates->get('ougc_profilecomments_comment_options_delete').'";');
		}
		if($edit || $delete)
		{
			$sep = '';
			if($edit && $delete)
			{
				$sep = ' - ';
			}
			eval('$comment[\'options\'] = "'.$templates->get('ougc_profilecomments_comment_options').'";');
		}

		// Format the comment date and time
		$comment['date'] = $lang->sprintf($lang->ougc_profilecomments_comment_time, my_date($mybb->settings['dateformat'], $comment['date']), my_date($mybb->settings['timeformat'], $comment['date']));

		// Show edit info
		if($comment['edituid'])
		{
			// Format the username
			$comment['editusername'] = htmlspecialchars_uni($comment['editusername']);
			$comment['editusername'] = build_profile_link($comment['editusername'], $comment['edituid']);

			// Format the comment date and time
			$comment['editdate'] = $lang->sprintf($lang->ougc_profilecomments_comment_time, my_date($mybb->settings['dateformat'], $comment['editdate']), my_date($mybb->settings['timeformat'], $comment['editdate']));

			// Eval it
			$lang->ougc_profilecomments_comment_edited = $lang->sprintf($lang->ougc_profilecomments_comment_edited, $comment['editusername'], $comment['editdate']);
			eval('$comment[\'edited\'] = "'.$templates->get('ougc_profilecomments_comment_edited').'";');
		}

		// Parser message
		ougc_profilecomments_parse_message($comment['message'], $comment['username']);

		// Format username
		$comment['username'] = htmlspecialchars_uni($comment['username']);
		$comment['username'] = format_name($comment['username'], $comment['usergroup'], $comment['displaygroup']);
		if(OUGC_PROFILECOMMENTS_USEMYBBURLS == 1)
		{
			$comment['username'] = build_profile_link($comment['username'], $comment['sid']);
		}
		else
		{
			if(!$comment['username'] && !$comment['sid'])
			{
				$comment['username'] = $lang->guest;
			}
			elseif(!$comment['sid'])
			{
				$comment['username'] = $comment['username'];
			}
			else
			{
				$comment['username'] = "<a href=\"{$mybb->settings['bburl']}/".htmlspecialchars_uni(str_replace('{uid}', $comment['sid'], PROFILE_URL))."\">{$comment['username']}</a>";
			}
		}

		// Get avatar
		if(!$comment['avatar'])
		{
			$comment['avatar'] = $mybb->settings['ougc_profilecomments_davatar'];
			$comment['avatardimensions'] = $mybb->settings['ougc_profilecomments_davatardim'];
		}
		$comment['avatar'] = htmlspecialchars_uni($comment['avatar']);
		$dimensions = explode('|', $comment['avatardimensions']);
		if($dimensions[0] && $dimensions[1])
		{
			list($maxwidth, $maxheight) = array_map('intval', explode('x', my_strtolower($mybb->settings['ougc_profilecomments_maxdim'])));
			if($dimensions[0] > $maxwidth || $dimensions[1] > $maxheight)
			{
				require_once MYBB_ROOT."inc/functions_image.php";
				$scale = scale_image($dimensions[0], $dimensions[1], $maxwidth, $maxheight);
			}
		}
		$comment['width'] = ($scale['width'] ? $scale['width'] : $dimensions[0]);
		$comment['height'] = ($scale['height'] ? $scale['height'] : $dimensions[1]);

		// Add it +
		eval('$comments .= "'.$templates->get('ougc_profilecomments_comment').'";');
	}

	// This iser has no comments
	if(!$comments)
	{
		eval('$comments = "'.$templates->get('ougc_profilecomments_empty').'";');
	}

	return $comments;
}