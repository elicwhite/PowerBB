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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/calendar.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/profile.php';
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums)".' / '.$lang_calendar['Calendar'];
require FORUM_ROOT.'header.php';

//----------------------------------
// Coded by Gizzmo
//----------------------------------


$day = (isset($_REQUEST['day']))? intval($_REQUEST['day']) : date("j");
$month = (isset($_REQUEST['month']))? intval($_REQUEST['month']) : date("n");
$year = (isset($_REQUEST['year']))? intval($_REQUEST['year']) : date("Y");

$day_in_mth = date("t", mktime(0, 0, 0, $month, $day, $year)) ;
$dayfull = date("jS", mktime(0, 0, 0, $month, $day, $year));
$monthtext = date("F", mktime(0, 0, 0, $month, 1, $year));
$day_text = date("D", mktime(0, 0, 0, $month, 1, $year));
$day_of_wk = date("w", mktime(0, 0, 0, $month, 1, $year));


$type = (isset($type))? $_REQUEST['type'] : $configuration['cal_start_view'] ;
$t = (isset($_GET['t']))? $_GET['t'] : NULL ;
$action = (isset($_GET['action']))? $_GET['action'] : NULL ;

/*=======================*/
/*= Navigation Function =*/
/*=======================*/
if(empty($t)&& empty($action))
{
	$month_start = mktime(0,0,0,$month,1,$year);
	$month_end = mktime(23,59,59,$month,$day_in_mth,$year);
	if($type == "posts")
	{
	
		$result = $db->query('SELECT posted FROM '.$db->prefix.'topics WHERE posted > '.$month_start.' AND posted < '.$month_end) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
		$topic = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		while($topic_list = $db->fetch_row($result))
		{
			$time = date('j',$topic_list[0]);
			$topic[$time]++;
		};
		
		//Find the posts made for "said" day
		$result = $db->query('SELECT posted FROM '.$db->prefix.'posts WHERE posted > '.$month_start.' AND posted < '.$month_end) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
		$posts = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		while($post_list = $db->fetch_assoc($result))
		{
			$time = date('j',$post_list['posted']);
			$posts[$time]++;
		};
	}
	//change querys if $type is set to events
	elseif($type == "events")
	{

		//Find the birthdays for "said" day
		$result = $db->query('SELECT DAYOFMONTH(birthday) as day FROM '.$db->prefix.'users WHERE MONTH(birthday) = '.$month) or error('Unable to fetch birtday list', __FILE__, __LINE__, $db->error());
		$bdays = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		while($bday_list = $db->fetch_assoc($result))
		{
			$bdays[$bday_list['day']]++;
		};

		//Find the events for "said" day
		$result = $db->query('SELECT id, title, DAYOFMONTH(date) as day FROM '.$db->prefix.'calendar WHERE MONTH(date) = '.$month.' AND (YEAR(date) = '.$year.' OR YEAR(date) = "0000")') or error('Unable to fetch calendar dates', __FILE__, __LINE__, $db->error());
		$dates = array('','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','');
		while($dates_list = $db->fetch_assoc($result))
		{
			$dates[$dates_list['day']] .= "<a href='calendar.php?t=events&amp;id=".$dates_list['id']."'>".$dates_list['title']."</a><br>";
		};
	}
	$ltype = (isset($type))? 'type='.$type.'&amp;' : NULL ;
	?>
<h2 style="text-align:center">
	<span>
		<a href="?<? echo $ltype?>month=<? echo $month?>&amp;year=<? echo $year-1;?>">&lt;&ndash;</a>
		<a href="?<? echo $ltype?>month=<?if($month == 1){echo '12';}else{echo $month-1;}?>&amp;year=<?if($month == 1){echo $year-1;}else{echo$year;}?>">&lt;&mdash;</a>
		<? echo $lang_calendar[$monthtext]." ".$year."\n" ?>
		<a href="?<? echo $ltype?>month=<?if($month == 12){echo '1';}else{echo $month+1;}?>&amp;year=<?if($month == 12){echo $year+1;}else{echo$year;}?>">&mdash;&gt;</a>
		<a href="?<? echo $ltype?>month=<? echo $month?>&amp;year=<? echo $year+1;?>">&ndash;&gt;</a>
	</span>
</h2>
	<div class="box">
		<table cellspacing="0">
		<thead>
			<tr>
				<th><? echo $lang_calendar['Sun']?></th>
				<th><? echo $lang_calendar['Mon']?></th>
				<th><? echo $lang_calendar['Tue']?></th>
				<th><? echo $lang_calendar['Wed']?></th>
				<th><? echo $lang_calendar['Thu']?></th>
				<th><? echo $lang_calendar['Fri']?></th>
				<th><? echo $lang_calendar['Sat']?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
<?
		if ($day_of_wk <> 0)
		{
			for ($i=0; $i<$day_of_wk; $i++)
			{
				echo "\t\t\t\t<td class='calendar_no' width='14%'>&nbsp;</td>\n";
			}
		}
		for ($date_of_mth = 1; $date_of_mth <= $day_in_mth; $date_of_mth++) {
			if ($day_of_wk = 0)
			{
				for ($i=0; $i<$day_of_wk; $i++);
				{
					echo "\t\t\t<tr>\n";
				}
			}
	
			$date_no = date("j", mktime(0, 0, 0, $month, $date_of_mth, $year));
			$day_of_wk = date("w", mktime(0, 0, 0, $month, $date_of_mth, $year));
	
			if ($date_no ==  date("j") &&  $month == date("n") && $year == date("Y"))
			{
				echo "\t\t\t\t<td class='calendar_day' height='75' valign='top' width='14%'><div>".$date_no."</div>";
				
				if(!empty($topic[$date_no]))echo"<a href='calendar.php?t=topics'>".$lang_calendar['Topics']." ".$topic[$date_no]."</a><br />";
				if(!empty($posts[$date_no]))echo"<a href='calendar.php?t=posts'>".$lang_calendar['Posts']." ".$posts[$date_no]."</a><br />";
				if(!empty($bdays[$date_no]))echo"<a href='calendar.php?t=bday'>".$lang_calendar['Birthdays']." ".$bdays[$date_no]."</a><br />";
				if(!empty($dates[$date_no]))echo $dates[$date_no]."<br>";
				
				echo "</td>\n";
			}
			else
			{
				echo "\t\t\t\t<td height='75' valign='top' width='14%'><div>$date_no</div>";
				
				if(!empty($topic[$date_no]))echo"<a href='calendar.php?t=topics&amp;year=".$year."&amp;month=".$month."&amp;day=".$date_no."'>".$lang_calendar['Topics']." ".$topic[$date_no]."</a><br />";
				if(!empty($posts[$date_no]))echo"<a href='calendar.php?t=posts&amp;year=".$year."&amp;month=".$month."&amp;day=".$date_no."'>".$lang_calendar['Posts']." ".$posts[$date_no]."</a><br />";
				if(!empty($bdays[$date_no]))echo"<a href='calendar.php?t=bday&amp;year=".$year."&amp;month=".$month."&amp;day=".$date_no."'>".$lang_calendar['Birthdays']." ".$bdays[$date_no]."</a><br />";
				
				if(!empty($dates[$date_no]))echo $dates[$date_no]."<br>";
				
				echo "</td>\n";
			}
			if ( $day_of_wk == 6 ) {echo "\t\t\t</tr>\n\t\t\t<tr>\n";}
			if ( $day_of_wk < 6 && $date_of_mth == $day_in_mth )
			{
				for ( $i = $day_of_wk ; $i < 6; $i++ )
				{
					echo "\t\t\t\t<td class='calendar_no' width='14%'>&nbsp;</td>\n";
				}
				echo "\t\t\t</tr>\n";
			}
		}
?>
			<tr>
				<td colspan="7">
				<div style="float:left">
				<form method="post" action="calendar.php">
				Go To: 
				<select name="month">
<?
				$month_name = array($lang_calendar['January'],$lang_calendar['February'],$lang_calendar['March'],$lang_calendar['April'],$lang_calendar['May'],$lang_calendar['June'],$lang_calendar['July'],$lang_calendar['August'],$lang_calendar['September'],$lang_calendar['October'],$lang_calendar['November'],$lang_calendar['December']);
				for($x=00;$x<12;$x++)
				{
					$z = $x+1;
					echo"\t\t\t\t\t<option value='".$z."'";
					if($month == $z)echo" selected";
					echo">".$month_name[$x]."</option>\n";
				}
?>
				</select>
				
				<select name="year">
<?
				for($x=-5; $x<=5; $x++)
				{
					$said_year = $year+$x;
					echo"\t\t\t\t\t<option value='".$said_year."'";
					if($said_year == $year)echo" SELECTED";
					echo ">".$said_year."</option>\n";
				}
?>
				</select>
				<input type="submit" name="Submit">
				<span class="conr">
					<select name="type">
						<option value="posts" <? if($type=="posts")echo"selected=\"selected\""?>>Posts/Topics</option>
						<option value="events" <? if($type=="events")echo"selected=\"selected\""?>>Events</option>
					</select>
					<input type="submit" />
				</span>
				</form>
				</div>
				</td>
			</tr>
		</tbody>
		</table>
	</div>
<?

////////////////////////////////////////////////////////
if($configuration['cal_show_cal'] == "yes"){
?>
<br />
<table cellspacing="0">
<tr><td valign="top" style="border:none;padding:0 5px 0 0; margin:0">
<?
for($X=$month-1; $X<=$month+1; $X++)
{

$day_in_mth = date("t", mktime(0, 0, 0, $X, $day, $year)) ;
$dayfull = date("jS", mktime(0, 0, 0, $X, $day, $year));
$monthtext = date("F", mktime(0, 0, 0, $X, 1, $year));
$day_text = date("D", mktime(0, 0, 0, $X, 1, $year));
$day_of_wk = date("w", mktime(0, 0, 0, $X, 1, $year));


	if($X != $month){
?>
	<h2><span><a href="calendar.php?month=<? echo $X?>"><? echo $lang_calendar[$monthtext]?></a></span></h2>
	<div class="box">
		<table cellspacing="0">
		<thead>
			<tr>
				<th><? echo $lang_calendar['s']?></th>
				<th><? echo $lang_calendar['m']?></th>
				<th><? echo $lang_calendar['t']?></th>
				<th><? echo $lang_calendar['w']?></th>
				<th><? echo $lang_calendar['t']?></th>
				<th><? echo $lang_calendar['f']?></th>
				<th><? echo $lang_calendar['s']?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
<?
		if ($day_of_wk <> 0){for ($i=0; $i<$day_of_wk; $i++){ echo "\t\t\t\t<td class='calendar_no'>&nbsp;</td>\n"; }}
	
		for ($date_of_mth = 1; $date_of_mth <= $day_in_mth; $date_of_mth++) {
	
			if ($day_of_wk = 0){for ($i=0; $i<$day_of_wk; $i++);{ echo "\t\t\t<tr>\n"; }}
	
			$date_no = date("j", mktime(0, 0, 0, $X, $date_of_mth, $year));
			$day_of_wk = date("w", mktime(0, 0, 0, $X, $date_of_mth, $year));
			
	
			if ($date_no ==  date("j") &&  $X == date("n") && $year == date("Y")){echo "\t\t\t\t<td class='calendar_day'><div>".$date_no."</div></td>\n";
			}else{echo "\t\t\t\t<td><div>$date_no</div></td>\n";}

			if ( $day_of_wk == 6 ) {echo "\t\t\t</tr>\n";}
			if ( $day_of_wk < 6 && $date_of_mth == $day_in_mth ) {for ( $i = $day_of_wk ; $i < 6; $i++ ) {echo "\t\t\t\t<td class='calendar_no'>&nbsp;</td>\n\n";}}
		}
?>
			</tr>
		</tbody>
		</table>
	</div>
<? }
else
{
	echo '</td><td valign="top" style="border:none;padding:0; margin:0">';

}
?>
</td></tr></table>

<?
}
////////////////////////////////////////////////////////
?><br />

<div class="box">
<p>&nbsp;<a href="calendar.php?action=add">Add Event</a> | <a href="calendar.php?action=edit">Edit Event</a></p>
</div>
<?
}
elseif(isset($t))
{
	$datestart = mktime(0,0,0,$month,$day,$year);
	$dateend = mktime(23,59,59,$month,$day,$year);
###########################################################
###//===============//=================//===============//#
##//===============// Show Topic List //===============//##
#//===============//=================//===============//###
###########################################################
	if($t == 'topics')
	{
		//Number of posts to display
		$disp_topics = $forum_user['disp_topics'];
		
		$result = $db->query('SELECT id FROM '.$db->prefix.'topics WHERE posted > '.$datestart.' AND posted < '.$dateend);
		$num_replies = $db->num_rows($result);
		
		
		// Determine the post offset (based on $_GET['p'])
		$num_pages = ceil(($num_replies) / $disp_topics);
		
		$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
		$start_from = $disp_topics * ($p - 1);
		
		$pages = paginate($num_pages, $p, 'calendar.php?t=topics&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day);
		
		// Pulls list of topics for the day
		$result = $db->query('SELECT id, poster, subject, posted, last_post, last_post_id, last_poster, moved_to, num_replies, closed FROM '.$db->prefix.'topics WHERE posted > '.$datestart.'  AND posted < '.$dateend.' ORDER BY posted LIMIT '.$start_from.','.$disp_topics) or error('Unable to fetch topic list for forum', __FILE__, __LINE__, $db->error());
		?>
	
		
		<h2 style="text-align:center"><span><? echo $lang_calendar['Viewing_Topics']." ".$monthtext." ".$dayfull." ".$year?></span></h2>
		<div class="box">
	
			<table cellspacing="0">
			<thead>
			<tr>
				<th style="white-space: nowrap">Topic</th>
				<th style="width: 13%; white-space: nowrap">Author</th>
				<th style="width: 7%; white-space: nowrap">Replies</th>
				<th style="width: 25%; white-space: nowrap">Last post</th>
			</tr>
			</thead>
			<tbody>
		<?
		if($db->num_rows($result))
		{
			while ($cur_topic = $db->fetch_assoc($result))
			{
				if ($cur_topic['moved_to'] == null)
					$last_post = '<a href="viewtopic.php?pid='.$cur_topic['last_post_id'].'#'.$cur_topic['last_post_id'].'">'.format_time($cur_topic['last_post']).'</a><br> '.$lang_common['by'].' '.htmlspecialchars($cur_topic['last_poster']);
				else
					$last_post = '&nbsp;';
		
				if ($config['o_censoring'] == '1')
					$cur_topic['subject'] = censor_words($cur_topic['subject']);
				
				if ($cur_topic['closed'] == '0')
					$subject = '<a href="viewtopic.php?id='.$cur_topic['id'].'">'.htmlspecialchars($cur_topic['subject']).'</a>';
				else
					$subject = '<a class="Powerbbclosed" href="viewtopic.php?id='.$cur_topic['id'].'">'.htmlspecialchars($cur_topic['subject']).'</a>';
				
		?>
				<tr class="PowerBBtopic">
					<td><?php echo $subject ?></td>
					<td><?php echo htmlspecialchars($cur_topic['poster']) ?></td>
					<td><?php echo ($cur_topic['moved_to'] == null) ? $cur_topic['num_replies'] : '&nbsp;' ?></td>
					<td style="white-space: nowrap"><?php echo $last_post ?></td>
				</tr>
		<?
			}
		}else{
		?>
				<tr class="PowerBBtopic">
					<td class="PowerBBcon1cent" colspan="5"><? echo$lang_calendar['No_Topics']?></td>
				</tr>
		<?}?>
			</tbody>
			</table>
		</div>
		
	<?}
##########################################################
###//===============//================//===============//#
##//===============// Show Post list //===============//##
#//===============//================//===============//###
##########################################################
	elseif($t == 'posts')
	{
		//Number of posts to display
		$disp_posts = $forum_user['disp_posts'];
		
		$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE posted > '.$datestart.' AND posted < '.$dateend);
		$num_replies = $db->num_rows($result);
		
		
		// Determine the post offset (based on $_GET['p'])
		$num_pages = ceil(($num_replies) / $disp_posts);
		
		$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
		$start_from = $disp_posts * ($p - 1);
		
		$pages = paginate($num_pages, $p, 'calendar.php?t=posts&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day);
		
		$result = $db->query('
			SELECT 
			 p.id, p.poster, p.poster_id, p.message, p.posted, p.topic_id,
			 t.id as tid, t.subject as tsub, t.forum_id,
			 f.id as fid, f.forum_name as fname 
			FROM '.$db->prefix.'posts AS p 
			INNER JOIN '.$db->prefix.'topics AS t ON p.topic_id=t.id 
			INNER JOIN '.$db->prefix.'forums AS f ON t.forum_id=f.id 
			WHERE p.posted > '.$datestart.' AND p.posted < '.$dateend.'
			
			ORDER BY p.posted LIMIT '.$start_from.','.$disp_posts)or error('Unable to fetch topic list for forum', __FILE__, __LINE__, $db->error());
	?>
	
		<h2 align="center"><span><? echo $lang_calendar['Viewing_Posts']." ".$monthtext." ".$dayfull." ".$year?></span></h2>
		<div class="box">
			<table cellspacing="0">
			<thead>
				<tr>
					<th style="white-space: nowrap">&nbsp;<? echo $lang_common['Message']?></th>
					<th style="width: 12%"><? echo $lang_common['Posted']?></th>
					<th style="width: 12%"><? echo $lang_common['Forum']?></th>
				</tr>
			</thead>
			<tbody>
		<?
		if($db->num_rows($result))
		{
			while ($cur_posts = $db->fetch_assoc($result))
			{
				if (forum_strlen($cur_posts['message']) >= 80)
				{
					$cur_posts['message'] = substr($cur_posts['message'], 0, 79);
					$cur_posts['message'] .= '&hellip;';
				}
		
		?>
				<tr>
					<td>
					Topic: <a href="viewtopic.php?id=<? echo$cur_posts['tid']?>"><? echo $cur_posts['tsub']?></a><br>
					Posted by: <a href="profile.php?id=<? echo$cur_posts['poster_id']?>"><? echo $cur_posts['poster']?></a><br>
					
					<div class=box style="padding:5px; margin:4px;">
					<? echo $cur_posts['message']?>
					<div style="text-align: right">
					<a href="viewtopic.php?pid=<? echo$cur_posts['id'].'#'.$cur_posts['id']?>">
					Go to post</a></div>
					</div>
					</td>
					<td class="PowerBBcon2cent"><? echo date($config['o_time_format'], $cur_posts['posted'])?></td>
					<td class="PowerBBcon1cent"><a href="viewforum.php?id=<? echo$cur_posts['fid']?>"><? echo $cur_posts['fname']?></a></td>
					
				</tr>
		
		<?	}
		}else{
		?>
				<tr>
					<td colspan="3"><? echo$lang_calendar['No_Posts']?></td>
				</tr>
		<?}?>
			</tbody>
			</table>
		</div>

	
	
	<?}
########################################################
###//===============//===============//==============//#
##//===============// Birthday list //==============//##
#//===============//===============//==============//###
########################################################
	elseif($t == 'bday')
	{
	require FORUM_ROOT.'lang/'.$forum_user['language'].'/profile.php';
	
	$result = $db->query('
		SELECT id, username, displayname use_avatar, last_post, registered, birthday, DAYOFMONTH(birthday) as day
		FROM '.$db->prefix.'users 
		WHERE DAYOFMONTH(birthday) = '.$day.'
		AND MONTH(birthday) = '.$month.'
		ORDER BY username') or error('Unable to fetch birtday list', __FILE__, __LINE__, $db->error());
	
	?>
		<h2 align="center"><span><? echo $lang_calendar['Viewing_Bday']." ".$monthtext." ".$dayfull." ".$year?></span></h2>
		<div class="box">
	
			<table cellspacing="0">
			<thead>
				<tr>
					<th style="width: 80px"><? echo $lang_profile['Avatar']?></th>
					<th style="width: 25%"><? echo $lang_common['Username']?></th>
					<th style="width: 15%"><? echo $lang_calendar['Age']?></th>
					<th style=""><? echo $lang_common['Registered']?></th>
				</tr>
			</thead>
			<tbody>
		
	<?
		if($db->num_rows($result))
		{
			while($bday_list = $db->fetch_assoc($result))
			{
				if ($bday_list['use_avatar'] == '1')
				{
					if ($img_size = @getimagesize($config['o_avatars_dir'].'/'.$bday_list['id'].'.gif'))
						$avatar = '<img class="poweravatar" src="'.$config['o_avatars_dir'].'/'.$bday_list['id'].'.gif">';
					else if ($img_size = @getimagesize($config['o_avatars_dir'].'/'.$bday_list['id'].'.jpg'))
						$avatar = '<img class="poweravatar" src="'.$config['o_avatars_dir'].'/'.$bday_list['id'].'.jpg">';
					else if ($img_size = @getimagesize($config['o_avatars_dir'].'/'.$bday_list['id'].'.png'))
						$avatar = '<img class="poweravatar" src="'.$config['o_avatars_dir'].'/'.$bday_list['id'].'.png">';
					else
						$avatar = $lang_profile['No avatar'];
				}
				else
					$avatar = $lang_profile['No avatar'];
					
				list($bday_year,$bday_month,$bday_day) = explode('-', $bday_list['birthday']);
				$age = $year-$bday_year;
			?>
				<tr>
					<td><? echo $avatar?></td>
					<td><a href="profile.php?id=<? echo$bday_list['id']?>"><? echo $bday_list['username']?></a></td>
					<td><? echo $age?></td>
					<td><? echo format_time($bday_list['registered'])?></td>
				</tr>
			<?
			}
		}else{
		?>
				<tr>
					<td colspan="4"><? echo$lang_calendar['No_Bday']?></td>
				</tr>
		<?}?>
			</tbody>
			</table>
		</div>
	<?
	}
#######################################################
###//===============//==============//==============//#
##//===============// Events by id //==============//##
#//===============//==============//==============//###
#######################################################
	elseif($t = 'events')
	{
	$id = intval($_GET['id']);	
	if(empty($id))
			message('You did not specify a event id.');
	$result = $db->query('
		SELECT 
		 e.id, e.date, e.title, e.user_id, e.body,
		 u.username as username, u.num_posts as posts, u.registered as reg,
		 g.g_title as group_id
		 
		FROM '.$db->prefix.'calendar AS e 
		INNER JOIN '.$db->prefix.'users AS u ON e.user_id=u.id 
		LEFT JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id
				
		WHERE e.id = "'.$id.'"
		') or error('Unable to fetch event list', __FILE__, __LINE__, $db->error());
		
	if(!$db->num_rows($result))
		message('There is no event with that id number.');
		
	$event_info = $db->fetch_assoc($result);
			
	//setting up the date info
	$date_part = explode('-',$event_info['date']);
	$date = date("F jS", mktime(0,0,0,$date_part['1'],$date_part['2'], $date_part['0']));
	
	if($date_part[0]=="0000")
		$date .= ' (Recuring Event)';
	else
		$date .= $date_part[0].' (Single Day Event)';
		
	
	
?>
		<h2 align="center"><span><? echo $lang_calendar['Viewing_Event']?> <? echo $event_info['title']?></span></h2>
		<div class="box">
	
			<table cellspacing="0">
			<thead>
				<tr>
					<th style="width:18em;" align="left"><a href="profile.php?id=<? echo $event_info['user_id']?>"><? echo $event_info['username']?></a></th>
					<th align="left"><? echo $lang_calendar['Event_Date']?><? echo $date?></th>
					
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<dl>
							<dd><? echo $lang_calendar['Group'].' '.$event_info['group_id']?></dd>
							<dd><? echo $lang_calendar['Posts'].' '.$event_info['posts']?></dd>
							<dd><? echo $lang_calendar['Joined'].' '.format_time($event_info['reg'], true)?></dd>
						</dl>
					</td>
					<td valign="top"><? echo $event_info['body']?></td>
				</tr>
				<tr>
					<th colspan="2" align="center"> <a href="calendar.php?type=events"><?php echo $lang_common['Go back'] ?></a> </td>
				</tr>
			</tbody>
			</table>
		</div>

<?	}
if($t != "events")
	navigation($t);

}
elseif(isset($action))
{
	if(($forum_user['group_id'] == USER_MOD && $configuration['cal_mod_add']=='no') or ($forum_user['group_id'] == USER_MEMBER && $configuration['cal_user_add']=='no') or ($forum_user['group_id'] == USER_GUEST))
		message($lang_calendar['warning']);
		
#######################################################
###//===============//==============//==============//#
##//===============// Add an event //==============//##
#//===============//==============//==============//###
#######################################################
	if($action == 'add')
	{
		if(isset($_POST['form_sent']))
		{
			// Check to see if the Title, Body, Month, and Day were sent
			if(empty($_POST['title']))
				message($lang_calendar['need_title']);
			elseif(empty($_POST['body']))
				message($lang_calendar['need_body']);
			elseif($_POST['month']=="0" || $_POST['day']=="0")
				message($lang_calendar['need_date']);
				
			// Clean up body and title from POST
			$title = forum_trim($_POST['title']);
			$body = str_replace("\n", '<br />', forum_linebreaks($_POST['body']));
			
			// Setup the corretct date layout for the database
			if($_POST['year'] == 'Year')
				$_POST['year'] = "0000";
			
			// Check to see of the month and day were set
			
			// Check to see if the day seleced for the month is an actual day
			$year=($_POST['year']=='0000')? date('Y'): $_POST['year'];
			if(date('t', mktime(0,0,0,$_POST['month'],1,$year))< $_POST['day'])
				message($lang_calendar['date_error']);
			
			$date = $_POST['year'].'-'.$_POST['month'].'-'.$_POST['day'];
			
			// Add the Event to the database
			$db->query('INSERT INTO '.$db->prefix.'calendar (date, title, body, user_id) VALUES("'.$date.'", "'.$title.'", "'.$body.'", "'.$_POST['user_id'].'")') or error('Unable to create new event', __FILE__, __LINE__, $db->error());
			
			redirect(FORUM_ROOT.'calendar.php','Calendar event added.');
		}
		else
		{
		?>
		<div class="blockform">
		<h2><? echo $lang_calendar['add_event']?></h2>
		<div class="box">
			<form method="post" action="calendar.php?action=add" onsubmit="return process_form(this)">
				<div class="inform">
					<fieldset>
						<legend><? echo $lang_calendar['add_info']?></legend>
						<div class="infldset">
							<input type="hidden" name="form_sent" value="1" />
							<input type="hidden" name="user_id" value="<? echo $forum_user['id']?>" />
							
							<label style="float:left; width: 152px;">
								<strong><? echo $lang_calendar['Title']?></strong><br />
								<input type="text" name="title" maxlength="50" tabindex="1" /><br />
							</label>
							
							<label>
								<strong><? echo $lang_calendar['Date']?></strong> <i><? echo $lang_calendar['date_help']?></i><br />
								
								<select name="month" tabindex="2">
									<option value='0'><? echo $lang_calendar['Month']?></option>
<?
	$month_name = array('',$lang_calendar['January'],$lang_calendar['February'],$lang_calendar['March'],$lang_calendar['April'],$lang_calendar['May'],$lang_calendar['June'],$lang_calendar['July'],$lang_calendar['August'],$lang_calendar['September'],$lang_calendar['October'],$lang_calendar['November'],$lang_calendar['December']);
	for($x=1;$x<13;$x++)
		echo"\t\t\t\t\t\t\t\t\t<option value='".$x."'>".$month_name[$x]."</option>\n";

?>
								</select>
								<select name="day" tabindex="3">
									<option value='0'><? echo $lang_calendar['Day']?></option>
<?
	for($x=01;$x<=31;$x++)
		echo"\t\t\t\t\t\t\t\t\t<option value='".$x."'>".$x."</option>\n";

?>
								</select>
								<input name="year" size="5" value="<? echo $lang_calendar['Year']?>" tabindex="4" />&nbsp;
		
							</label>
							
							
							<div class="txtarea">
								<label>
									<strong><? echo $lang_calendar['Body']?></strong><br />
									<textarea name="body" rows="7" cols="65"></textarea><br />
								</label><br />
							</div>
						</div>
						
					</fieldset>
				</div>
				<p><input type="submit" value="<?php echo $lang_common['Submit'] ?>" /><a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
			</form>
		</div>
		</div>
		<?
		}
	}
########################################################
###//===============//===============//==============//#
##//===============// Edit an event //==============//##
#//===============//===============//==============//###
########################################################
	elseif($action =='edit')
	{
		if(isset($_POST['form_sent']))
		{
			// Check to see if the Title, Body, Month, and Day were sent
			if(empty($_POST['title']))
				message($lang_calendar['need_title']);
			elseif(empty($_POST['body']))
				message($lang_calendar['need_body']);
			elseif($_POST['month']=="0" || $_POST['day']=="0")
				message($lang_calendar['need_date']);
				
			// Clean up body and title from POST
			$title = forum_trim($_POST['title']);
			$body = str_replace("\n", '<br />', forum_linebreaks($_POST['body']));
			
			// Setup the corretct date layout for the database
			if($_POST['year'] == 'Year')
				$_POST['year'] = "0000";
			
			// Check to see of the month and day were set
			
			// Check to see if the day seleced for the month is an actual day
			$year=($_POST['year']=='0000')? date('Y'): $_POST['year'];
			if(date('t', mktime(0,0,0,$_POST['month'],1,$year))< $_POST['day'])
				message($lang_calendar['date_error']);
			
			$date = $_POST['year'].'-'.$_POST['month'].'-'.$_POST['day'];
			
			// Add the Event to the database
			$db->query('UPDATE '.$db->prefix.'calendar SET date="'.$date.'", title="'.$title.'", body="'.$body.'" WHERE id='.$_POST['event_id']) or error('Unable to Update event', __FILE__, __LINE__, $db->error());
			
			redirect(FORUM_ROOT.'calendar.php?action=edit','Calendar event Edited.');
		}
		elseif(isset($_POST['delete_event']))
		{
			if(isset($_POST['delete_confirmed'])){	
				$db->query('DELETE FROM '.$db->prefix.'calendar WHERE id ="'.$_POST['delete_event'].'"') or error('Unable to Delete event', __FILE__, __LINE__, $db->error());
			
				redirect(FORUM_ROOT.'calendar.php?action=edit','Calendar event deleted');
			}
			else
			{
				if(empty($_POST['event']))
					message($lang_calendar['no_event']);
?>
<div id="msg" class="block">
	<h2><span><?php echo $lang_common['Info'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p><? echo $lang_calendar['delete_confirm'];?></p>
			<form method="post" action="calendar.php?action=edit">
			<input type="hidden" name="delete_event" value="<? echo $_POST['event']?>">
			<p><input type="Submit" name="delete_confirmed" value=" Yes ">&nbsp;<a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
			</form>
		</div>
	</div>	
</div>
<?
			}
		}
		else if(isset($_POST['edit_event']))
		{
		if(empty($_POST['event']))
			message($lang_calendar['no_event']);
			
		// Get all the info for the event your trying to edit
		$result = $db->query('SELECT * FROM '.$db->prefix.'calendar WHERE id ="'.$_POST['event'].'"') or error('Unable to fetch event information', __FILE__, __LINE__, $db->error());
		$event_list = $db->fetch_assoc($result);
		
		// Get the date info and put it into parts
		$date = explode('-', $event_list['date']);
		
?>		<div class="blockform">
		<h2><? echo $lang_calendar['edit_event']?></h2>
		<div class="box">
			<form method="post" action="calendar.php?action=edit" onsubmit="return process_form(this)">
				<div class="inform">
					<fieldset>
						<legend><? echo $lang_calendar['edit_info']?></legend>
						<div class="infldset">
							<input type="hidden" name="form_sent" value="1" />
							<input type="hidden" name="event_id" value="<? echo $_POST['event']?>" />
							
							<label style="float:left; width: 152px;">
								<strong><? echo $lang_calendar['Title']?></strong><br />
								<input type="text" value="<? echo $event_list['title']?>" name="title" maxlength="50" tabindex="1" /><br />
							</label>
							
							<label>
								<strong><? echo $lang_calendar['Date']?></strong> <i><? echo $lang_calendar['date_help']?></i><br />
								
								<select name="month" tabindex="2">
									<option value='0'><? echo $lang_calendar['Month']?></option>
<?
	$month_name = array('',$lang_calendar['January'],$lang_calendar['February'],$lang_calendar['March'],$lang_calendar['April'],$lang_calendar['May'],$lang_calendar['June'],$lang_calendar['July'],$lang_calendar['August'],$lang_calendar['September'],$lang_calendar['October'],$lang_calendar['November'],$lang_calendar['December']);
	for($x=1;$x<13;$x++)
	{
		$s = ($x == $date[1])? ' selected' : NULL ;
		echo"\t\t\t\t\t\t\t<option value='".$x."'".$s.">".$month_name[$x]."</option>\n";
	}
?>
								</select>
								<select name="day" tabindex="3">
									<option value='0'><? echo $lang_calendar['Day']?></option>
<?
	for($x=01;$x<=31;$x++)
	{
		$s = ($x == $date[2])? ' selected' : NULL ;
		echo"\t\t\t\t\t\t\t<option value='".$x."'".$s.">".$x."</option>\n";
	}
?>
								</select>
								<input name="year" value="<? echo $date[0]?>" size="5" tabindex="4" />&nbsp;
								
							</label>
							
							
							<div class="txtarea">
								<label>
									<strong><? echo $lang_calendar['Body']?></strong><br />
									<textarea name="body" rows="7" cols="65"><? echo str_replace('<br />',"\n",$event_list['body'])?></textarea><br />
								</label><br />
							</div>
						</div>
						
					</fieldset>
				</div>
				<p><input type="submit" value="<?php echo $lang_common['Submit'] ?>" /><a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
			</form>
		</div>
		</div>

<?
		}
		else
		{
			
		if(
			($forum_user['group_id']== USER_MOD && $configuration['cal_mod_edit'] == "no")
			AND
			($forum_user['group_id']!= USER_ADMIN)
		)
		$can_edit = "WHERE user_id = ".$forum_user['id'];
		else
		$can_edit = "";
		
		$result = $db->query('SELECT * FROM '.$db->prefix.'calendar '.$can_edit.' ORDER BY id') or error('Unable to fetch event information', __FILE__, __LINE__, $db->error());
		
		if(!$db->num_rows($result))
			message($lang_calendar['no_event']);
	
		?>
		<form method="post" action="calendar.php?action=edit" onsubmit="return process_form(this)">
		<div class="blockform">
		<h2><? echo $lang_calendar['edit_event']?></h2>
		<div class="box">
			<table cellspacing='0'>
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th><? echo $lang_calendar['Title']?></th>
					<th><? echo $lang_calendar['Date']?></th>
					<th><? echo $lang_calendar['Body']?></th>
					
					
				</tr>
			</thead>
			<tbody>
		<?
		
		while($event_list = $db->fetch_assoc($result))
		{
			$date_part = explode('-',$event_list['date']);
			$date_year = ($date_part['0']=='0000')?  date('y') : $date_part['0'];
			$date = date("F jS", mktime(0,0,0,$date_part['1'],$date_part['2'],$date_year));
	
?>
				<tr>
					<td style="width:10px">
						<input type="radio" name="event" value="<? echo $event_list['id']?>"
					</td>
					<td><? echo $event_list['title']?></td>
					<td style="width:85px"><? echo $date?></td>
					<td><? echo str_replace('\n','<br />',$event_list['body'])?></td>
					
				</tr>
<?
		}
?>
				<tr>
					<td colspan="4">
						<input type="submit" Value="Edit Event" name="edit_event">&nbsp;<input type="submit" Value="Delete Event" name="delete_event">&nbsp;<a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a>
					</td>
			</tbody>
			</table>
		</div>
		</form>
<?
		}
		
		
	}
}
}
?>
<br />
<? require FORUM_ROOT.'footer.php';?>