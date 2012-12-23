<?php

/***************************************************************************
 *
 *   OUGC Profile Comments plugin (/inc/languages/english/ougc_profilecomments.lang.php)
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

$l['ougc_profilecomments'] = 'OUGC Profile Comments';
$l['ougc_profilecomments_profile'] = 'Profile Comments';
$l['ougc_profilecomments_global_alert'] = 'You have {1} new profile comments.';

// View comments
$l['ougc_profilecomments_empty'] = 'There are currently no comments fro this user.';
$l['ougc_profilecomments_submit'] = 'Submit Comment';
$l['ougc_profilecomments_comment_ip'] = 'IP';
$l['ougc_profilecomments_comment_edit'] = 'Edit';
$l['ougc_profilecomments_comment_delete'] = 'Delete';
$l['ougc_profilecomments_comment_delete_confirm'] = 'Are you sure you want to delete this comment?';
$l['ougc_profilecomments_comment_conversation'] = 'View conversation';
$l['ougc_profilecomments_comment_time'] = '{1}, {2}';
$l['ougc_profilecomments_comment_edited'] = 'Comment edited by {1} on {2}.';

// Errors
$l['ougc_profilecomments_error_self'] = 'You cannot comment yourself.';
$l['ougc_profilecomments_error_cansend'] = 'You have no permission to send comments.';
$l['ougc_profilecomments_error_canreceive'] = 'This user have no permission receive comments.';
$l['ougc_profilecomments_error_disabled'] = 'Comments are disabled in this profile.';
$l['ougc_profilecomments_error_buddies'] = 'Only buddies can comment in this profile.';
$l['ougc_profilecomments_error_ignored'] = 'Comments are disabled in this profile.';
$l['ougc_profilecomments_error_flood'] = 'You have to wait {1} seconds before sending a new comment.';
$l['ougc_profilecomments_error_invalidcomment'] = 'The selected comment doesn\'t exists.';
$l['ougc_profilecomments_error_conversation'] = 'There is no a conversation between this two users.';

$l['ougc_profilecomments_error_send_empty'] = 'You need to enter a comment.';
$l['ougc_profilecomments_error_send_minlength'] = 'You need to write a minimum of {1} characters.';
$l['ougc_profilecomments_error_send_maxlength'] = 'You need to write a maximum of {1} characters.';
$l['ougc_profilecomments_error_send_duplicated'] = 'This comments seems to be duplicated.';

// Redirect
$l['ougc_profilecomments_redirect_send'] = 'Comment was adeed succefully.<br />You will now be redirected.';
$l['ougc_profilecomments_redirect_edit'] = 'Comment was edited succefully.<br />You will now be redirected.';
$l['ougc_profilecomments_redirect_delete'] = 'Comment was deleted succefully.<br />You will now be redirected.';

// Conversation
$l['ougc_profilecomments_conversation'] = 'Conversation';

// UserCP
$l['ougc_profilecomments_ucp_alert'] = 'Alert me with a notice when I receive new Profile Comments.';
$l['ougc_profilecomments_ucp_privacy'] = 'Privacy Options';
$l['ougc_profilecomments_ucp_privacy_0'] = 'Everybody';
$l['ougc_profilecomments_ucp_privacy_1'] = 'Nobody';
$l['ougc_profilecomments_ucp_privacy_2'] = 'Buddies';