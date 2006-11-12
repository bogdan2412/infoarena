<?php
// Version: 1.1 RC2; Display

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Show the anchor for the top and for the first message. If the first message is new, say so.
	echo '
<a name="top"></a>
<a name="msg', $context['first_message'], '"></a>', $context['first_new_message'] ? '<a name="new"></a>' : '';

		// Show the linktree
	echo '
<div>', theme_linktree(), '</div>';

	// Is this topic also a poll?
	if ($context['is_poll'])
	{
		echo '
<table cellpadding="3" cellspacing="0" border="0" width="100%" class="tborder" style="padding-top: 0; margin-bottom: 2ex;">
	<tr>
		<td class="titlebg" colspan="2" valign="middle" style="padding-left: 6px;">
			<img src="', $settings['images_url'], '/topic/', $context['poll']['is_locked'] ? 'normal_poll_locked' : 'normal_poll', '.gif" alt="" align="bottom" /> ', $txt['smf43'], '
		</td>
	</tr>
	<tr>
		<td width="5%" valign="top" class="windowbg"><b>', $txt['smf21'], ':</b></td>
		<td class="windowbg">
			', $context['poll']['question'];
		if (!empty($context['poll']['expire_time']))
			echo '
					&nbsp;(', ($context['poll']['is_expired'] ? $txt['poll_expired_on'] : $txt['poll_expires_on']), ': ', $context['poll']['expire_time'], ')';

		// Are they not allowed to vote but allowed to view the options?
		if ($context['poll']['show_results'] || !$context['allow_vote'])
		{
			echo '
			<table>
				<tr>
					<td style="padding-top: 2ex;">
						<table border="0" cellpadding="0" cellspacing="0">';

				// Show each option with its corresponding percentage bar.
			foreach ($context['poll']['options'] as $option)
				echo '
							<tr>
								<td style="padding-right: 2ex;', $option['voted_this'] ? 'font-weight: bold;' : '', '">', $option['option'], '</td>', $context['allow_poll_view'] ? '
								<td nowrap="nowrap">' . $option['bar'] . ' ' . $option['votes'] . ' (' . $option['percent'] . '%)</td>' : '', '
							</tr>';

			echo '
						</table>
					</td>
					<td valign="bottom" style="padding-left: 15px;">';

			// If they are allowed to revote - show them a link!
			if ($context['allow_change_vote'])
				echo '
					<a href="', $scripturl, '?action=vote;topic=', $context['current_topic'], '.', $context['start'], ';poll=', $context['poll']['id'], ';sesc=', $context['session_id'], '">', $txt['poll_change_vote'], '</a><br />';

			// If we're viewing the results... maybe we want to go back and vote?
			if ($context['poll']['show_results'] && $context['allow_vote'])
				echo '
						<a href="', $scripturl, '?topic=', $context['current_topic'], '.', $context['start'], '">', $txt['poll_return_vote'], '</a><br />';

			// If they're allowed to lock the poll, show a link!
			if ($context['poll']['lock'])
				echo '
						<a href="', $scripturl, '?action=lockVoting;topic=', $context['current_topic'], '.', $context['start'], ';sesc=', $context['session_id'], '">', !$context['poll']['is_locked'] ? $txt['smf30'] : $txt['smf30b'], '</a><br />';

			// If they're allowed to edit the poll... guess what... show a link!
			if ($context['poll']['edit'])
				echo '
						<a href="', $scripturl, '?action=editpoll;topic=', $context['current_topic'], '.', $context['start'], '">', $txt['smf39'], '</a>';

			echo '
					</td>
				</tr>', $context['allow_poll_view'] ? '
				<tr>
					<td colspan="2"><b>' . $txt['smf24'] . ': ' . $context['poll']['total_votes'] . '</b></td>
				</tr>' : '', '
			</table><br />';
		}
		// They are allowed to vote! Go to it!
		else
		{
			echo '
			<form action="', $scripturl, '?action=vote;topic=', $context['current_topic'], '.', $context['start'], ';poll=', $context['poll']['id'], '" method="post" style="margin: 0px;">
				<table>
					<tr>
						<td colspan="2">';

			// Show a warning if they are allowed more than one option.
			if ($context['poll']['allowed_warning'])
				echo '
							', $context['poll']['allowed_warning'], '
						</td>
					</tr><tr>
						<td>';

			// Show each option with its button - a radio likely.
			foreach ($context['poll']['options'] as $option)
				echo '
							', $option['vote_button'], ' ', $option['option'], '<br />';

			echo '
						</td>
						<td valign="bottom" style="padding-left: 15px;">';

			// Allowed to view the results? (without voting!)
			if ($context['allow_poll_view'])
				echo '
							<a href="', $scripturl, '?topic=', $context['current_topic'], '.', $context['start'], ';viewResults">', $txt['smf29'], '</a><br />';

			// Show a link for locking the poll as well...
			if ($context['poll']['lock'])
				echo '
							<a href="', $scripturl, '?action=lockVoting;topic=', $context['current_topic'], '.', $context['start'], ';sesc=', $context['session_id'], '">', (!$context['poll']['is_locked'] ? $txt['smf30'] : $txt['smf30b']), '</a><br />';

			// Want to edit it? Click right here......
			if ($context['poll']['edit'])
				echo '
							<a href="', $scripturl, '?action=editpoll;topic=', $context['current_topic'], '.', $context['start'], '">', $txt['smf39'], '</a>';

				echo '
						</td>
					</tr><tr>
						<td colspan="2"><input type="submit" value="', $txt['smf23'], '" /></td>
					</tr>
				</table>
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</form>';
		}

		echo '
		</td>
	</tr>
</table>';
	}

	// Does this topic have some events linked to it?
	if (!empty($context['calendar_events']))
	{
		echo '
<table cellpadding="3" cellspacing="0" border="0" width="100%" class="tborder" style="padding-top: 0; margin-bottom: 3ex;">
		<tr>
				<td class="titlebg" valign="middle" align="left" style="padding-left: 6px;">
						', $txt['calendar_linked_events'], '
				</td>
		</tr>
		<tr>
				<td width="5%" valign="top" class="windowbg">
						<ul>';
		foreach ($context['calendar_events'] as $event)
			echo '
								<li>
										<b>', $event['title'] , '</b> ' , ($event['can_edit'] ? '<a href="' . $event['modify_href'] . '" >[' . $txt['calendar_shortedit'] . ']</a> ' : '') , ': ', $event['start_date'], ($event['start_date'] != $event['end_date'] ? ' - ' . $event['end_date'] : ''), '
								</li>';
		echo '
						</ul>
				</td>
		</tr>
</table>';
	}

	// Build the normal button array.
	$normal_buttons = array(
		'reply' => array('test' => 'can_reply', 'text' => 146, 'image' => 'reply.gif', 'lang' => true, 'url' => $scripturl . '?action=post;topic=' . $context['current_topic'] . '.' . $context['start'] . ';num_replies=' . $context['num_replies']),
		'notify' => array('test' => 'can_mark_notify', 'text' => 125, 'image' => 'notify.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . ($context['is_marked_notify'] ? $txt['notification_disable_topic'] : $txt['notification_enable_topic']) . '\');"', 'url' => $scripturl . '?action=notify;sa=' . ($context['is_marked_notify'] ? 'off' : 'on') . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']),
		'custom' => array(),
		'send' => array('test' => 'can_send_topic', 'text' => 707, 'image' => 'sendtopic.gif', 'lang' => true, 'url' => $scripturl . '?action=sendtopic;topic=' . $context['current_topic'] . '.0'),
		'print' => array('text' => 465, 'image' => 'print.gif', 'lang' => true, 'custom' => 'target="_blank"', 'url' => $scripturl . '?action=printpage;topic=' . $context['current_topic'] . '.0'),
	);

	// Special case for the custom one.
	if ($context['user']['is_logged'] && $settings['show_mark_read'])
		$normal_buttons['custom'] = array('text' => 'mark_unread', 'image' => 'markunread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=topic;t=' . $context['mark_unread_time'] . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']);
	elseif ($context['can_add_poll'])
		$normal_buttons['custom'] = array('text' => 'add_poll', 'image' => 'add_poll.gif', 'lang' => true, 'url' => $scripturl . '?action=editpoll;add;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']);
	else
		unset($normal_buttons['custom']);

	// Show the page index... "Pages: [1]".
	echo '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="middletext" valign="bottom" style="padding-bottom: 4px;">', $txt[139], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#lastPost"><b>' . $txt['topbottom5'] . '</b></a>' : '', '</td>
		<td align="right" style="padding-right: 1ex;">
			<div class="nav" style="margin-bottom: 2px;"> ', $context['previous_next'], '</div>
			<table cellpadding="0" cellspacing="0">
				<tr>
					', template_button_strip($normal_buttons, 'bottom'), '
				</tr>
			</table>
		</td>
	</tr>
</table>';

	// Show the topic information - icon, subject, etc.
	echo '
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-bottom: 0;">
		<tr>
				<td class="catbg_l"></td>
				<td class="catbg3" height="30px" valign="middle" width="25px" style="padding-left: 0px;">
						<img src="', $settings['images_url'], '/topic/', $context['class'], '.gif" align="bottom" alt="" />
				</td>
				<td class="catbg3" valign="middle" style="padding-left: 0px;" id="top_subject">
						', $txt[118], ': ', $context['subject'], ' &nbsp;(', $txt[641], ' ', $context['num_views'], ' ', $txt[642], ')
				</td>
				<td class="catbg_r"></td>
		</tr>';
	if (!empty($settings['display_who_viewing']))
	{
		echo '
		<tr>
				<td colspan="3" class="smalltext">';

		// Show just numbers...?
		if ($settings['display_who_viewing'] == 1)
				echo count($context['view_members']), ' ', count($context['view_members']) == 1 ? $txt['who_member'] : $txt[19];
		// Or show the actual people viewing the topic?
		else
			echo empty($context['view_members_list']) ? '0 ' . $txt[19] : implode(', ', $context['view_members_list']) . ((empty($context['view_num_hidden']) || $context['can_moderate_forum']) ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');

		// Now show how many guests are here too.
		echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_topic'], '
				</td>
		</tr>';
	}

	echo '
</table>';

	echo '
<form action="', $scripturl, '?action=quickmod2;topic=', $context['current_topic'], '.', $context['start'], '" method="post" name="quickModForm" id="quickModForm" style="margin: 0;" onsubmit="return in_edit_mode == 1 ? modify_save(\'' . $context['session_id'] . '\') : confirm(\'' . $txt['quickmod_confirm'] . '\');">';

	// These are some cache image buttons we may want.
	$reply_button = create_button('quote.gif', 145, 'smf240', 'align="middle"');
	$modify_button = create_button('modify.gif', 66, 17, 'align="middle"');
	$remove_button = create_button('delete.gif', 121, 31, 'align="middle"');
	$split_button = create_button('split.gif', 'smf251', 'smf251', 'align="middle"');

	// Get all the messages...
	while ($message = $context['get_message']())
	{
	// Show's the post details
		echo '
	<table class="titlebg" width="100%">
	<tr>
		<td width="30px">
		<a href="', $message['href'], '"><img src="', $message['icon_url'] . '" alt="" border="0" /></a>
		</td>
		<td>
			<div class="smalltext" style="font-weight: bold;" id="subject_', $message['id'], '">
				<a href="', $message['href'], '">', $message['subject'], '</a>
			</div>
		</td>
		<td align="right" class="smalltext">
		&#171; ', !empty($message['counter']) ? $txt[146] . ' #' . $message['counter'] : '', ' ', $txt[30], ': ', $message['time'], ' &#187;
		</td>
	</tr>
	</table>';
	// Time to display all the posts
	echo '
<table class="tborder" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr><td style="padding: 1px 1px 0 1px;">';

		// Show the message anchor and a "new" anchor if this message is new.
		if ($message['id'] != $context['first_message'])
			echo '
		<a name="msg', $message['id'], '"></a>', $message['first_new'] ? '<a name="new"></a>' : '';

		echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr><td class="', $message['alternate'] == 0 ? 'windowbg' : 'windowbg2', '">';

		// Show information about the poster of this message.
		echo '
				<table class="bordercolor" width="100%" cellpadding="3" cellspacing="0" style="table-layout: fixed; padding: 5px;">
					<tr>';
		// Don't show these things for guests.
		if (!$message['member']['is_guest'])
		{
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
				echo '
						<td width="80px" align="center" valign="middle">';
			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
				echo '
								<div style="width: 100%;">', $message['member']['avatar']['image'], '</div>';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $message['member']['blurb'] != '')
				echo '
								<span class="smalltext">', $message['member']['blurb'], '</span>';
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
				echo '
						</td>';
		}
		
		// Show member's/guest name
				echo '
						<td valign="top" style="overflow: hidden;" class="smalltext">
							<font size="4">', $message['member']['link'], '</font>';
		// Don't show these things for guests.
		if (!$message['member']['is_guest'])
		{
			// Show online and offline buttons?
			if (!empty($modSettings['onlineEnable']) && !$message['member']['is_guest'])
				echo '
								', $context['can_send_pm'] ? '<a href="' . $message['member']['online']['href'] . '" title="' . $message['member']['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $message['member']['online']['image_href'] . '" alt="' . $message['member']['online']['text'] . '" border="0" style="margin-top: 2px;" />' : $message['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '' : '', '';
		}
		// Show the member's custom title, if they have one.
		if (isset($message['member']['title']) && $message['member']['title'] != '')
			echo '
								<br />', $message['member']['title'], '';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (isset($message['member']['group']) && $message['member']['group'] != '')
			echo '
								<br />', $message['member']['group'], '';
		// Don't show these things for guests.
		if (!$message['member']['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
				echo '
								<br />', $message['member']['post_group'], '<br />
								<br />';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				// Don't show the profile button if you're not allowed to view the profile.
				if ($message['member']['can_view_profile'])
					echo '
								<a href="', $message['member']['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt[27] . '" title="' . $txt[27] . '" border="0" />' : $txt[27]), '</a>';

				// Don't show an icon if they haven't specified a website.
				if ($message['member']['website']['url'] != '')
					echo '
								<a href="', $message['member']['website']['url'], '" title="' . $message['member']['website']['title'] . '" target="_blank">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt[515] . '" border="0" />' : $txt[515]), '</a>';

				// Don't show the email address if they want it hidden.
				if (empty($message['member']['hide_email']))
					echo '
								<a href="mailto:', $message['member']['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt[69] . '" title="' . $txt[69] . '" border="0" />' : $txt[69]), '</a>';

				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
								<a href="', $scripturl, '?action=pm;sa=send;u=', $message['member']['id'], '" title="', $message['member']['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($message['member']['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $message['member']['online']['label'] . '" border="0" />' : $message['member']['online']['label'], '</a>';
			}
					echo '
						</td>
						<td width="120px" class="smalltext">';

			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
			echo '
								', $message['member']['group_stars'], '<br />';

			// Is karma display enabled?  Total or +/-?
			if ($modSettings['karmaMode'] == '1')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' ', $message['member']['karma']['good'] - $message['member']['karma']['bad'], '<br />';
			elseif ($modSettings['karmaMode'] == '2')
				echo '
								
								', $modSettings['karmaLabel'], ' +', $message['member']['karma']['good'], '/-', $message['member']['karma']['bad'], '<br />';

			// Is this user allowed to modify this member's karma?
			if ($message['member']['karma']['allow'])
				echo '
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $message['member']['id'], ';topic=', $context['current_topic'], '.' . $context['start'], ';m=', $message['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $message['member']['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';m=', $message['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a><br />';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $message['member']['gender']['image'] != '')
				echo '
								', $txt[231], ': ', $message['member']['gender']['image'], '<br />';

			// Show how many posts they have made.
			echo '
								', $txt[26], ': ', $message['member']['posts'], '<br />
								<br />';

			// This shows the popular messaging icons.
			echo '
								', $message['member']['icq']['link'], '
								', $message['member']['msn']['link'], '
								', $message['member']['aim']['link'], '
								', $message['member']['yim']['link'], '<br />';
		}
		// Otherwise, show the guest's email.
		elseif (empty($message['member']['hide_email']))
			echo '
								<br />
								<br />
								<a href="mailto:', $message['member']['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt[69] . '" title="' . $txt[69] . '" border="0" />' : $txt[69]), '</a>';

		// Done with the information about the poster... on to the post itself.
		echo '
							
						</td>
					</tr>
				</table>
				<table width="100%">
					<tr>
						<td valign="top" width="100%">
							<table width="100%" border="0"><tr>
								<td align="', !$context['right_to_left'] ? 'right' : 'left', '" valign="bottom" height="20" style="font-size: smaller;">';

		// Can they reply? Have they turned on quick reply?
		if ($context['can_reply'] && !empty($options['display_quick_reply']))
			echo '
					<a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';num_replies=', $context['num_replies'], ';sesc=', $context['session_id'], '" onclick="doQuote(', $message['id'], ', \'', $context['session_id'], '\'); return false;">', $reply_button, '</a>';

		// So... quick reply is off, but they *can* reply?
		elseif ($context['can_reply'])
			echo '
					<a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';num_replies=', $context['num_replies'], ';sesc=', $context['session_id'], '">', $reply_button, '</a>';

		// Can the user modify the contents of this post?
		if ($message['can_modify'])
			echo '
					<a href="', $scripturl, '?action=post;msg=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';sesc=', $context['session_id'], '">', $modify_button, '</a>';

		// How about... even... remove it entirely?!
		if ($message['can_remove'])
			echo '
					<a href="', $scripturl, '?action=deletemsg;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';sesc=', $context['session_id'], '" onclick="return confirm(\'', $txt[154], '?\');">', $remove_button, '</a>';

		// What about splitting it off the rest of the topic?
		if ($context['can_split'])
			echo '
					<a href="', $scripturl, '?action=splittopics;topic=', $context['current_topic'], '.0;at=', $message['id'], '">', $split_button, '</a>';

		// Show a checkbox for quick moderation?
		if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $message['can_remove'])
			echo '
									<input type="checkbox" name="msgs[]" value="', $message['id'], '" class="check" onclick="document.getElementById(\'quickmodSubmit\').style.display = \'\';" />';

		// Show the post itself, finally!
		echo '
								</td>
							</tr></table>
							<hr width="100%" size="1" class="hrcolor" />
							<div class="post"', $message['can_modify'] ? ' id="msg_' . $message['id'] . '"' : '', '>', $message['body'], '</div>', $message['can_modify'] ? '
							<img src="' . $settings['images_url'] . '/icons/modify_inline.gif" alt="" align="right" id="modify_button_' . $message['id'] . '" style="cursor: pointer; display: none;" onclick="modify_msg(\'' . $message['id'] . '\', \'' . $context['session_id'] . '\')" />' : '' , '
						</td>
					</tr>';

		// Now for the attachments, signature, ip logged, etc...
		echo '
					<tr>
						<td valign="bottom" class="smalltext" width="85%">
							<table width="100%" border="0" style="table-layout: fixed;"><tr>
								<td colspan="2" class="smalltext" width="100%">';

		// Assuming there are attachments...
		if (!empty($message['attachment']))
		{
			echo '
									<hr width="100%" size="1" class="hrcolor" />
									<div style="overflow: auto; width: 100%;">';
			foreach ($message['attachment'] as $attachment)
			{
				if ($attachment['is_image'])
				{
					if ($attachment['thumbnail']['has_thumb'])
						echo '
									<a href="', $attachment['href'], ';image" id="link_', $attachment['id'], '" onclick="', $attachment['thumbnail']['javascript'], '"><img src="', $attachment['thumbnail']['href'], '" alt="" id="thumb_', $attachment['id'], '" border="0" /></a><br />';
					else
						echo '
									<img src="' . $attachment['href'] . ';image" alt="" width="' . $attachment['width'] . '" height="' . $attachment['height'] . '" border="0" /><br />';
				}
				echo '
										<a href="' . $attachment['href'] . '"><img src="' . $settings['images_url'] . '/icons/clip.gif" align="middle" alt="*" border="0" />&nbsp;' . $attachment['name'] . '</a> (', $attachment['size'], ($attachment['is_image'] ? ', ' . $attachment['real_width'] . 'x' . $attachment['real_height'] . ' - ' . $txt['attach_viewed'] : ' - ' . $txt['attach_downloaded']) . ' ' . $attachment['downloads'] . ' ' . $txt['attach_times'] . '.)<br />';
			}

			echo '
									</div>';
		}

		echo '
								</td>
							</tr><tr>
								<td valign="bottom" class="smalltext" id="modified_', $message['id'], '">';

		// Show "« Last Edit: Time by Person »" if this post was edited.
		if ($settings['show_modify'] && !empty($message['modified']['name']))
			echo '
									&#171; <i>', $txt[211], ': ', $message['modified']['time'], ' ', $txt[525], ' ', $message['modified']['name'], '</i> &#187;';

		echo '
								</td>
								<td align="', !$context['right_to_left'] ? 'right' : 'left', '" valign="bottom" class="smalltext">';

		// Maybe they want to report this post to the moderator(s)?
		if ($context['can_report_moderator'])
			echo '
									<a href="', $scripturl, '?action=reporttm;topic=', $context['current_topic'], '.', $message['counter'], ';msg=', $message['id'], '">', $txt['rtm1'], '</a> &nbsp;';
		echo '
									<img src="', $settings['images_url'], '/ip.gif" alt="" border="0" />';

		// Show the IP to this user for this post - because you can moderate?
		if ($context['can_moderate_forum'] && !empty($message['member']['ip']))
			echo '
									<a href="', $scripturl, '?action=trackip;searchip=', $message['member']['ip'], '">', $message['member']['ip'], '</a> <a href="', $scripturl, '?action=helpadmin;help=see_admin_ip" onclick="return reqWin(this.href);" class="help">(?)</a>';
		// Or, should we show it because this is you?
		elseif ($message['can_see_ip'])
			echo '
									<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $message['member']['ip'], '</a>';
		// Okay, are you at least logged in?  Then we can show something about why IPs are logged...
		elseif (!$context['user']['is_guest'])
			echo '
									<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $txt[511], '</a>';
		// Otherwise, you see NOTHING!
		else
			echo '
									', $txt[511];

		echo '
								</td>
							</tr></table>';

		// Show the member's signature?
		if (!empty($message['member']['signature']) && empty($options['show_no_signatures']))
			echo '
							<hr width="100%" size="1" class="hrcolor" />
							<div class="signature">', $message['member']['signature'], '</div>';

		echo '
						</td>
					</tr>
				</table>
			</td></tr>
		</table>
	</td></tr>';
	}
	echo '
	<tr><td style="padding: 0 0 1px 0;"></td></tr>
</table>
<a name="lastPost"></a>';

	// As before, build the custom button right.
	if ($context['user']['is_logged'] && $settings['show_mark_read'])
		$normal_buttons['custom'] = array('text' => 'mark_unread', 'image' => 'markunread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=topic;t=' . $context['mark_unread_time'] . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']);
	elseif ($context['can_add_poll'])
		$normal_buttons['custom'] = array('text' => 'add_poll', 'image' => 'add_poll.gif', 'lang' => true, 'url' => $scripturl . '?action=editpoll;add;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']);

	echo '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="middletext">', $txt[139], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#top"><b>' . $txt['topbottom4'] . '</b></a>' : '', '</td>
		<td align="right" style="padding-right: 1ex;">
			<table cellpadding="0" cellspacing="0">
				<tr>
					', template_button_strip($normal_buttons, 'top', true), '
				</tr>
			</table>
		</td>
	</tr>
</table>';

	if ($context['show_spellchecking'])
		echo '
<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/spellcheck.js"></script>';

echo '
<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/xml_topic.js"></script>
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	quickReplyCollapsed = ', !empty($options['display_quick_reply']) && $options['display_quick_reply'] == 2 ? 'false' : 'true', ';

	smf_topic = ', $context['current_topic'], ';
	smf_start = ', $context['start'], ';
	smf_show_modify = ', $settings['show_modify'] ? '1' : '0', ';

	// On quick modify, this is what the body will look like.
	var smf_template_body_edit = \'<div id="error_box" style="padding: 4px; color: red;"></div><textarea class="editor" name="message" rows="12" style="width: 94%; margin-bottom: 10px;">%body%</textarea><br /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input type="hidden" name="topic" value="', $context['current_topic'], '" /><input type="hidden" name="msg" value="%msg_id%" /><div style="text-align: center;"><input type="submit" name="post" value="', $txt[10], '" onclick="return modify_save(\\\'' . $context['session_id'] . '\\\');" accesskey="s" />&nbsp;&nbsp;', $context['show_spellchecking'] ? '<input type="button" value="' . $txt['spell_check'] . '" onclick="spellCheck(\\\'quickModForm\\\', \\\'message\\\');" />&nbsp;&nbsp;' : '', '<input type="submit" name="cancel" value="', $txt['modify_cancel'], '" onclick="return modify_cancel();" /></div>\';

	// And this is the replacement for the subject.
	var smf_template_subject_edit = \'<input type="text" name="subject" value="%subject%" size="60" style="width: 99%;"  maxlength="80" />\';

	// Restore the message to this after editing.
	var smf_template_body_normal = \'%body%\';
	var smf_template_subject_normal = \'<a href="', $scripturl, '?topic=', $context['current_topic'], '.msg%msg_id%#msg%msg_id%">%subject%</a>\';
	var smf_template_top_subject = "', $txt[118], ': %subject% &nbsp;(', $txt[641], ' ', $context['num_views'], ' ', $txt[642], ')"

	if (window.XMLHttpRequest)
		showModifyButtons();
// ]]></script>
<table border="0" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 1ex;">
		<tr>';
	echo '
				<td valign="top" align="', !$context['right_to_left'] ? 'right' : 'left', '" class="nav"> ', $context['previous_next'], '</td>
		</tr>
</table>';

	$mod_buttons = array(
		'move' => array('test' => 'can_move', 'text' => 132, 'image' => 'admin_move.gif', 'lang' => true, 'url' => $scripturl . '?action=movetopic;topic=' . $context['current_topic'] . '.0'),
		'delete' => array('test' => 'can_delete', 'text' => 63, 'image' => 'admin_rem.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt[162] . '\');"', 'url' => $scripturl . '?action=removetopic2;topic=' . $context['current_topic'] . '.0;sesc=' . $context['session_id']),
		'lock' => array('test' => 'can_lock', 'text' => empty($context['is_locked']) ? 'smf279' : 'smf280', 'image' => 'admin_lock.gif', 'lang' => true, 'url' => $scripturl . '?action=lock;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']),
		'sticky' => array('test' => 'can_sticky', 'text' => empty($context['is_locked']) ? 'smf277' : 'smf278', 'image' => 'admin_sticky.gif', 'lang' => true, 'url' => $scripturl . '?action=sticky;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']),
		'merge' => array('test' => 'can_merge', 'text' => 'smf252', 'image' => 'merge.gif', 'lang' => true, 'url' => $scripturl . '?action=mergetopics;board=' . $context['current_board'] . '.0;from=' . $context['current_topic']),
		'remove_poll' => array('test' => 'can_remove_poll', 'text' => 'poll_remove', 'image' => 'admin_remove_poll.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['poll_remove_warn'] . '\');"', 'url' => $scripturl . '?action=removepoll;topic=' . $context['current_topic'] . '.' . $context['start']),
		'calendar' => array('test' => 'calendar_post', 'text' => 'calendar37', 'image' => 'linktocal.gif', 'lang' => true, 'url' => $scripturl . '?action=post;calendar;msg=' . $context['topic_first_message'] . ';topic=' . $context['current_topic'] . '.0;sesc=' . $context['session_id']),
	);

	if ($context['can_remove_post'] && !empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1)
		$mod_buttons[] = array('text' => 'quickmod_delete_selected', 'image' => 'delete_selected.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['quickmod_confirm'] . '\');" id="quickmodSubmit"', 'url' => 'javascript:document.quickModForm.submit();');

	echo '
	<table cellpadding="0" cellspacing="0" border="0" style="margin-left: 1ex;">
		<tr>
			', template_button_strip($mod_buttons, 'bottom') , '
		</tr>
	</table>';

	if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $context['can_remove_post'])
		echo '
	<input type="hidden" name="sc" value="', $context['session_id'], '" />
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		document.getElementById("quickmodSubmit").style.display = "none";
	// ]]></script>';
	echo '
</form>';

	echo '
<div class="tborder"><div class="titlebg2" style="padding: 4px;" align="', !$context['right_to_left'] ? 'right' : 'left', '">
	<form action="', $scripturl, '" method="get" style="padding:0; margin: 0;">
		<span class="smalltext">' . $txt[160] . ':</span>
		<select name="jumpto" id="jumpto" onchange="if (this.selectedIndex > 0 &amp;&amp; this.options[this.selectedIndex].value) window.location.href = smf_scripturl + this.options[this.selectedIndex].value.substr(smf_scripturl.indexOf(\'?\') == -1 || this.options[this.selectedIndex].value.substr(0, 1) != \'?\' ? 0 : 1);">
			<option value="">' . $txt[251] . ':</option>';
	foreach ($context['jump_to'] as $category)
	{
		echo '
			<option value="" disabled="disabled">-----------------------------</option>
			<option value="#', $category['id'], '">', $category['name'], '</option>
			<option value="" disabled="disabled">-----------------------------</option>';
		foreach ($category['boards'] as $board)
			echo '
			<option value="?board=', $board['id'], '.0"', $board['is_current'] ? ' selected="selected"' : '', '> ' . str_repeat('==', $board['child_level']) . '=> ' . $board['name'] . '</option>';
	}
	echo '
		</select>&nbsp;
		<input type="button" value="', $txt[161], '" onclick="if (this.form.jumpto.options[this.form.jumpto.selectedIndex].value) window.location.href = \'', $scripturl, '\' + this.form.jumpto.options[this.form.jumpto.selectedIndex].value;" />
	</form>
</div></div>';

	echo '<br />';

	if ($context['can_reply'] && !empty($options['display_quick_reply']))
	{
		echo '
<a name="quickreply"></a>
<form action="', $scripturl, '?action=post2" method="post" name="postmodify" id="postmodify" onsubmit="submitonce(this);" style="margin: 0;">
	<table border="0" cellspacing="1" cellpadding="3" class="bordercolor" width="100%" style="clear: both;">
			<tr>
				<td colspan="2" class="catbg"><a href="javascript:swapQuickReply();"><img src="', $settings['images_url'], '/', $options['display_quick_reply'] == 2 ? 'collapse' : 'expand', '.gif" alt="+" id="quickReplyExpand" /></a> <a href="javascript:swapQuickReply();">', $txt['quick_reply_1'], '</a></td>
			</tr>
			<tr id="quickReplyOptions"', $options['display_quick_reply'] == 2 ? '' : ' style="display: none"', '>
				<td class="windowbg" width="25%" valign="top">', $txt['quick_reply_2'], $context['is_locked'] ? '<br /><br /><b>' . $txt['quick_reply_warning'] . '</b>' : '', '</td>
				<td class="windowbg" width="75%" align="left"><table border="0" cellspacing="0" cellpadding="0" width="100%">
				', template_enhanced_quick_reply() ,'
				<tr>
					<td colspan="2">
						<input type="submit" name="post" value="' . $txt[105] . '" onclick="return submitThisOnce(this);" accesskey="s" tabindex="2" />
						<input type="submit" name="preview" value="' . $txt[507] . '" onclick="return submitThisOnce(this);" accesskey="p" tabindex="4" />';
		if ($context['show_spellchecking'])
			echo '
						<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'postmodify\', \'message\');" />';
		echo '
						<input type="hidden" name="topic" value="' . $context['current_topic'] . '" />
						<input type="hidden" name="subject" value="' . $txt['response_prefix'] . $context['subject'] . '" />
						<input type="hidden" name="icon" value="xx" />
						<input type="hidden" name="notify" value="', $context['is_marked_notify'] || !empty($options['auto_notify']) ? '1' : '0', '" />
						<input type="hidden" name="goback" value="', empty($options['return_to_post']) ? '0' : '1', '" />
						<input type="hidden" name="num_replies" value="', $context['num_replies'], '" />
						<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
						<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
					</td>
				</tr>
				</table></td>
			</tr>
	</table>
</form>';

		if ($context['show_spellchecking'])
			echo '
<form name="spell_form" id="spell_form" method="post" target="spellWindow" action="', $scripturl, '?action=spellcheck"><input type="hidden" name="spellstring" value="" /></form>';
	}
}
// Show an enhanced quick reply box for those that want everything.
function template_enhanced_quick_reply()
{
	global $context, $modSettings, $settings, $txt, $options, $scripturl, $db_prefix;
	
      loadLanguage('Post');
	// Initialize smiley array...
	$context['smileys'] = array(
		'postform' => array(),
		'popup' => array(),
	);

	// Load smileys - don't bother to run a query if we're not using the database's ones anyhow.
	if (empty($modSettings['smiley_enable']) && $context['user']['smiley_set'] != 'none')
		$context['smileys']['postform'][] = array(
			'smileys' => array(
				array('code' => ':)', 'filename' => 'smiley.gif', 'description' => $txt[287]),
				array('code' => ';)', 'filename' => 'wink.gif', 'description' => $txt[292]),
				array('code' => ':D', 'filename' => 'cheesy.gif', 'description' => $txt[289]),
				array('code' => ';D', 'filename' => 'grin.gif', 'description' => $txt[293]),
				array('code' => '>:(', 'filename' => 'angry.gif', 'description' => $txt[288]),
				array('code' => ':(', 'filename' => 'sad.gif', 'description' => $txt[291]),
				array('code' => ':o', 'filename' => 'shocked.gif', 'description' => $txt[294]),
				array('code' => '8)', 'filename' => 'cool.gif', 'description' => $txt[295]),
				array('code' => '???', 'filename' => 'huh.gif', 'description' => $txt[296]),
				array('code' => '::)', 'filename' => 'rolleyes.gif', 'description' => $txt[450]),
				array('code' => ':P', 'filename' => 'tongue.gif', 'description' => $txt[451]),
				array('code' => ':-[', 'filename' => 'embarrassed.gif', 'description' => $txt[526]),
				array('code' => ':-X', 'filename' => 'lipsrsealed.gif', 'description' => $txt[527]),
				array('code' => ':-\\', 'filename' => 'undecided.gif', 'description' => $txt[528]),
				array('code' => ':-*', 'filename' => 'kiss.gif', 'description' => $txt[529]),
				array('code' => ':\'(', 'filename' => 'cry.gif', 'description' => $txt[530])
			),
			'last' => true,
		);
	elseif ($context['user']['smiley_set'] != 'none')
	{
		if (($temp = cache_get_data('posting_smileys', 480)) == null)
		{
			$request = db_query("
				SELECT code, filename, description, smileyRow, hidden
				FROM {$db_prefix}smileys
				WHERE hidden IN (0, 2)
				ORDER BY smileyRow, smileyOrder", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
			{
				$row['code'] = htmlspecialchars($row['code']);
				$row['filename'] = htmlspecialchars($row['filename']);
				$row['description'] = htmlspecialchars($row['description']);

				$context['smileys'][empty($row['hidden']) ? 'postform' : 'popup'][$row['smileyRow']]['smileys'][] = $row;
			}
			mysql_free_result($request);

			cache_put_data('posting_smileys', $context['smileys'], 480);
		}
		else
			$context['smileys'] = $temp;
	}

	// Clean house... add slashes to the code for javascript.
	foreach (array_keys($context['smileys']) as $location)
	{
		foreach ($context['smileys'][$location] as $j => $row)
		{
			$n = count($context['smileys'][$location][$j]['smileys']);
			for ($i = 0; $i < $n; $i++)
			{
				$context['smileys'][$location][$j]['smileys'][$i]['code'] = addslashes($context['smileys'][$location][$j]['smileys'][$i]['code']);
				$context['smileys'][$location][$j]['smileys'][$i]['js_description'] = addslashes($context['smileys'][$location][$j]['smileys'][$i]['description']);
			}

			$context['smileys'][$location][$j]['smileys'][$n - 1]['last'] = true;
		}
		if (!empty($context['smileys'][$location]))
			$context['smileys'][$location][count($context['smileys'][$location]) - 1]['last'] = true;
	}
	$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $context['user']['smiley_set'];

	// Allow for things to be overridden.
	if (!isset($context['post_box_columns']))
		$context['post_box_columns'] = 75;
	if (!isset($context['post_box_rows']))
		$context['post_box_rows'] = 7;
	if (!isset($context['post_box_name']))
		$context['post_box_name'] = 'message';
	if (!isset($context['post_form']))
		$context['post_form'] = 'postmodify';

	// Generate a list of buttons that shouldn't be shown - this should be the fastest way to do this.
	if (!empty($modSettings['disabledBBC']))
	{
		$disabled_tags = explode(',', $modSettings['disabledBBC']);
		foreach ($disabled_tags as $tag)
			$context['disabled_tags'][trim($tag)] = true;
	}

	// Assuming BBC code is enabled then print the buttons and some javascript to handle it.
	if (!empty($modSettings['enableBBC']) && !empty($settings['show_bbc']))
	{
		echo '
				<tr>
					<td align="right"></td>
					<td valign="middle">
						<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
							function bbc_highlight(something, mode)
							{
								something.style.backgroundImage = "url(" + smf_images_url + (mode ? "/bbc/bbc_hoverbg.gif)" : "/bbc/bbc_bg.gif)");
							}
						// ]]></script>';

		// The below array makes it dead easy to add images to this page. Add it to the array and everything else is done for you!
		$context['bbc_tags'] = array();
		$context['bbc_tags'][] = array(
			'bold' => array('code' => 'b', 'before' => '[b]', 'after' => '[/b]', 'description' => $txt[253]),
			'italicize' => array('code' => 'i', 'before' => '[i]', 'after' => '[/i]', 'description' => $txt[254]),
			'underline' => array('code' => 'u', 'before' => '[u]', 'after' => '[/u]', 'description' => $txt[255]),
			'strike' => array('code' => 's', 'before' => '[s]', 'after' => '[/s]', 'description' => $txt[441]),
			array(),
			'glow' => array('code' => 'glow', 'before' => '[glow=red,2,300]', 'after' => '[/glow]', 'description' => $txt[442]),
			'shadow' => array('code' => 'shadow', 'before' => '[shadow=red,left]', 'after' => '[/shadow]', 'description' => $txt[443]),
			'move' => array('code' => 'move', 'before' => '[move]', 'after' => '[/move]', 'description' => $txt[439]),
			array(),
			'pre' => array('code' => 'pre', 'before' => '[pre]', 'after' => '[/pre]', 'description' => $txt[444]),
			'left' => array('code' => 'left', 'before' => '[left]', 'after' => '[/left]', 'description' => $txt[445]),
			'center' => array('code' => 'center', 'before' => '[center]', 'after' => '[/center]', 'description' => $txt[256]),
			'right' => array('code' => 'right', 'before' => '[right]', 'after' => '[/right]', 'description' => $txt[446]),
			array(),
			'hr' => array('code' => 'hr', 'before' => '[hr]', 'description' => $txt[531]),
			array(),
			'size' => array('code' => 'size', 'before' => '[size=10pt]', 'after' => '[/size]', 'description' => $txt[532]),
			'face' => array('code' => 'font', 'before' => '[font=Verdana]', 'after' => '[/font]', 'description' => $txt[533]),
		);
		$context['bbc_tags'][] = array(
			'flash' => array('code' => 'flash', 'before' => '[flash=200,200]http://', 'after' => '[/flash]', 'description' => $txt[433]),
			'img' => array('code' => 'img', 'before' => '[img]http://', 'after' => '[/img]', 'description' => $txt[435]),
			'url' => array('code' => 'url', 'before' => '[url=http://', 'after' => ']', 'after' => '[/url]', 'description' => $txt[257]),
			'email' => array('code' => 'email', 'before' => '[email]', 'after' => '[/email]', 'description' => $txt[258]),
			'ftp' => array('code' => 'ftp', 'before' => '[ftp=ftp://', 'after' => ']', 'after' => '[/ftp]', 'description' => $txt[434]),
			array(),
			'table' => array('code' => 'table', 'before' => '[table][tr][td]', 'after' => '[/table]', 'description' => $txt[436]),
			'tr' => array('code' => 'td', 'before' => '[table][tr][td]', 'after' => '[/td][/tr][/table]', 'description' => $txt[449]),
			'td' => array('code' => 'td', 'before' => '[table][tr][td]', 'after' => '[/td][/tr][/table]', 'description' => $txt[437]),
			array(),
			'sup' => array('code' => 'sup', 'before' => '[sup]', 'after' => '[/sup]', 'description' => $txt[447]),
			'sub' => array('code' => 'sub', 'before' => '[sub]', 'after' => '[/sub]', 'description' => $txt[448]),
			'tele' => array('code' => 'tt', 'before' => '[tt]', 'after' => '[/tt]', 'description' => $txt[440]),
			array(),
			'code' => array('code' => 'code', 'before' => '[code]', 'after' => '[/code]', 'description' => $txt[259]),
			'quote' => array('code' => 'quote', 'before' => '[quote]', 'after' => '[/quote]', 'description' => $txt[260]),
			array(),
			'list' => array('code' => 'list', 'before' => '[list]\n[list][li]', 'after' => '[/li][/list]\n[list][li][/li][/list]\n[/list]', 'description' => $txt[261]),
		);

		// Here loop through the array, printing the images/rows/separators!
		foreach ($context['bbc_tags'][0] as $image => $tag)
		{
			// Is there a "before" part for this bbc button? If not, it can't be a button!!
			if (isset($tag['before']))
			{
				// Is this tag disabled?
				if (!empty($context['disabled_tags'][$tag['code']]))
					continue;

				// If there's no after, we're just replacing the entire selection in the post box.
				if (!isset($tag['after']))
					echo '<a href="javascript:void(0);" onclick="replaceText(\'', $tag['before'], '\', document.forms.', $context['post_form'], '.', $context['post_box_name'], '); return false;">';
				// On the other hand, if there is one we are surrounding the selection ;).
				else
					echo '<a href="javascript:void(0);" onclick="surroundText(\'', $tag['before'], '\', \'', $tag['after'], '\', document.forms.', $context['post_form'], '.', $context['post_box_name'], '); return false;">';

				// Okay... we have the link. Now for the image and the closing </a>!
				echo '<img onmouseover="bbc_highlight(this, true);" onmouseout="if (window.bbc_highlight) bbc_highlight(this, false);" src="', $settings['images_url'], '/bbc/', $image, '.gif" align="bottom" width="23" height="22" alt="', $tag['description'], '" title="', $tag['description'], '" style="background-image: url(', $settings['images_url'], '/bbc/bbc_bg.gif); margin: 1px 2px 1px 1px;" /></a>';
			}
			// I guess it's a divider...
			else
				echo '<img src="', $settings['images_url'], '/bbc/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />';
		}

		// Print a drop down list for all the colors we allow!
		if (!isset($context['disabled_tags']['color']))
			echo ' <select onchange="surroundText(\'[color=\' + this.options[this.selectedIndex].value.toLowerCase() + \']\', \'[/color]\', document.forms.', $context['post_form'], '.', $context['post_box_name'], '); this.selectedIndex = 0; document.forms.', $context['post_form'], '.', $context['post_box_name'], '.focus(document.forms.', $context['post_form'], '.', $context['post_box_name'], '.caretPos);" style="margin-bottom: 1ex;">
								<option value="" selected="selected">', $txt['change_color'], '</option>
								<option value="Black">', $txt[262], '</option>
								<option value="Red">', $txt[263], '</option>
								<option value="Yellow">', $txt[264], '</option>
								<option value="Pink">', $txt[265], '</option>
								<option value="Green">', $txt[266], '</option>
								<option value="Orange">', $txt[267], '</option>
								<option value="Purple">', $txt[268], '</option>
								<option value="Blue">', $txt[269], '</option>
								<option value="Beige">', $txt[270], '</option>
								<option value="Brown">', $txt[271], '</option>
								<option value="Teal">', $txt[272], '</option>
								<option value="Navy">', $txt[273], '</option>
								<option value="Maroon">', $txt[274], '</option>
								<option value="LimeGreen">', $txt[275], '</option>
							</select>';
		echo '<br />';

		// Print the buttom row of buttons!
		foreach ($context['bbc_tags'][1] as $image => $tag)
		{
			if (isset($tag['before']))
			{
				// Is this tag disabled?
				if (!empty($context['disabled_tags'][$tag['code']]))
					continue;

				// If there's no after, we're just replacing the entire selection in the post box.
				if (!isset($tag['after']))
					echo '<a href="javascript:void(0);" onclick="replaceText(\'', $tag['before'], '\', document.forms.', $context['post_form'], '.', $context['post_box_name'], '); return false;">';
				// On the other hand, if there is one we are surrounding the selection ;).
				else
					echo '<a href="javascript:void(0);" onclick="surroundText(\'', $tag['before'], '\', \'', $tag['after'], '\', document.forms.', $context['post_form'], '.', $context['post_box_name'], '); return false;">';

				// Okay... we have the link. Now for the image and the closing </a>!
				echo '<img onmouseover="bbc_highlight(this, true);" onmouseout="if (window.bbc_highlight) bbc_highlight(this, false);" src="', $settings['images_url'], '/bbc/', $image, '.gif" align="bottom" width="23" height="22" alt="', $tag['description'], '" title="', $tag['description'], '" style="background-image: url(', $settings['images_url'], '/bbc/bbc_bg.gif); margin: 1px 2px 1px 1px;" /></a>';
			}
			// I guess it's a divider...
			else
				echo '<img src="', $settings['images_url'], '/bbc/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />';
		}

		echo '
					</td>
				</tr>';
	}

	// Now start printing all of the smileys.
	if (!empty($context['smileys']['postform']))
	{
		echo '
				<tr>
					<td align="right"></td>
					<td valign="middle">';

		// Show each row of smileys ;).
		foreach ($context['smileys']['postform'] as $smiley_row)
		{
			foreach ($smiley_row['smileys'] as $smiley)
				echo '
						<a href="javascript:void(0);" onclick="replaceText(\' ', $smiley['code'], '\', document.forms.', $context['post_form'], '.', $context['post_box_name'], '); return false;"><img src="', $settings['smileys_url'], '/', $smiley['filename'], '" align="bottom" alt="', $smiley['description'], '" title="', $smiley['description'], '" /></a>';

			// If this isn't the last row, show a break.
			if (empty($smiley_row['last']))
				echo '<br />';
		}

		// If the smileys popup is to be shown... show it!
		if (!empty($context['smileys']['popup']))
			echo '
						<a href="javascript:moreSmileys();">[', $txt['more_smileys'], ']</a>';

		echo '
					</td>
				</tr>';
	}

	// If there are additional smileys then ensure we provide the javascript for them.
	if (!empty($context['smileys']['popup']))
	{
		echo '
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					var smileys = [';

		foreach ($context['smileys']['popup'] as $smiley_row)
		{
			echo '
						[';
			foreach ($smiley_row['smileys'] as $smiley)
			{
				echo '
							["', $smiley['code'], '","', $smiley['filename'], '","', $smiley['js_description'], '"]';
				if (empty($smiley['last']))
					echo ',';
			}

			echo ']';
			if (empty($smiley_row['last']))
				echo ',';
		}

		echo '];
					var smileyPopupWindow;
	
					function moreSmileys()
					{
						var row, i;
	
						if (smileyPopupWindow)
							smileyPopupWindow.close();
	
						smileyPopupWindow = window.open("", "add_smileys", "toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=220,resizable=yes");
						smileyPopupWindow.document.write(\'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n<html>\');
						smileyPopupWindow.document.write(\'\n\t<head>\n\t\t<title>', $txt['more_smileys_title'], '</title>\n\t\t<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />\n\t</head>\');
						smileyPopupWindow.document.write(\'\n\t<body style="margin: 1ex;">\n\t\t<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">\n\t\t\t<tr class="titlebg"><td align="left">', $txt['more_smileys_pick'], '</td></tr>\n\t\t\t<tr class="windowbg"><td align="left">\');
	
						for (row = 0; row < smileys.length; row++)
						{
							for (i = 0; i < smileys[row].length; i++)
							{
								smileys[row][i][2] = smileys[row][i][2].replace(/"/g, \'&quot;\');
								smileyPopupWindow.document.write(\'<a href="javascript:void(0);" onclick="window.opener.replaceText(&quot; \' + smileys[row][i][0] + \'&quot;, window.opener.document.forms.', $context['post_form'], '.', $context['post_box_name'], '); window.focus(); return false;"><img src="', $settings['smileys_url'], '/\' + smileys[row][i][1] + \'" alt="\' + smileys[row][i][2] + \'" title="\' + smileys[row][i][2] + \'" style="padding: 4px;" border="0" /></a> \');
							}
							smileyPopupWindow.document.write("<br />");
						}
	
						smileyPopupWindow.document.write(\'</td></tr>\n\t\t\t<tr><td align="center" class="windowbg"><a href="javascript:window.close();\\">', $txt['more_smileys_close_window'], '</a></td></tr>\n\t\t</table>\n\t</body>\n</html>\');
						smileyPopupWindow.document.close();
					}
				// ]]></script>';
	}

	// Finally the most important bit - the actual text box to write in!
	echo '
				<tr>
					<td valign="top" align="right"></td>
					<td>
						<textarea class="editor" name="', $context['post_box_name'], '" rows="', $context['post_box_rows'], '" cols="', $context['post_box_columns'], '" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" onchange="storeCaret(this);" tabindex="6"></textarea>
					</td>
				</tr>';
}
?>