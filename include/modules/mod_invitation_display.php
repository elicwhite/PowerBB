<?
include_once(FORUM_ROOT.'include/modules/mod_invitation_db.php');
$valid_invitations = showInvitations($id);
if ($forum_user['g_set_title'] == '1') $title_field = '<label>'.$lang_common['Title'].'&nbsp;&nbsp;(<em>'.$lang_profile['Leave blank'].'</em>)<br /><input type="text" name="title" value="'.convert_htmlspecialchars($user['title']).'" size="30" maxlength="50" /><br /></label>'."\n";
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
require FORUM_ROOT.'header.php';
generate_profile_menu('invitation');
?>
	<div class="blockform">
		<h2><span><?php echo convert_htmlspecialchars($user['username']).' - '.$lang_invitation['Invitations'] ?></span></h2>
<?
if($valid_invitations > 0)
{
?>			
		<div class="box">
			<form id="invitation2" method="post" action="profile.php?action=sendInvitation&section=invitation&amp;id=<?php echo $id ?>">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_invitation['Send Invitation'] ?></legend>
						<div class="infldset"><?php echo  $lang_invitation['You have'] ?>
						 <span style="font-weight:bold"><?php echo $valid_invitations ?></span> <?php echo  $lang_invitation['Invitations left'] ?><br /><br />
							<input type="hidden" name="form_sent" value="1" />
							<label><?php echo $lang_invitation['Recipient'] ?><br /><input type="text" class="textbox"name="form[req_recipient]" value="" size="50" maxlength="40" /><br /></label>
							<label><?php echo $lang_invitation['Message'] ?><br /><textarea name="form[invitation_text]" cols="50" rows="8"></textarea><br /></label>
						</div>
					</fieldset>
				</div>
				<p><input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" />
			</form>
		</div>
<?
}
if($forum_user['g_id'] == USER_ADMIN &&  $valid_invitations == 0)
{ 
?>			
		<div class="box">
			<form id="invitation3" method="post" action="profile.php?action=makeInvitations&section=invitation&amp;id=<?php echo $id ?>">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_invitation['New Invitations'] ?></legend>
						<div class="infldset">
							<input type="hidden" name="form_sent" value="1" />
							<label><?php echo $lang_invitation['Number'] ?><br /><input type="text" name="form[anzahl]" class="textbox" value="" size="2" maxlength="2" /><br /></label>
						</div>
					</fieldset>
				</div>
				<p><input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" /></p>
			</form>
		</div>
<?
}  
?>	
</div>