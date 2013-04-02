<?php
if (!defined('IN_FORUM')) exit;
define('PLUGIN_LOADED', 1);
if(isset($_REQUEST['what']) && $_REQUEST['what'] == "userview")
{
	$result = $db->query('SELECT id from '.$db->prefix.'users WHERE email=\'' . $_REQUEST['email'] .'\'') or error('Could not find user for the email address '. $_REQUEST['email']);
	if ($db->num_rows($result))
	{
		$res = $db->fetch_assoc($result);
		Header('location:' . $configuration['o_base_url'] . '/profile.php?id='.$res['id']);
	}
	else 
	{
		error("No User with this email address");
	}
}

function insertInvitation($id)
{
  global $db;
  $result = $db->query('insert into '.$db->prefix.'invitations (userid,code,created) values('.intval($id).', \''. md5(microtime()) .'\', NOW())') or error('Could not insert Invitation code', __FILE__, __LINE__, $db->error());
      return 1;
      }

if(isset($_REQUEST['what']) && $_REQUEST['what'] == "recipients")  {
  $what = trim($_REQUEST['what']);
  $period = trim($_REQUEST['period']);
  $fromd = trim($_REQUEST['fromd']);
  $tod = trim($_REQUEST['tod']);
	if ($period == '' && ($fromd == '' or $tod == '')) message('You need to chose a period or set beginning and end date!');
   elseif($period == '' && !ereg("[0-9]{4}-[0-9]{2}-[0-9]{2}",$fromd)) message('Invalid beginning date!');
   elseif($period == '' && !ereg("[0-9]{4}-[0-9]{2}-[0-9]{2}",$tod)) message('Invalid end date!');
	if($period == 'today')  {
	  $start = date("Y-m-d")." 00:00:00";
	  $end = date("Y-m-d")." 23:59:59";
	  }	
	elseif($period == 'yesterday')  {
	  $start = date("Y-m-d", strtotime("yesterday"))." 00:00:00";
	  $end = date("Y-m-d", strtotime("yesterday"))." 23:59:59";
	  }	
	elseif($period == 'lastweek')  {
	  $start = date("Y-m-d", strtotime("-6 day"))." 00:00:00";
	  $end = date("Y-m-d")." 23:59:59";
	  }	
	elseif($period == 'thismonth')  {
	  $start = date("Y-m")."-01 00:00:00";
	  $end = date("Y-m-d")." 23:59:59";
	  }	
	elseif($period == 'lastmonth')  {
	  $start = date("Y-m", strtotime("-1 month"))."-01 00:00:00";
	  $monthno = (date("n", strtotime("-1 month")) + 1);
	  $yearno = date("Y", strtotime("-1 month"));
	  $lastday = mktime(0, 0, 0, $monthno, 0, $yearno);
	  $end = date("Y-m-d",$lastday)." 23:59:59";
	  }	
	elseif($period == 'alltime')  {
	  $start = "1970-01-01 00:00:00";
	  $end = date("Y-m-d")." 23:59:59";
	  }	
	else  {
	  $start = $fromd . " 00:00:00";
	  $end = $tod . " 23:59:59";
	  }
	$startday = ereg_replace(" 00:00:00","", $start);
	$endday = ereg_replace(" 23:59:59","", $end);
   }
if(isset($_REQUEST['what']) && $_REQUEST['what'] == "senders")  {
  $what = trim($_REQUEST['what']);
  $limit = trim($_REQUEST['limit']);
  $sendername = trim($_REQUEST['sendername']);
  $order = trim($_REQUEST['order']);
  $dir = trim($_REQUEST['dir']);
  }
if(isset($_REQUEST['what']) && $_REQUEST['what'] == "peruser")  {
  $what = trim($_REQUEST['what']);
  $userid = trim($_REQUEST['userid']);
  $username = trim($_REQUEST['username']);
  }
$group_opt = '';
$grs = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups order by g_id ASC') or error('SELECT g_id, g_title FROM '.$db->prefix.'groups order by g_id ASC', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($grs)) error('Unable to fetch Groups.');
while($found_group = $db->fetch_assoc($grs))  {
    $group_opt .= '<option value="'.$found_group['g_id'].'">'.$found_group['g_title'].'
   ';
    }
if(isset($_REQUEST['what']) && $_REQUEST['what'] == "makeInvitations")  {
  $what = trim($_REQUEST['what']);
  $num = $_REQUEST['num'];
  $group = $_REQUEST['group'];
  $group_opt = ereg_replace($group.'"', $group.'" selected',$group_opt);
  }
$period_opt = '
<option value="today">Today</option>
<option value="yesterday">Yesterday</option>
<option value="lastweek">Last 7 days</option>
<option value="thismonth">This month</option>
<option value="lastmonth">Last month</option>
<option value="alltime">All time</option>
';
if(isset($period) and $period != '')  {
  $period_opt = ereg_replace($period.'"', $period.'" selected',$period_opt);
  }
$limit_opt ='
<option value="10">10
<option value="20">20
<option value="50">50
<option value="100">100
';
if(isset($limit) and $limit != '')  {
  $limit_opt = ereg_replace($limit.'"', $limit.'" selected',$limit_opt);
  }
$order_opt ='
<option value="username">Username
<option value="st">Sent
<option value="nost">Unsent
<option value="ust">Used
';
if(isset($order) and $order != '')  {
  $order_opt = ereg_replace($order.'"', $order.'" selected',$order_opt);
  }
$dir_opt ='
<option value="ASC">Ascending
<option value="DESC">Descending
';
if(isset($dir) and $dir != '')  {
  $dir_opt = ereg_replace($dir.'"', $dir.'" selected',$dir_opt);
  }
generate_admin_menu($plugin);
?>
	<div class="blockform">
		<h2><span>Help</span></h2>
		<div class="box">
			<form>
				<div class="inform">
					<div class="infldset">
						<table class="aligntop" cellspacing="0">
						<tr>
							<td width="100px"><img src=img/admin/configcheck.png></td>
							<td>
								<span>This Plugin lets you see extended statistics about the invitations sent by your users and issue mass invitations.</span>
							</td>
						</tr>
						</table>
					</div>
				</div>
			</form>
		</div><br />
		<h2 class="block2"><span>Invitation Statistics</span></h2>
		<div class="box">
			<form id="userstats" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
			<input type="hidden" name="what" value="recipients" />
				<div class="inform">
					<fieldset>
						<table class="aligntop" cellspacing="0">
						<!--Thanks to wiseage for this function -->
							<tr>
								<th style="width:10%">See Recipients</th>
								<th style="width:10%">Period</th>
								<td style="width:20%">
								 <select name="period">
								   <option value="">Choose</option>
<?php echo $period_opt ?>
								 </select>&nbsp;&nbsp;or</td>						 
								<td style="width:24%"><input type="text" class="textbox" name="fromd" value="<?php if(isset($startday))  echo $startday ?>" size="18" tabindex="1" />
									<span>Start date YYYY-mm-dd</span>
								</td>
								<td style="width:20%">
									<input type="text" class="textbox" name="tod" value="<?php if(isset($endday))  echo $endday ?>" size="18" tabindex="1" />
									<span>End date YYYY-mm-dd</span>
								</td>
								<td style="width:6%">
                            <input type="submit" class="b1" name="stats" value="Go!" tabindex="2" />
								</td>
							</tr>
						</table>	
					</fieldset>
				</div>

<?php
	if(isset($_REQUEST['what']) && $what == "recipients")
	{
	  $result = $db->query('SELECT i.recipient, i.userid, i.sent, i.used, u.id, u.username FROM '.$db->prefix.'invitations AS i LEFT JOIN '.$db->prefix.'users AS u ON i.userid=u.id  WHERE i.sent >= \''.$start.'\' and i.sent <= \''.$end . '\' order by sent DESC') or error('SELECT i.recipient, i.userid, i.sent, i.used, u.id, u.username FROM '.$db->prefix.'invitations AS i LEFT JOIN '.$db->prefix.'users AS u ON i.userid=u.id  WHERE i.sent >= \''.$start.'\' and i.sent <= \''.$end . '\' order by sent DESC', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))  {
	  echo '<h2 style="background-color:#B84623"> No Invitation Recipients for this period</h2>
	  Maybe you have to refine your search';
	  }
	else  {
?>
        <div class="inform">
			<fieldset>
				<legend>Invitation Recipients from <?php echo $startday ?> - <? echo $endday ?></legend>
				<div class="infldset">
				<table cellspacing="0">
			<thead>
				<tr>

					<th class="tc2" scope="col" style="width:30%">Recipient Mail <br /><span style="font-weight:normal;font-size:10px">Click to see Recipient details</span></th>
					<th class="tcr" scope="col" style="width:30%;text-align:left">Inviting User</th>
					<th class="tcr" scope="col" style="width:20%;text-align:left">Invitation sent</th>
					<th class="tcr" scope="col" style="width:20%;text-align:left">Invitation used</th>
				</tr>
			</thead>
			<tbody>
<?php
   $total_recipients = 0;
   while($stat_recipients = $db->fetch_assoc($result))  {
     echo '<tr>
     <td class="tc2" style="width:30%">';
     if($stat_recipients['used'] > 0)  {
     echo '<a href="'.$_SERVER['REQUEST_URI'] . '&stats=1&what=userview&email='.$stat_recipients['recipient'].'">'.$stat_recipients['recipient'] . '</a>';
     }
     else  {
       echo $stat_recipients['recipient'];
       }
     echo '</td>
     <td class="tcr" style="width:30%;text-align:left">'.$stat_recipients['username'] . '</td>
     <td class="tcr" style="width:20%;text-align:left">'.$stat_recipients['sent'] . '</td>';
     if($stat_recipients['used'] > 0)  {
       echo '<td class="tcr" style="width:20%;text-align:left">'.$stat_recipients['used'] . '</td>';
       }
     else  {
       echo '<td class="tcr" style="width:20%;text-align:left">---</td>';
       }
     echo '</tr>';
     $total_recipients++;
     }
?>
</tbody>
    <tr>
      <td class="tc2" scope="col" style="width:30%;font-weight:bold">Total:<? echo $total_recipients ?></td>
		<td class="tcr" scope="col" style="width:70%;text-align:left;font-weight:bold" colspan="3"></td>
				</tr>
</table>
</div>
</fieldset>
</div>			

<?php
  }
  }
?>
			</form>
			<form id="senderstats" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
			<input type="hidden" name="what" value="senders" />
				<div class="inform">
					<fieldset>
						<table class="aligntop" cellspacing="0">
							<tr>
								<th style="width:15%">Invitation Stats<br/> for User</th>
								<td style="width:22%"><input type="text" class="textbox" name="sendername" value="<?php if(isset($sendername))  echo $sendername ?>" size="18" tabindex="1" />
									<span>You can use the Wildcard (*)</span>
								</td>
								<td style="width:15%">
								 Show <select name="limit">
<?php echo $limit_opt ?>
								 </select></td>						 
								<td style="width:31%">
								 Order <select name="order">
<?php echo $order_opt ?>
								 </select>&nbsp;<select name="dir">
<?php echo $dir_opt ?>
								 </select></td>						 
								 <td style="width:7%">
                            <input type="submit" class="b1" name="senders" value="Go!" tabindex="2" />
								</td>
							</tr>
						</table>	
					</fieldset>
				</div>
<?php
if(isset($_REQUEST['what']) && $what == "senders")  {
    $and = '';
     if($_REQUEST['sendername'] != '')  {
       $and .= ' and u.username LIKE \'' . ereg_replace("\*","%",$_REQUEST['sendername']) . '\'';
       }
     $sentt = 'select sum(i.sent > 0) as st, sum(i.sent = 0) as nost, sum(i.used > 0) as ust,  i.userid, u.username from '.$db->prefix.'invitations i INNER JOIN '.$db->prefix.'users u ON i.userid=u.id  WHERE 1 ' . $and .' group by userid order by '.$order.' '.$dir.' limit 0,'.intval($limit);
	  $result = $db->query($sentt) or error($sentt, __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))  {
	  echo '<h2 style="background-color:#B84623"> No Invitation Statistics available</h2>
	  Maybe you have to refine your search';
	  }
	else  {
?>
        <div class="inform">
			<fieldset>
				<legend>Number of Invitations per User</legend>
				<div class="infldset">
				<table cellspacing="0">
			<thead>
				<tr>

					<th class="tc2" scope="col" style="width:30%">User <br /><span style="font-weight:normal;font-size:10px">Click for invitation details</span></th>
					<th class="tc2" scope="col" style="width:15%;text-align:right">Total</th>
					<th class="tc2" scope="col" style="width:15%;text-align:right">Sent</th>
					<th class="tcr" scope="col" style="width:15%;text-align:right">Unsent</th>
					<th class="tcr" scope="col" style="width:25%;text-align:right">Used</th>
				</tr>
			</thead>
			<tbody>
<?php
   $total_st = 0;
   $total_nost = 0;
   $total_ust = 0;
   $total_all = 0;
   while($user_invit = $db->fetch_assoc($result))  {
     $all = (intval($user_invit['st']) + intval($user_invit['nost']));
     echo '<tr>';
     if($user_invit['st'] > 0)  {
       echo '<td class="tc2" style="width:40%"><a href="'.$_SERVER['REQUEST_URI'].'&what=peruser&userid='.$user_invit['userid'] . '&username='.$user_invit['username'] . '">'.$user_invit['username'] . '</a></td>';
       }
     else  {
       echo '<td class="tc2" style="width:40%">'.$user_invit['username'] . '</td>';
       }
     echo'
     <td class="tc2" style="width:15%;text-align:right">'. $all . '</td>
     <td class="tc2" style="width:15%;text-align:right">'.$user_invit['st'] . '</td>
     <td class="tc2" style="width:15%;text-align:right">'.$user_invit['nost'] . '</td>
     <td class="tcr" style="width:30%;text-align:right">'.$user_invit['ust'] . '</td>
     </tr>';
     $total_all += $all;
     $total_st += $user_invit['st'];
     $total_nost += $user_invit['nost'];
     $total_ust += $user_invit['ust'];
     }
?>
</tbody>
    <tr>
      <td class="tc2" scope="col" style="width:30%;font-weight:bold"></td>
		<td class="tcr" scope="col" style="width:15%;text-align:right;font-weight:bold">Sum:&nbsp;&nbsp;<? echo $total_all ?></td>
		<td class="tcr" scope="col" style="width:15%;text-align:right;font-weight:bold">Sum:&nbsp;&nbsp;<? echo $total_st ?></td>
		<td class="tcr" scope="col" style="width:15%;text-align:right;font-weight:bold">Sum:&nbsp;&nbsp;<? echo $total_nost ?></td>
		<td class="tcr" scope="col" style="width:25%;text-align:right;font-weight:bold">Sum:&nbsp;&nbsp;<? echo $total_ust ?></td>
				</tr>
</table>
</div>
</fieldset>
</div>

<?php
        }
  }
?>
<?php
if(isset($_REQUEST['what']) && $what == "peruser")  {
     $inv = 'select sent, recipient, used from '.$db->prefix.'invitations where sent > 0 AND userid='.intval($userid).' order by sent DESC';
	  $result = $db->query($inv) or error($inv, __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))
		error('Unable to fetch user information.');
?>
        <div class="inform">
			<fieldset>
				<legend>Invitations sent by <?php echo $username ?></legend>
				<div class="infldset">
				<table cellspacing="0">

			<thead>
				<tr>

					<th class="tc2" scope="col" style="width:50%">Recipient</th>
					<th class="tc2" scope="col" style="width:25%;text-align:left">Send Date</th>
					<th class="tc2" scope="col" style="width:25%;text-align:left">Used at</th>
				</tr>
			</thead>
			<tbody>
<?php
   while($user_invit = $db->fetch_assoc($result))  {
     echo '<tr>
        <td class="tc2" style="width:50%;text-align:left">'. $user_invit['recipient'] . '</td>
        <td class="tc2" style="width:25%;text-align:left">'.$user_invit['sent'] . '</td>';
     if($user_invit['used'] > 0)  {
       echo '<td class="tc2" style="width:25%">'.$user_invit['used'] . '</td>';
       }
     else  {
       echo '<td class="tc2" style="width:25%">----</td>';
       }
     echo'</tr>';
     }
?>
</tbody>
</table>
</div>
</fieldset>
</div>

<?php
  }
?>			
			</form>
			<form id="senderstats" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
			<input type="hidden" name="what" value="makeInvitations" />
				<div class="inform">
					<fieldset>
						<table class="aligntop" cellspacing="0">
						<!--Thanks to wiseage for this function -->
							<tr>
								<th style="width:25%">Make Invitations</th>
								<td style="width:30%">Amount per User: <input type="text" class="textbox" name="num" value="" size="5" tabindex="1" /></td>
								<td style="width:30%">
								 Group <select name="group">
								 <option value="all">All Groups</option>
<?php echo $group_opt ?>
								 </select></td>						 
								 <td style="width:15%">
                            <input type="submit" class="b1" name="senders" value="Go!" tabindex="2" />
								</td>
							</tr>
						</table>	
<?php
if(isset($_REQUEST['what']) && $what == "makeInvitations")  {
     if($group != 'all')  {
        $and = ' AND group_id='.intval($group);
        }
     $inv = 'select id from '.$db->prefix.'users where 1' .$and;
	  $result = $db->query($inv) or error($inv, __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))
		error('Unable to fetch user information.');
   $affected_users = 0;
   $total_invitations = 0;
   while($users = $db->fetch_assoc($result))  {
        for($i = 0;$i < $num;$i++)  {
            insertInvitation($users['id']);
            }
        $affected_users++;
        $total_invitations += $num;
        }
?>
        <div class="inform">
			<h2><?php echo $total_invitations ?> Invitations created for <?php echo $affected_users ?> Users</h2>
        </div>
<?php
  }
?>			
					</fieldset>
				</div>
</form>
	</div>
</div>