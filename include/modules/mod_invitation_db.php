<?
function showInvitations($id)
{
	global $db,$forum_user;
	$result = $db->query("SELECT count(*) as CT FROM ".$db->prefix."invitations WHERE userid='" . $id . "' and recipient=''") or error('Unable to fetch Invitation information', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result) or $db->num_rows($result) == 0)
	{
		return 0;
	}
	else
	{
		list($num_invitations) = $db->fetch_row($result);
		return $num_invitations;
	}
}
 
function showInviter($id)
{
	global $db,$forum_user;
	$result = $db->query("SELECT username from ".$db->prefix."users WHERE id='" . $id . "'") or error('Unable to fetch Inviter information', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result) or $db->num_rows($result) == 0)
	{
		return 0;
	}
	else
	{
		list($inviter) = $db->fetch_row($result);
		return $inviter;
	}
}

function insertInvitation($id)
{
	global $db;
	$result = $db->query("insert into ".$db->prefix."invitations (userid,code,created) values(".intval($id).", '". md5(microtime()) ."', NOW())") or error('Unable to insert invitation', __FILE__, __LINE__, $db->error());
      return 1;
}

function updateInvitation($id,$code,$recipient, $mtext)
{
	global $db;
	$result = $db->query("update ".$db->prefix."invitations set recipient='" . $recipient . "', recipient_text='" . addslashes($mtext) . "', sent=NOW() where userid=".intval($id)." and code='". substr($code,0,32)."'")  or error('Unable to insert invitation', __FILE__, __LINE__, $db->error());
	return 1;
}
   
function getLastInvitation($id)
{
	global $db, $forum_user, $lang_invitation;
	$result = $db->query("SELECT code from ".$db->prefix."invitations WHERE userid='" . $id . "' and recipient=''") or error('Unable to fetch Invitation code', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result) or $db->num_rows($result) == 0)
	{
		message($lang_invitation['No code']);
	}
	else
	{
		list($code) = $db->fetch_row($result);
		return $code;
	}
}

function checkInvitation($email,$code)
{
	global $db;
	$result = $db->query("SELECT userid from ".$db->prefix."invitations WHERE recipient='" . $email . "' and code='" . $code . "' and used='00000000000000'") or error('Unable to fetch Invitation code', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result) or $db->num_rows($result) == 0)
	{
		return "No data";
	}
	else
	{
		list($userid) = $db->fetch_row($result);
		$result = $db->query("update  ".$db->prefix."invitations set used=NOW() where recipient='" . $email . "' and code='" . $code . "'") or error('Unable to update Invitation table', __FILE__, __LINE__, $db->error());
		return $userid;
	}
}

function massInvite($id, $amount)
{
      for($i = 0;$i < intval($amount);$i++)
	{
		insertInvitation($id);
	}
}
?>