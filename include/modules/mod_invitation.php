<?		
include_once(FORUM_ROOT . "include/modules/mod_invitation_db.php");
if(isset($_GET['action']) && $_GET['action'] == "makeInvitations")
{
	$form = extract_elements(array('anzahl'));
	if($forum_user['g_id'] == USER_ADMIN)
	{ 
		for($i = 0;$i < $form['anzahl'];$i++)
		{
			insertInvitation($id);
			usleep(100);
		}
	}
	redirect(FORUM_ROOT.'profile.php?section='.$section.'&amp;id='.$id, $lang_profile['Profile redirect']);
}
if(isset($_GET['action']) && $_GET['action'] == "sendInvitation")
{
	$code = getLastInvitation($id);
	$form = extract_elements(array('req_recipient','invitation_text'));
	if($forum_user['g_id'] != USER_ADMIN and $forum_user['id'] != $id)
	{
		error($lang_invitation['No Permission'], __FILE__, __LINE__);
	}
	else
	{ 
		$pp = split("/",$_SERVER['SCRIPT_NAME']);
		array_pop($pp);
		$act_link = "http://".$_SERVER['HTTP_HOST'].implode("/",$pp)."/register.php?invite=".$code;
		$mtext = $form['invitation_text'] . "\n".$lang_invitation['Forum Link']."<a href='".$act_link."'>".$act_link."</a>";
		mail($form['req_recipient'], $lang_invitation['Mail Subject Invitation'],$mtext,"From:".$forum_user['email']);
		updateInvitation($id,$code,$form['req_recipient'], $mtext);
		redirect(FORUM_ROOT.'profile.php?section='.$section.'&amp;id='.$id, $lang_invitation['Invitation redirect']);
     }
}
?>