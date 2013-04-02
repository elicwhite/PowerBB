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

define('ADMIN_CONSOLE', 1);
define('FORUM_ROOT', '../');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'include/common_admin.php';
define('LOG_PRUNE', 30);
$logtype = intval($_GET['type']);
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums) / Logs";
require FORUM_ROOT.'header.php';
generate_admin_menu('logs');
?>
<div class="blockform">
	<div class="tab-page" id="logsPane">
		<script type="text/javascript">var tabPane1 = new WebFXTabPane( document.getElementById( "logsPane" ), 1 )</script>
		<div class="tab-page" id="intro-page">
			<h2 class="tab">
				Help
			</h2>
			<script type="text/javascript">tabPane1.addTabPage( document.getElementById( "intro-page" ) );</script>
			<div class="box">
				<form>
					<div class="inform">
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
										<td width="100px"><img src="<?php echo FORUM_ROOT?>img/admin/bans.png" /></td>
										<td>
											<span>This plugin help the master administrator track the actions of the other admins.</span>
										</td>
								</tr>
							</table>
						</div>
					</div>
				</form>
			</div>
		</div>
		
		<br />
		<div class="tab-page" id="viewer-page">
			<h2 class="tab">
				Log Viewer
			</h2>
			<script type="text/javascript">tabPane1.addTabPage( document.getElementById( "viewer-page" ) );</script>
			<div class="box">
				<form id="userlist" method="get" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
					<input type="hidden" name="plugin" value="<?php echo $_GET['plugin'] ?>" />
						<div class="inform">
							<fieldset>
								<legend>Log Type</legend>
								<div class="infldset">
									<select name="type">
										<option value="-1" <?php echo ($logtype < 1) ? 'selected': ''; ?>>All Logs</option>
										<option value="1" <?php echo ($logtype == 1) ? 'selected': ''; ?>>Ban Add</option>
										<option value="2" <?php echo ($logtype == 2) ? 'selected': ''; ?>>Ban Update</option>
										<option value="3" <?php echo ($logtype == 3) ? 'selected': ''; ?>>Ban Removal</option>
										<option value="4" <?php echo ($logtype == 4) ? 'selected': ''; ?>>Category Add</option>
										<option value="5" <?php echo ($logtype == 5) ? 'selected': ''; ?>>Category Removal</option>
										<option value="6" <?php echo ($logtype == 6) ? 'selected': ''; ?>>Category Update</option>
										<option value="7" <?php echo ($logtype == 7) ? 'selected': ''; ?>>Censor Add</option>
										<option value="8" <?php echo ($logtype == 8) ? 'selected': ''; ?>>Censor Removal</option>
										<option value="9" <?php echo ($logtype == 9) ? 'selected': ''; ?>>Censor Update</option>
										<option value="10" <?php echo ($logtype == 10) ? 'selected': ''; ?>>Forum Add</option>
										<option value="11" <?php echo ($logtype == 11) ? 'selected': ''; ?>>Forum Removal</option>
										<option value="12" <?php echo ($logtype == 12) ? 'selected': ''; ?>>Forum Update</option>
										<option value="13" <?php echo ($logtype == 13) ? 'selected': ''; ?>>Group Add</option>
										<option value="14" <?php echo ($logtype == 14) ? 'selected': ''; ?>>Group Removal</option>
										<option value="15" <?php echo ($logtype == 15) ? 'selected': ''; ?>>Group Update</option>
										<option value="16" <?php echo ($logtype == 16) ? 'selected': ''; ?>>Options Update</option>
										<option value="17" <?php echo ($logtype == 17) ? 'selected': ''; ?>>Permissions Update</option>
										<option value="18" <?php echo ($logtype == 18) ? 'selected': ''; ?>>Rank Add</option>
										<option value="19" <?php echo ($logtype == 19) ? 'selected': ''; ?>>Rank Removal</option>
										<option value="20" <?php echo ($logtype == 20) ? 'selected': ''; ?>>Rank Update</option>
										<option value="21" <?php echo ($logtype == 21) ? 'selected': ''; ?>>Password Update</option>
										<option value="23" <?php echo ($logtype == 23) ? 'selected': ''; ?>>Usergroup Update</option>
										<option value="24" <?php echo ($logtype == 24) ? 'selected': ''; ?>>Mods Update</option>
										<option value="25" <?php echo ($logtype == 25) ? 'selected': ''; ?>>User Removal</option>
										<option value="26" <?php echo ($logtype == 26) ? 'selected': ''; ?>>User Update</option>
									</select>
								</div>
							</fieldset>
						</div>
						<p>
						<input type="submit" class="b1" name="view_logs" value="<?php echo $lang_common['Submit'] ?>" accesskey="s" />
						</p>
					</form>
				</div>
<?php
if (isset($_GET['view_logs']))
{
	if (defined('LOG_PRUNE')) $result = $db->query('DELETE FROM '.$db->prefix.'logs WHERE time<'.(time() - (LOG_PRUNE * 86400))) or error('Unable to prune log info', __FILE__, __LINE__, $db->error());
	if ($logtype > 0) $result = $db->query('SELECT COUNT(*) as mcount FROM '.$db->prefix.'logs WHERE type='.$logtype) or error('Unable to fetch log count', __FILE__, __LINE__, $db->error());
	else $result = $db->query('SELECT COUNT(*) as mcount FROM '.$db->prefix.'logs') or error('Unable to fetch log count', __FILE__, __LINE__, $db->error());
	$log_pages = ceil($db->result($result) / 25);
	$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $log_pages) ? 1 : intval($_GET['p']);
	$start_from = 25 * ($p - 1);
	$paging_links = 'Pages: '.paginate($log_pages, $p, $_SERVER['REQUEST_URI']);
	if ($logtype > 0) $result = $db->query('SELECT * FROM '.$db->prefix.'logs WHERE type='.$logtype.' ORDER By time DESC LIMIT '.$start_from.', 25') or error('Unable to fetch log info', __FILE__, __LINE__, $db->error());
	else $result = $db->query('SELECT * FROM '.$db->prefix.'logs ORDER By time DESC LIMIT '.$start_from.', 25') or error('Unable to fetch log info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
?>
				<h2 class="block2">
					<span>
						Log Results
					</span>
				</h2>
				<div class="box">
					<div class="inbox">
						<table cellspacing="0">
							<thead>
								<tr>
									<th class="tc2" scope="col">Page</th>
									<th class="tc2" scope="col">Action</th>
									<th class="tc2" scope="col">Who</th>
									<th class="tc2" scope="col">Time</th>
								</tr>
							</thead>
							<tbody>
<?php
		while ($row = $db->fetch_assoc($result))
		{
			$cur_page = stripslashes($row['page']); 
			$cur_username = stripslashes($row['username']); 
			$cur_ip = stripslashes($row['ip']); 
			$cur_time = format_time(stripslashes($row['time'])); 
			$cur_data = $row['data']; 
			$cur_typen = intval($row['type']);
			$dataset = array(); 
			$dataset = unserialize($cur_data);
			array_walk($dataset, 'array_trim');
			switch ($cur_typen)
			{
				case 1:
					$cur_action = "Ban Addition";
					$dataout = "Ban Username: " . $dataset[0];	
					$dataout .= "<br />Ban IP: " . $dataset[1];
					$dataout .= "<br />Ban Email: " . $dataset[2];
					$dataout .= "<br />Ban Reason: " . $dataset[3];
					$dataout .= "<br />Ban Expire: " . $dataset[4];
					break;
				case 3:
					$cur_action = "Ban Modification";
					$dataout = "Ban ID: " . $dataset[5];
					$dataout = "<br />New Ban Username: " . $dataset[0];	
					$dataout .= "<br />New Ban IP: " . $dataset[1];
					$dataout .= "<br />New Ban Email: " . $dataset[2];
					$dataout .= "<br />New Ban Reason: " . $dataset[3];
					$dataout .= "<br />New Ban Expire: " . $dataset[4];
					break;
				case 2:
					$cur_action = "Ban Removal";
					$dataout = "Ban ID: " . $dataset[4];
					$dataout = "<br />New Ban Username: " . $dataset[0];	
					$dataout .= "<br />New Ban IP: " . $dataset[1];
					$dataout .= "<br />New Ban Email: " . $dataset[2];
					$dataout .= "<br />New Ban Reason: " . $dataset[3];
					break;
				case 4:
					$cur_action = "Category Addition";
					$dataout = "Category Name: " . $dataset[0];
					break;
				case 5:
					$cur_action = "Category Removal";
					$dataout = "CatID: " . $dataset[1];
					$dataout .= "<br />Category Name: " . $dataset[0];
					break;
				case 6:
					$cur_action = "Category Modification";
					$dataout = "CatID: " . $dataset[2];
					$dataout .= "<br />New Cat Name: " . $dataset[0];
					$dataout .= "<br />New Cat Pos: " . $dataset[1];
					break;
				case 7:
					$cur_action = "Censor Addition";
					$dataout = "Censor Search: " . $dataset[0];
					$dataout .= "<br />Censor Replace: " . $dataset[1];
					break;
				case 8:
					$cur_action = "Censor Removal";
					$dataout = "CensorID: " . $dataset[1];
					$dataout .= "<br />Search Word: " . $dataset[0];
					break;
				case 9:
					$cur_action = "Category Modification";
					$dataout = "CensorID: " . $dataset[2];
					$dataout .= "<br />New Search Word: " . $dataset[0];
					$dataout .= "<br />New Replacement: " . $dataset[1];
					break;
				case 10:
					$cur_action = "Forum Addition";
					$dataout = "Add For Cat: " . $dataset[0];
					break;
				case 11:
					$cur_action = "Forum Removal";
					$dataout = "ForumID: " . $dataset[1];
					$dataout .= "<br />Forum Name: " . $dataset[0];
					break;
				case 12:
					$cur_action = "Forum Modification";
					$dataout = "ForumID: " . $dataset[5];
					$dataout .= "<br />New Forum Name: " . $dataset[0];
					$dataout .= "<br />New Description: " . $dataset[1];
					$dataout .= "<br />New Redirect URL: " . $dataset[2];
					$dataout .= "<br />New Sort By: " . $dataset[3];
					$dataout .= "<br />New Cat: " . $dataset[4];
					break;
				case 13:
					$cur_action = "Group Addition";
					$ReadBoard = (intval($dataset[2]) == 1) ? "Yes": "No";
					$PostReplies = (intval($dataset[3]) == 1) ? "Yes": "No";
					$PostTopics = (intval($dataset[4]) == 1) ? "Yes": "No";
					$EditPosts = (intval($dataset[5]) == 1) ? "Yes": "No";
					$DeletePosts = (intval($dataset[6]) == 1) ? "Yes": "No";
					$DeleteTopics = (intval($dataset[7]) == 1) ? "Yes": "No";
					$SetTitle = (intval($dataset[8]) == 1) ? "Yes": "No";
					$Search = (intval($dataset[9]) == 1) ? "Yes": "No";
					$SearchUsers = (intval($dataset[10]) == 1) ? "Yes": "No";
					$SubjectInterval = $dataset[11];
					$SearchInterval = $dataset[12];
					$PostFlood = $dataset[13];
					$SearchFlood = $dataset[14];
					$dataout = "Group Title: " . $dataset[0];
					$dataout .= "<br />User Title: " . $dataset[1];
					$dataout .= "<br />Can Read Boards: " . $ReadBoard;
					$dataout .= "<br />Can Post Replies: " . $PostReplies;
					$dataout .= "<br />Can Post Topics: " . $PostTopics;
					$dataout .= "<br />Can Edit Posts: " . $EditPosts;
					$dataout .= "<br />Can Delete Posts: " . $DeletePosts;
					$dataout .= "<br />Can Delete Topics: " . $DeleteTopics;
					$dataout .= "<br />Can Set Title: " . $SetTitle;
					$dataout .= "<br />Can Search: " . $Search;
					$dataout .= "<br />Can Search Users: " . $SearchUsers;
					$dataout .= "<br />Subject Change Interval: " . $SubjectInterval;
					$dataout .= "<br />Search Interval: " . $SearchInterval;
					$dataout .= "<br />Post Flood: " . $PostFlood;
					$dataout .= "<br />Search Flood: " . $SearchFlood;
					break;
				case 14:
					$cur_action = "Group Removal";
					$dataout = "GroupID: " . $dataset[1];
					$dataout .= "<br />Group Title: " . $dataset[0];
					break;
				case 15:
					$cur_action = "Group Modification";
					$ReadBoard = (intval($dataset[2]) == 1) ? "Yes": "No";
					$PostReplies = (intval($dataset[3]) == 1) ? "Yes": "No";
					$PostTopics = (intval($dataset[4]) == 1) ? "Yes": "No";
					$EditPosts = (intval($dataset[5]) == 1) ? "Yes": "No";
					$DeletePosts = (intval($dataset[6]) == 1) ? "Yes": "No";
					$DeleteTopics = (intval($dataset[7]) == 1) ? "Yes": "No";
					$SetTitle = (intval($dataset[8]) == 1) ? "Yes": "No";
					$Search = (intval($dataset[9]) == 1) ? "Yes": "No";
					$SearchUsers = (intval($dataset[10]) == 1) ? "Yes": "No";
					$SubjectInterval = $dataset[11];
					$SearchInterval = $dataset[12];
					$PostFlood = $dataset[13];
					$SearchFlood = $dataset[14];
					$dataout = "GroupID: " . $dataset[15];
					$dataout = "New Group Title: " . $dataset[0];
					$dataout .= "<br />New User Title: " . $dataset[1];
					$dataout .= "<br />Can Read Boards: " . $ReadBoard;
					$dataout .= "<br />Can Post Replies: " . $PostReplies;
					$dataout .= "<br />Can Post Topics: " . $PostTopics;
					$dataout .= "<br />Can Edit Posts: " . $EditPosts;
					$dataout .= "<br />Can Delete Posts: " . $DeletePosts;
					$dataout .= "<br />Can Delete Topics: " . $DeleteTopics;
					$dataout .= "<br />Can Set Title: " . $SetTitle;
					$dataout .= "<br />Can Search: " . $Search;
					$dataout .= "<br />Can Search Users: " . $SearchUsers;
					$dataout .= "<br />Subject Change Interval: " . $SubjectInterval;
					$dataout .= "<br />Search Interval: " . $SearchInterval;
					$dataout .= "<br />Post Flood: " . $PostFlood;
					$dataout .= "<br />Search Flood: " . $SearchFlood;
					break;
				case 16:
					$cur_action = "Options Modification";
					$dataout = "Option: " . $dataset[1];
					$dataout .= "<br />New Value: " . $dataset[0];
					break;
				case 17:
					$cur_action = "Permissions Modification";
					$dataout = "Option: " . $dataset[1];
					$dataout .= "<br />New Value: " . $dataset[0];
					break;
				case 18:
					$cur_action = "Rank Addition";
					$dataout = "Rank Name: " . $dataset[0];
					$dataout = "<br />Minimum Posts: " . $dataset[1];
					break;
				case 19:
					$cur_action = "Rank Removal";
					$dataout = "RankID: " . $dataset[1];
					$dataout .= "<br />Rank Name: " . $dataset[0];
					break;
				case 20:
					$cur_action = "Rank Modification";
					$dataout = "RankID: " . $dataset[2];
					$dataout .= "<br />New Rank Name: " . $dataset[0];
					$dataout .= "<br />New Minimum Posts: " . $dataset[1];
					break;
				case 21:
					$cur_action = "Pass Modification";
					$dataout = "Changed Password For: " . UserFromID($dataset[0]);
					break;
				case 23:
					$cur_action = "UserGroup Modification";
					$dataout = "Changed UserGroup For: " . UserFromID($dataset[1]);
					$dataout .= "<br />Group: " . GroupFromID($dataset[0]);
					break;
				case 24:
					$cur_action = "Moderator Modification";
					$dataout = "Changed Moderator For: " . UserFromID($dataset[1]);
					$dataout .= "<br />Forum: " . ForumFromID($dataset[0]);
					break;
				case 25:
					$cur_action = "User Removal";
					$dataout = "UserID: " . $dataset[1];
					$dataout .= "<br />User: " . $dataset[0];
					break;
				case 26:
					$cur_action = "User Modification";
					$dataout = "Changed User For: " . $dataset[6];
					$dataout .= "<br />New Username: " . $dataset[2];
					$dataout .= "<br />New Email: " . $dataset[4];
					$dataout .= "<br />New Num Posts: " . $dataset[3];
					$dataout .= "<br />New Note: " . $dataset[1];
					break;
			}
			?>
				<tr>
					<td class="tc2"><?php echo $cur_page."\n" ?></td>
					<td class="tc2"><?php echo $cur_action ?></td>
					<td class="tc2"><?php echo $cur_username . " (" . $cur_ip . ")" ?></td>
					<td class="tc2"><?php echo $cur_time ?></td>
				</tr>
<?php 		}
		echo "\t\t\t".'			</tbody>'."\n\t\t\t".'
							</table>'."\n\t\t".'
						</div>'."\n\t".'
					</div>'."\n".'
				</div>'."\n\n";
	?>
				<br />
				<div class="block">
					<div class="linksb">
						<div class="inbox">
							<p class="pagelink conl">
								<?php echo $paging_links ?>
							</p>
							<div class="clearer"></div>
						</div>
					</div>
				</div>
	<?php
	} else
	{?>
			<div class="block">
	<h2 class="block2"><span>Log Results</span></h2>
	<div class="box">
		<div align="center" class="inbox">
		No Matching Logs Found
		</div>
	</div>
	</div>
	 <?php
	}
}
function array_trim(&$data)
{ 
	$data = stripslashes($data);
	if (substr($data,0,1) == '\'') $data = substr_replace($data,'', 0, 1); 
	if (substr($data,-1,1) == '\'') $data = substr_replace($data,'', -1, 1); 
	$data = convert_htmlspecialchars($data); 
}
?>
<div class="clearer">
	</div>
</div>
<?php require FORUM_ROOT.'admin/admin_footer.php'; ?>