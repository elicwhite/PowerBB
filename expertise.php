<?php
//----------------------------------------------
// PowerBB
//----------------------------------------------
// All code is copyright to Power Software
// unless mentioned otherwise. This code
// may NOT be reproduced, or distributed
// by any means, unless you have explicit
// written permission from Power Software.
// Some code is derived from early versions
// of PunBB.
//-----------------------------------------------
// Copyright as of 2006
// All rights reserved
//-----------------------------------------------

define('FORUM_ROOT', './');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/expertise.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/profile.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/invitation.php';
$id = isset($_POST['id']) ? intval($_POST['id']) : $forum_user['id'];
$id = isset($_GET['id']) ? intval($_GET['id']) : $forum_user['id'];
if ($id < 2) message($lang_common['No permission']);
if ($forum_user['id'] != $id)
{
	if ($forum_user['g_id'] == USER_MEMBER)
	{
		$EXPERTISE_VIEWER = "regular";
	}
	else if ($forum_user['g_id'] > USER_MOD)
	{
		message($lang_common['No permission']);
	}
	else if ($forum_user['g_id'] < USER_GUEST)
	{
		$result = $db->query('SELECT group_id FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
		{
			message($lang_common['Bad request']);
		}
		if ($configuration['p_mod_edit_users'] == '0' || $db->result($result) < USER_GUEST)
		{
			$EXPERTISE_VIEWER = "regular";
		}
		else
		{
			$EXPERTISE_VIEWER = "moderator";
		}
	}
}
else
{
	$EXPERTISE_VIEWER = "self";
}
$result = $db->query('SELECT u.username, u.id, u.realname, g.g_id, g.g_user_title FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) message($lang_common['Bad request']);
$user = $db->fetch_assoc($result);
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if (isset($_GET['tag']))
	{
		$look_up_this_tag = intval(trim($_GET['tag']));
		$users_tagged = array();
		$sql = "SELECT l.taggee_id, count(l.taggee_id) as count, u.username FROM ". $db->prefix.'expertise_links' ." l, ".$db->prefix."users u WHERE l.tag_id = ".$look_up_this_tag." AND l.confirmed = '1' AND l.taggee_id = u.id GROUP BY l.taggee_id";
		$result = $db->query($sql) or error('Could not get tagged users from table '. $db->prefix.'expertise_links' .'', __FILE__, __LINE__, $db->error()) or die (mysql_error());
		while ($row = $db->fetch_assoc($result))
		{
			$users_tagged[$row['taggee_id']]['taggee_id'] = $row['taggee_id'];
			$users_tagged[$row['taggee_id']]['count'] = $row['count'];
			$users_tagged[$row['taggee_id']]['username'] = $row['username'];
		}
		$sql = "SELECT name FROM ". $db->prefix.'expertise_tags' ." WHERE id = ".$look_up_this_tag."";
		$result = $db->query($sql) or error('Could not get tag name from table '. $db->prefix.'expertise_tags' .'', __FILE__, __LINE__, $db->error());
		while ($row = $db->fetch_assoc($result))
		{
			$tag_of_interest = $row['name'];
		}
	}
	$expertise_tags = array();
	$sql = "SELECT DISTINCT t.id, t.name FROM ". $db->prefix.'expertise_tags' ." t, ". $db->prefix.'expertise_links' ." l WHERE (l.tagger_id = ". $user['id'] ." OR l.taggee_id = ". $user['id'] .") AND t.id = l.tag_id";
	$result = $db->query($sql) or error('Could not get tag data from tables '. $db->prefix.'expertise_tags' . ' and '. $db->prefix.'expertise_links' .'', __FILE__, __LINE__, $db->error());
	while ($row = $db->fetch_assoc($result))
	{
		$expertise_tags[$row['id']] = $row['name'];
	}
	$expertise_users = array();
	$sql = "SELECT DISTINCT u.id, u.username FROM ".$db->prefix."users u, ". $db->prefix.'expertise_links' ." l WHERE (l.taggee_id = ". $user['id'] .") AND u.id = l.tagger_id";
	$result = $db->query($sql) or error('Could not get tagging users data from tables users and '. $db->prefix.'expertise_links' .'', __FILE__, __LINE__, $db->error());
	while ($row = $db->fetch_assoc($result))
	{
		$expertise_users[$row['id']] = $row['username'];
	}
	$expertise_incoming = array();
	$sql = 'SELECT tagger_id, tag_id, taggee_id, confirmed FROM ' . $db->prefix.'expertise_links' . ' WHERE taggee_id = ' . $user['id'];
	$result = $db->query($sql) or error('Could not get tagger data from table '. $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
	while ($row = $db->fetch_assoc($result))
	{
		$expertise_incoming[$row['tagger_id']][] = $row;
	}
	$expertise_self = array();
	$sql = 'SELECT tagger_id, tag_id, taggee_id FROM ' . $db->prefix.'expertise_links' . ' WHERE taggee_id = ' . $user['id'] . ' AND tagger_id = ' . $user['id'];
	$result = $db->query($sql) or error('Could not get self-tagging data from table '. $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
	while ($row = $db->fetch_assoc($result))
	{
		array_push($expertise_self, $row['tag_id']);
	}
	$expertise_allothers = array();
	$sql = "SELECT tag_id, count(tag_id) as howmany FROM ". $db->prefix.'expertise_links' ." WHERE taggee_id = '".$user['id']."' AND confirmed = '1' GROUP BY tag_id ORDER BY howmany DESC";
	$result = $db->query($sql) or error('Could not get all-others data from table '. $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
	while ($row = $db->fetch_assoc($result))
	{
		$expertise_allothers[$row['tag_id']] = $row['howmany'];
	}
	$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_common['Profile'];
	require FORUM_ROOT.'header.php';
//	if ($EXPERTISE_VIEWER == "self" || $EXPERTISE_VIEWER == "moderator") 
generate_profile_menu('expertise');
?>
<?php
	if (isset($_GET['tag']))
	{
?>
		<div class="blockform">
			<h2><span><?php echo $lang_profile['Section expertise'] ?> - Tag </span></h2>
			<div class="box">
				<form id="expertise" method="post" action="expertise.php?id=<?php echo $id ?>">
					<div class="inform">
						<fieldset>
							<legend><?php echo $lang_expertise['Tag - Who']; ?></legend>
							<div class="infldset">
								<p><?php echo $lang_expertise['Tag - Who - Info']; ?></p>
								<div class="rbox">
<?php
									foreach ($users_tagged as $one)
									{
												$users_table .= "<tr><td width=\"10\">";
												$users_table .= $one['count'];
												$users_table .= "</td><td>";
												$users_table .= "<a href=\"expertise.php?id=".$one['taggee_id']."\">".$one['username']."</a>";
												$users_table .= "</td></tr>";
												$users_count++;
									}
									if ($users_count < 1)
									{
										echo "<br />*** ".$lang_expertise['Tag - Who - None']." ***<br /><br />";
									}
									else
									{
										echo "<h5>$tag_of_interest</h5>";
										echo "<table border=\"0\" width=\"100\">\n";
										echo "<tr><td>Count</td><td>Username</td></tr>";
										echo $users_table;
										echo "</table>\n";
									}
?>
								</div>
							</div>
						</fieldset>
					</div>
				</form>
			</div>
			<div class="clearer"></div>
		</div>
<?php
	}
	else
	{
?>	
		<div class="blockform">
			<h2><span><?php echo convert_htmlspecialchars($user['username']) ?> - <?php echo $lang_profile['Section expertise'] ?></span></h2>
			<div class="box">
				<form id="expertise" method="post" action="expertise.php?id=<?php echo $id ?>">
					<div>
						<input type="hidden" name="form_sent" value="1" />
					</div>
					<?php
						if ($EXPERTISE_VIEWER != "self")
						{
							echo "<div class=\"inform\">";
							echo "<p><a href=\"expertise.php?id=".$forum_user['id']."\">".$lang_expertise['Another - Link Back']."</a></p>"; 
							echo "<p><br /><a href=\"profile.php?id=".$user['id']."\">".$lang_expertise['Another - Link To Main Profile']."</a></p>"; 
							echo "</div>\n";
							echo "<div class=\"inform\"></div>\n";
						}
					?>
					<div class="inform">
						<fieldset>
							<legend>
<?php
								if ($EXPERTISE_VIEWER == "self"){ echo $lang_expertise['Self - Expertise Claimed']; }
								else{ echo $lang_expertise['Another - Expertise Claimed']; }
?>
							</legend>
							<div class="infldset">
								<p><?php
										if ($EXPERTISE_VIEWER == "self"){ echo $lang_expertise['Self - Expertise Claimed Info']; }
										else{ echo $lang_expertise['Another - Expertise Claimed Info']; }
									?></p>
								<div class="rbox">
									<?php if ($EXPERTISE_VIEWER == "self" || $EXPERTISE_VIEWER == "moderator"){ ?>
									<label><input class="textbox" type="text" name="<?php
									
										if ($EXPERTISE_VIEWER == "moderator")
										{
											echo "grant_self_expertise";
										}
										else
										{
											echo "self_expertise";
										}
									
									?>" size="55" value="<?php
										foreach ($expertise_self as $one)
										{
											echo convert_htmlspecialchars($expertise_tags[$one]);
											echo " ";
										}
										?>"><br /><?php echo $lang_expertise['Tags - Delimited']; ?></label>
									<?php }
									else { ?>
									<label><?php
										foreach ($expertise_self as $one)
										{
											$self_listing_table .= "<a href=\"expertise.php?tag=".$one."\">".convert_htmlspecialchars($expertise_tags[$one])."</a> ";
											$noedit_count++;
										}
										if ($noedit_count < 1)
										{
											echo "<br />*** ".$lang_expertise['Another - Expertise Claimed - None']." ***<br /><br />";
										}
										else
										{
											echo "<table border=\"0\"><tr><td>\n";
											echo $self_listing_table;
											echo "</td><tr></table>\n";
										}
										?></label>
									<?php } ?>
								</div>
							</div>
						</fieldset>
					</div>
					<?php if ($EXPERTISE_VIEWER == "self"){ ?>
					<div class="inform">
						<fieldset>
							<legend><?php echo $lang_expertise['Self - Expertise Received']; ?></legend>
							<div class="infldset">
								<p><?php echo $lang_expertise['Self - Expertise Received Info']; ?></p>
								<div class="rbox">
	<?php echo "<br />";
									$granted_per_user = 0;
									foreach ($expertise_incoming as $incominguserid => $linkinfo)
									{
										if ($incominguserid != $user['id'])
										{
											foreach ($linkinfo as $one => $two)
											{
												$granted_per_user++;
												$granted_table .= "<tr>";
												if ($granted_per_user == 1)
												{
													$granted_table .= "<td width=\"10\"><a href=\"expertise.php?id=$incominguserid\">".$expertise_users[$incominguserid]."</a></td>";
												}
												else
												{
													$granted_table .= "<td width=\"10\"> </td>";
												}
												$granted_table .= "<td width=\"10\">";
												$granted_table .= "<input type=\"checkbox\" name=\"confirmation[".$incominguserid."][".$two['tag_id']."]\" value=\"1\"";
												if ($two['confirmed']==1){ $granted_table .= " checked";}
												$granted_table .= ">";
												$granted_table .= "</td><td width=\"10\">";
												$granted_table .= "<input type=\"checkbox\" name=\"delete_tag[".$incominguserid."][".$two['tag_id']."]\" value=\"1\">";
												$granted_table .= "</td><td>";
												$granted_table .= "<a href=\"expertise.php?tag=".$two['tag_id']."\">".convert_htmlspecialchars($expertise_tags[$two['tag_id']])."</a>";
												$granted_table .= "</td></tr>\n";
											}
											$incoming_count++;
										}
										$granted_per_user = 0;
									}
									if ($incoming_count < 1)
									{
										echo "<br />*** ".$lang_expertise['Self - Expertise Received - None']." ***<br /><br />";
									}
									else
									{
										echo "<table border=\"0\" width=\"100\">\n";
										echo "<tr><td>Username</td><td>Confirmed</td><td>Delete?</td><td>Tag</td></tr>";
										echo $granted_table;
										echo "</table>\n";
									}
	?>
								</div>
							</div>
						</fieldset>
					</div>
					<?php
					}
					else
					{
					?>
					<div class="inform">
						<fieldset>
							<legend><?php echo $lang_expertise['Another - Expertise Granted']; ?></legend>
							<div class="infldset">
								<p><?php echo $lang_expertise['Another - Expertise Granted Info']; ?></p>
								<div class="rbox">
									<label><?php echo $lang_expertise['Another - Grant Expertise']; ?>
									<input type="text" class="textbox" name="grant_expertise" size="30" value=""><br />
									<?php echo $lang_expertise['Tags - Delimited']; ?></label>
	<?php echo "<br />";
									foreach ($expertise_incoming as $incominguserid => $linkinfo)
									{
										if ($incominguserid == $forum_user['id'])
										{
											foreach ($linkinfo as $one => $two)
											{
												$granted_table .= "<tr><td width=\"10\">";
												if ($two['confirmed']==1){ $granted_table .=  " &#10003; ";}else{$granted_table .=  " No ";}
												$granted_table .= "</td><td width=\"10\">";
												$granted_table .=  "<input type=\"checkbox\" name=\"delete_tag[".$two['tag_id']."]\" value=\"1\">";
												$granted_table .= "</td><td>";
												$granted_table .= "<a href=\"expertise.php?tag=".$two['tag_id']."\">".convert_htmlspecialchars($expertise_tags[$two['tag_id']])."</a>";
												$granted_table .= "</td></tr>";
												$granted_count++;
											}
										}
									}
									if ($granted_count < 1)
									{
										echo "*** ".$lang_expertise['Another - Expertise Granted - None']." ***<br /><br />";
									}
									else
									{
										echo "<table border=\"0\" width=\"100\">\n";
										echo "<tr><td>Confirmed</td><td>Delete?</td><td>Tag</td></tr>";
										echo $granted_table;
										echo "</table>\n";
									}
	?>
								</div>
							</div>
						</fieldset>
					</div>
					<?php
					}
					?>
					<div class="inform">
						<p>
						<input type="submit" class="b1" name="update" value="<?php echo $lang_common['Submit'] ?>" />
							<?php
								if ($EXPERTISE_VIEWER == "self")
								{
									echo $lang_profile['Instructions'];
								}
								else
								{
									echo $lang_expertise['Instructions'];
								}
								echo "<br />";
								echo "<br />";
							?>
						</p>							
					</div>
					<div class="inform">
						<fieldset>
							<legend><?php echo $lang_expertise['Granted By Others']; ?></legend>
							<div class="infldset">
								<p><?php echo $lang_expertise['Granted By Others Info']; ?></p>
								<div class="rbox">
	<?php echo "<br />\n";
									foreach ($expertise_allothers as $one => $howmany)
									{
										echo "$howmany <a href=\"expertise.php?tag=".$one."\">".convert_htmlspecialchars($expertise_tags[$one])."</a><br />\n";
										$allothers_count++;
									}
									if ($allothers_count < 1) echo "*** ".$lang_expertise['Granted By Others - None']." ***<br /><br />";
	?>
								</div>
							</div>
						</fieldset>
					</div>
				</form>
			</div><div class="clearer"></div>
		</div></div>
<?php
	}
?>
<?php
if ($EXPERTISE_VIEWER == "self" || $EXPERTISE_VIEWER == "moderator") echo "</div>\n";
?>
<?php
require FORUM_ROOT.'footer.php';
}
else 
{
		if (isset($_POST['self_expertise']))
		{
					$self_expertise = addslashes($_POST['self_expertise']);
					$self_expertise = trim($self_expertise);
						$split_tags = preg_split("/[\s]+/", $self_expertise);
						if ($split_tags[0] == ""){unset($split_tags);}
						foreach ($split_tags as $one => $two)
						{
							$new_tags[$two] = rtrim($two, ",");
							if ($new_tags[$two] == ""){unset($new_tags[$two]);}
						}
						$old_tags = array();
						$sql = "SELECT DISTINCT t.id, t.name FROM ". $db->prefix.'expertise_tags' ." t, ". $db->prefix.'expertise_links' ." l WHERE (l.tagger_id = ". $user['id'] ." AND l.taggee_id = ". $user['id'] .") AND t.id = l.tag_id";
						$result = $db->query($sql) or error('Could not get tag data from tables '. $db->prefix.'expertise_tags' . ' and '. $db->prefix.'expertise_links' .'', __FILE__, __LINE__, $db->error());
						while ($row = $db->fetch_assoc($result))
						{
							$old_tags[$row['id']] = $row['name'];
						}
						foreach ($old_tags as $one => $two)
						{
							if (in_array($two,$new_tags))
							{
								unset($new_tags[$two]);
							}
							else
							{
								$sql = "DELETE FROM " . $db->prefix.'expertise_links' . " WHERE tag_id = " . intval($one) . " AND " . " tagger_id = " . intval($id) . " AND " . " taggee_id = " . intval($id);
								$result = $db->query($sql) or error('Could not delete a self tag from table ' . $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
							}
						}
						foreach ($new_tags as $one => $two)
						{
							$sql = "INSERT IGNORE INTO " . $db->prefix.'expertise_tags' . " SET name='$two'";
							$result = $db->query($sql) or error('Could not insert a new tag 1 into table ' . $db->prefix.'expertise_tags' . '', __FILE__, __LINE__, $db->error());
							$sql = "SELECT id, name FROM " . $db->prefix.'expertise_tags' . " WHERE name = '$two'";
							$result = $db->query($sql) or error('Could not get tag id from table '. $db->prefix.'expertise_tags' . '', __FILE__, __LINE__, $db->error());
							while ($row = $db->fetch_assoc($result))
							{
								$fresh_id = $row['id'];
							}
							$sql = "INSERT IGNORE INTO " . $db->prefix.'expertise_links' . " SET tagger_id='$id', taggee_id='$id', tag_id='$fresh_id', confirmed='1', created_at=now(), confirmed_at=now()";
							$result = $db->query($sql) or error('Could not insert a new link into table ' . $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
							
						}
					$old_confirmations = array();
					$sql = 'SELECT tagger_id, tag_id, confirmed FROM ' . $db->prefix.'expertise_links' . ' WHERE taggee_id = ' . $user['id'];
					$result = $db->query($sql) or error('Could not get confirmation data from table '. $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
					while ($row = $db->fetch_assoc($result))
					{
						$old_confirmations[$row['tagger_id']][$row['tag_id']] = $row['confirmed'];
					}
					$new_confirmations = $_POST['confirmation'];
					foreach ($old_confirmations as $from_user => $from_tags)
					{
						if ($id != $from_user)
						{
							foreach ($from_tags as $tagno => $confirmed)
							{
								$new_confirmations[$from_user][$tagno] = isset($new_confirmations[$from_user][$tagno]) ? $new_confirmations[$from_user][$tagno] : 0;
								if ($confirmed == $new_confirmations[$from_user][$tagno])
								{
									unset($new_confirmations[$from_user][$tagno]);
								}
								else
								{
									$toggled = ($confirmed == "1") ? 0 : 1;
									if ($toggled == 1){ $new_confirm = ", confirmed_at = now() ";}
									$update_confirm = ($toggled == 1) ? "now() " : "NULL";
									$sql = "UPDATE " . $db->prefix.'expertise_links' . " SET " . "confirmed = $toggled" . " , confirmed_at = $update_confirm" . " WHERE tagger_id = " . intval($from_user) . " AND " . " tag_id = " . intval($tagno) . " AND " . " taggee_id = " . intval($id);
									$result = $db->query($sql) or error('Could not update confirmation data from table ' . $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
								}
							}
						}
					}
					if (isset($_POST['delete_tag']))
					{
						$new_deletions = $_POST['delete_tag'];
						foreach ($new_deletions as $one => $two)
						{
							foreach ($two as $deletetagid => $trash)
							{
								$sql = "DELETE FROM " . $db->prefix.'expertise_links' . " WHERE tag_id = " . intval($deletetagid) . " AND " . " tagger_id = " . intval($one) . " AND " . " taggee_id = " . intval($forum_user['id']);
								$result = $db->query($sql) or error('Could not delete a granted tag from table ' . $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());						
							}
						}
					}
		}
		else if (isset($_POST['grant_expertise']))
		{
			if (isset($_POST['grant_self_expertise'])){
					$grant_self_expertise = addslashes($_POST['grant_self_expertise']);
					$grant_self_expertise = trim($grant_self_expertise);
					$split_tags = preg_split("/[\s]+/", $grant_self_expertise);
					if ($split_tags[0] == ""){unset($split_tags);}
					foreach ($split_tags as $one => $two)
					{
						$new_tags[$two] = rtrim($two, ",");
						if ($new_tags[$two] == ""){unset($new_tags[$two]);}
					}
					$old_tags = array();
					$sql = "SELECT DISTINCT t.id, t.name FROM ". $db->prefix.'expertise_tags' ." t, ". $db->prefix.'expertise_links' ." l WHERE (l.tagger_id = ". $user['id'] ." AND l.taggee_id = ". $user['id'] .") AND t.id = l.tag_id";
					$result = $db->query($sql) or error('Could not get tag data from tables '. $db->prefix.'expertise_tags' . ' and '. $db->prefix.'expertise_links' .'', __FILE__, __LINE__, $db->error());
					while ($row = $db->fetch_assoc($result))
					{
						$old_tags[$row['id']] = $row['name'];
					}
					foreach ($old_tags as $one => $two)
					{
						if (in_array($two,$new_tags))
						{
							unset($new_tags[$two]);
						}
						else
						{
							$sql = "DELETE FROM " . $db->prefix.'expertise_links' . " WHERE tag_id = " . intval($one) . " AND " . " tagger_id = " . intval($id) . " AND " . " taggee_id = " . intval($id);
							$result = $db->query($sql) or error('Could not delete a self tag from table ' . $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
						}
					}
					foreach ($new_tags as $one => $two)
					{
						$sql = "INSERT IGNORE INTO " . $db->prefix.'expertise_tags' . " SET name='$two'";
						$result = $db->query($sql) or error('Could not insert a new tag woot into table ' . $db->prefix.'expertise_tags' . '', __FILE__, __LINE__, $db->error());
						$sql = "SELECT id, name FROM " . $db->prefix.'expertise_tags' . " WHERE name = '$two'";
						$result = $db->query($sql) or error('Could not get tag id from table '. $db->prefix.'expertise_tags' . '', __FILE__, __LINE__, $db->error());
						while ($row = $db->fetch_assoc($result))
						{
							$fresh_id = $row['id'];
						}
						$sql = "INSERT IGNORE INTO " . $db->prefix.'expertise_links' . " SET tagger_id='$id', taggee_id='$id', tag_id='$fresh_id', confirmed='1', created_at=now(), confirmed_at=now()";
						$result = $db->query($sql) or error('Could not insert a new link into table ' . $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
					}
				}
				$new_deletions = $_POST['delete_tag'];
				foreach ($new_deletions as $one => $two)
				{
					$sql = "DELETE FROM " . $db->prefix.'expertise_links' . " WHERE tag_id = " . intval($one) . " AND " . " tagger_id = " . intval($forum_user['id']) . " AND " . " taggee_id = " . intval($id);
					$result = $db->query($sql) or error('Could not delete a granted tag from table ' . $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
				}
				unset($new_tags);
				unset($old_tags);
				unset($split_tags);
				$grant_expertise = addslashes($_POST['grant_expertise']);
				$grant_expertise = trim($grant_expertise);
				$split_tags = preg_split("/[\s]+/", $grant_expertise);
				if ($split_tags[0] == ""){unset($split_tags);}
				foreach ($split_tags as $one => $two)
				{
					$new_tags[$two] = rtrim($two, ",");
					if ($new_tags[$two] == ""){unset($new_tags[$two]);}
				}
				$old_tags = array();
				$sql = "SELECT DISTINCT t.id, t.name FROM ". $db->prefix.'expertise_tags' ." t, ". $db->prefix.'expertise_links' ." l WHERE (l.tagger_id = ". $forum_user['id'] ." AND l.taggee_id = ". $user['id'] .") AND t.id = l.tag_id";
				$result = $db->query($sql) or error('Could not get tag data from tables '. $db->prefix.'expertise_tags' . ' and '. $db->prefix.'expertise_links' .'', __FILE__, __LINE__, $db->error());
				while ($row = $db->fetch_assoc($result))
				{
					$old_tags[$row['id']] = $row['name'];
				}
				foreach ($old_tags as $one => $two)
				{
					if (in_array($two,$new_tags))
					{
						unset($new_tags[$two]);
					}
				}
				foreach ($new_tags as $one => $two)
				{
					$sql = "INSERT IGNORE INTO " . $db->prefix.'expertise_tags' . " SET name='$two'";
					$result = $db->query($sql) or error('Could not insert a new tag 2 into table ' . $db->prefix.'expertise_tags' . '', __FILE__, __LINE__, $db->error());
					$sql = "SELECT id, name FROM " . $db->prefix.'expertise_tags' . " WHERE name = '$two'";
					$result = $db->query($sql) or error('Could not get tag id from table '. $db->prefix.'expertise_tags' . '', __FILE__, __LINE__, $db->error());
					while ($row = $db->fetch_assoc($result))
					{
						$fresh_id = $row['id'];
					}
					$sql = "INSERT IGNORE INTO " . $db->prefix.'expertise_links' . " SET tagger_id='".$forum_user['id']."', taggee_id='".$user['id']."', tag_id='$fresh_id', created_at=now()";
					$result = $db->query($sql) or error('Could not insert a new link into table ' . $db->prefix.'expertise_links' . '', __FILE__, __LINE__, $db->error());
				}
		}
	if ($EXPERTISE_VIEWER == "self")
	{
		redirect(FORUM_ROOT.'expertise.php?id='.$id, $lang_expertise['Self - Update']);
	}
	else
	{
		redirect(FORUM_ROOT.'expertise.php?id='.$id, $lang_expertise['Another - Update']);
	}
}
?>