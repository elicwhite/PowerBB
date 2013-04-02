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
if ($forum_user['g_read_board'] == '0') message($lang_common['No view']);
require FORUM_ROOT.'lang/'.$forum_user['language'].'/gallery.php';
if (isset($_GET['get_host']))
{
	if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
	if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $_GET['get_host'])) $ip = $_GET['get_host'];
	else
	{
		$get_host = intval($_GET['get_host']);
		if ($get_host < 1) message($lang_common['Bad request']);
		$result = $db->query('SELECT poster_ip FROM '.$db->prefix.'gallery_img WHERE id='.$get_host) or error('Unable to fetch post IP address', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result)) message($lang_common['Bad request']);
		$ip = $db->result($result);
	}
	message('The IP address is: '.$ip.'<br />The host name is: '.@@gethostbyaddr($ip).'<br /><br /><a href="admin_users.php?show_users='.$ip.'">Show more users for this IP</a>');
}

function update_cat($cat_id)
{
	global $db;
  	$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'gallery_img WHERE cat_id='.$cat_id) or error('Unable to fetch categorie picture count', __FILE__, __LINE__, $db->error());
  	$num_img = $db->result($result);
    	$result = $db->query('SELECT posted, poster, poster_id FROM '.$db->prefix.'gallery_img WHERE cat_id='.$cat_id.' ORDER BY posted DESC LIMIT 1') or error('Unable to fetch poster/posted', __FILE__, __LINE__, $db->error());
  	if ($db->num_rows($result))
  	{
  		list($last_post, $last_poster, $last_poster_id) = $db->fetch_row($result);
    		$db->query('UPDATE '.$db->prefix.'gallery_cat SET num_img='.$num_img.', last_poster_id='.$last_poster_id.', last_post='.$last_post.', last_poster=\''.$db->escape($last_poster).'\' WHERE id='.$cat_id) or error('Unable to update last_post/last_poster', __FILE__, __LINE__, $db->error());
  	}
  	else $db->query('UPDATE '.$db->prefix.'gallery_cat SET num_img=0, last_post=NULL, last_poster_id=NULL, last_poster=NULL WHERE id='.$cat_id) or error('Unable to rezet last_post/last_poster', __FILE__, __LINE__, $db->error());
}

if(isset($_GET['pid']))
{
  $pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
  if ($pid < 1) message($lang_common['Bad request']);
  $result = $db->query('SELECT id, poster_id, subject, posted FROM '.$db->prefix.'gallery_img WHERE id='.$pid) or error('Unable to fetch pictures', __FILE__, __LINE__, $db->error());
  $cur_img = $db->fetch_assoc($result);
  if ($cur_img)
    {
      $db->query('UPDATE '.$db->prefix.'gallery_img SET num_views=num_views+1 WHERE id='.$pid) or error('Unable to update picture', __FILE__, __LINE__, $db->error());
      if($configuration['g_ftp_upload'] == 1)
      {
        if (@@fopen($configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.gif', 'r'))
          { $extension = '.gif'; $content_type = 'image/gif';}
        elseif (@@fopen($configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.jpg', 'r'))
          { $extension = '.jpg'; $content_type = 'image/jpeg';}
        elseif (@@fopen($configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.png', 'r'))
          { $extension = '.png'; $content_type = 'image/png';}
        else
          $extension = '';
      }
      else
      {
        if (file_exists($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.gif'))
          { $extension = '.gif'; }
        elseif (file_exists($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.jpg'))
          { $extension = '.jpg'; }
        elseif (file_exists($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.png'))
          { $extension = '.png'; }
        else $extension = '';
      }
      if($extension) $picture = $cur_img['poster_id'].'_'.$cur_img['posted'].$extension;
      else message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error No Img View']);
    }
  else
    message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error No Img View']);
    if($configuration['g_ftp_upload'] == 1) header('Location: '.$configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$picture);
    else header('Location: '.$configuration['g_rep_upload'].'/'.$picture);
}
elseif(isset($_GET['cid']))
{
  $id = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
  if ($id < 1) message($lang_common['Bad request']);
  $result = $db->query('SELECT c.cat_name, c.moderators, c.num_img, gp.post_cat FROM '.$db->prefix.'gallery_cat AS c LEFT JOIN '.$db->prefix.'gallery_perms AS gp ON (gp.cat_id=c.id AND gp.group_id='.$forum_user['g_id'].') WHERE (gp.read_cat IS NULL OR gp.read_cat=1) AND c.id='.$id) or error('Unable to fetch gallery info', __FILE__, __LINE__, $db->error());
  if (!$db->num_rows($result)) message($lang_common['Bad request']);
  $cur_cat = $db->fetch_assoc($result);
  $mods_array = array();
  if ($cur_cat['moderators'] != '') $mods_array = unserialize($cur_cat['moderators']);
  $is_admmod = ($forum_user['g_id'] == USER_ADMIN || (array_key_exists($forum_user['username'], $mods_array))) ? true : false;
  if ($is_admmod && isset($_GET['mod'])) $is_admmod_plink = '&mod='.$forum_user['id'];
  else $is_admmod_plink = '';
  if ((($cur_cat['post_cat'] == '' && $forum_user['g_post_topics'] == '1') || $cur_cat['post_cat'] == '1' || $is_admmod) && !isset($_GET['mod'])) $post_link = "\t\t".'<p class="postlink conr" id="postbuttons"><a href="gallery_post.php?cid='.$id.'">New Image</a>'."\n";
  else $post_link = '';
  $num_pages = ceil($cur_cat['num_img'] / $configuration['g_disp_img_default']);
  $p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
  $start_from = $configuration['g_disp_img_default'] * ($p - 1);
  if ($is_admmod && !isset($_GET['mod'])) $is_admmod_link = "\t\t".'&nbsp;&nbsp;&nbsp;<a href="gallery.php?cid='.$id.'&p='.$p.'&mod='.$forum_user['id'].'">'.$lang_gallery['Modo link'].'</a></p>'."\n";
  else $is_admmod_link = '';
  $paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'gallery.php?cid='.$id.$is_admmod_plink);
  if (isset($_GET['mod']) && $is_admmod)
  {
    if (isset($_REQUEST['move_img']) || isset($_POST['move_img_to']))
    {
    	if (isset($_POST['move_img_to']))
    	{
      	if (!preg_match('#/gallery\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
    		if (preg_match('/[^0-9,]/', $_POST['pictures'])) message($lang_common['Bad request']);
    		$pictures = explode(',', $_POST['pictures']);
    		$move_to_cat = isset($_POST['move_to_cat']) ? intval($_POST['move_to_cat']) : 0;
    		if (empty($pictures) || $move_to_cat < 1) message($lang_common['Bad request']);
    		$db->query('UPDATE '.$db->prefix.'gallery_img SET cat_id='.$move_to_cat.' WHERE id IN('.implode(',',$pictures).')') or error('Unable to move pictures', __FILE__, __LINE__, $db->error());
        	update_cat($id);
    		update_cat($move_to_cat);
     		redirect(FORUM_ROOT.'gallery.php?cid='.$id.$is_admmod_plink, $lang_gallery['Modo Move redirect']);
    	}
    	if (isset($_POST['move_img']))
    	{
    		$pictures = isset($_POST['pictures']) ? $_POST['pictures'] : array();
    		if (empty($pictures)) message($lang_gallery['Modo No select']);
    		$pictures = implode(',', array_keys($pictures));
    	}
    	else message($lang_common['Bad request']);
    	$page_title = convert_htmlspecialchars($configuration['o_board_name']).' / '.$lang_gallery['Modo link'];
    	require FORUM_ROOT.'header.php';
    ?>
    <div class="blockform">
    	<h2><span><?php echo $lang_gallery['Modo Move'] ?></span></h2>
    	<div class="box">
    		<form method="post" action="gallery.php?cid=<?php echo $id.$is_admmod_plink ?>">
    			<div class="inform">
    			<input type="hidden" name="pictures" value="<?php echo $pictures ?>" />
    				<fieldset>
    					<legend><?php echo $lang_gallery['Modo Move legend'] ?></legend>
    					<div class="infldset">
    						<label><?php echo $lang_gallery['Modo Move to'] ?>
    						<br /><select name="move_to_cat">
    <?php
    	$result = $db->query('SELECT c.id AS cid, c.cat_name FROM '.$db->prefix.'gallery_cat AS c LEFT JOIN '.$db->prefix.'gallery_perms AS gp ON (gp.cat_id=c.id AND gp.group_id='.$forum_user['group_id'].') WHERE (gp.read_cat IS NULL OR gp.read_cat=1) ORDER BY c.disp_position, c.id', true) or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
    	while ($cur_category = $db->fetch_assoc($result))
    	{
        if ($cur_category['cid'] != $id) echo "\t\t\t\t\t\t\t".'<option value="'.$cur_category['cid'].'">'.convert_htmlspecialchars($cur_category['cat_name']).'</option>'."\n";
    	}
    ?>
    							</optgroup>
    						</select>
    						<br /></label>
    					</div>
    				</fieldset>
    			</div>
    			<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input type="submit" class="b1" name="move_img_to" value="<?php echo $lang_gallery['Modo Move'] ?>" /></p>
    		</form>
    	</div>
    </div>
    <?php
    	require FORUM_ROOT.'footer.php';
      }
      if (isset($_REQUEST['delete_img']) || isset($_POST['delete_img_comply']))
      {
      	$pictures = isset($_POST['pictures']) ? $_POST['pictures'] : array();
      	if (empty($pictures)) message($lang_gallery['Modo No select']);
      	if (isset($_POST['delete_img_comply']))
      	{
        	if (!preg_match('#/gallery\.php#i', $_SERVER['HTTP_REFERER'])) message($lang_common['Bad referrer']);
      	if (preg_match('/[^0-9,]/', $pictures)) message($lang_common['Bad request']);
          $pictures = explode(',', $pictures);
          for ($i=0;$i<count($pictures);$i++)
          {
      		  $result_img = $db->query('SELECT id, poster_id, posted FROM '.$db->prefix.'gallery_img WHERE id='.$pictures[$i]) or error('Unable to select picture to delete', __FILE__, __LINE__, $db->error());
            $cur_img = $db->fetch_assoc($result_img);
      if($configuration['g_ftp_upload'] == 1)
      {
        $conn_id = ftp_connect($configuration['g_ftp_host']); 
        $login_result = ftp_login($conn_id, $configuration['g_ftp_login'], $configuration['g_ftp_pass']);  
        if ((!$conn_id) || (!$login_result)) message('<strong>'.$lang_gallery['Error Announce'].'</strong> '.$lang_gallery['Error FTP connect']);
        $del_picture = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.jpg');
        $del_picture = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.png');
        $del_picture = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.gif');
        $del_thumbs = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.jpg');
        $del_thumbs = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.png');
        $del_thumbs = @@ftp_delete($conn_id, $configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.gif');
        ftp_close($conn_id);
      }
      else
      {
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.jpg');
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.png');
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_'.$cur_img['posted'].'.gif');
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.jpg');
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.png');
      	@@unlink($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.gif');
      }  
        		$db->query('DELETE FROM '.$db->prefix.'gallery_img WHERE id='.$pictures[$i]) or error('Unable to delete picture '.$pictures[$i], __FILE__, __LINE__, $db->error());
          }
      		update_cat($id);
    		  redirect(FORUM_ROOT.'gallery.php?cid='.$id.$is_admmod_plink, $lang_gallery['Modo Del redirect']);
      	}
      	$page_title = convert_htmlspecialchars($configuration['o_board_name']).' / '.$lang_gallery['Modo link'];
      	require FORUM_ROOT.'header.php';
      ?>
      <div class="blockform">
      	<h2><?php echo $lang_gallery['Modo Del'] ?></h2>
      	<div class="box">
      		<form method="post" action="gallery.php?cid=<?php echo $id.$is_admmod_plink ?>">
      			<input type="hidden" name="pictures" value="<?php echo implode(',', array_keys($pictures)) ?>" />
      			<div class="inform">
      				<fieldset>
      					<legend><?php echo $lang_gallery['Modo Del legend'] ?></legend>
      					<div class="infldset">
      						<p><?php echo $lang_gallery['Modo Del confirm'] ?></p>
      					</div>
      				</fieldset>
      			</div>
      			<p><input type="button" class="b1" onclick="javascript:history.go(-1)" value="<?php echo $lang_common['Go back'] ?>"><input class="b1" type="submit" name="delete_img_comply" value="<?php echo $lang_gallery['Modo Del'] ?>" /></p>
      		</form>
      	</div>
      </div>
      <?php
      	require FORUM_ROOT.'footer.php';
      }
  }
  $page_title = convert_htmlspecialchars($configuration['o_board_name'].' / '.$cur_cat['cat_name']);
  define('ALLOW_INDEX', 1);
  require FORUM_ROOT.'header.php';
  ?>
  <div class="linkst">
  	<div class="inbox">
  		<p class="pagelink conl"><?php echo $paging_links ?></p>
  <?php echo $post_link.' '.$is_admmod_link ?>
  		<ul><li><a href="gallery.php"><?php echo $lang_gallery['Index'] ?></a>&nbsp;</li><li>&raquo;&nbsp;<?php echo convert_htmlspecialchars($cur_cat['cat_name']) ?></li></ul>
  		<div class="clearer"></div>
  	</div>
  </div>
  <?php
  if (isset($_GET['mod']) && $is_admmod) echo '<form method="post" action="gallery.php?cid='.$id.'&mod='.$forum_user['id'].'">'."\n";
  ?>
  <div id="vf" class="blocktable">
  	<h2><span><?php echo convert_htmlspecialchars($cur_cat['cat_name']) ?></span></h2>
  	<div class="box">
  		<div class="inbox">
  			<table cellspacing="0">
  			<thead>
  				<tr>
  					<th class="tcl" scope="col"><?php echo $lang_gallery['Picture'] ?></th>
  					<th class="tc2" scope="col"><?php echo $lang_gallery['Views'] ?></th>
  					<th class="tc2" style="width:<?php echo $configuration['g_max_width_thumbs']+$configuration['g_thumbs_margin']*2; ?>px;" scope="col"><?php echo $lang_gallery['Picture'] ?></th>
  					<th class="tcr" scope="col"><?php echo $lang_gallery['Date post'] ?></th>
  <?php
  if (isset($_GET['mod']) && $is_admmod) echo '					<th class="tcmod" scope="col">'.$lang_gallery['Modo Select'].'</th>'."\n";
  ?>
  				</tr>
  			</thead>
  			<tbody>
  <?php
  $sql = 'SELECT id, poster, poster_id, poster_ip, poster_email, subject, message, posted, num_views FROM '.$db->prefix.'gallery_img WHERE cat_id='.$id.' ORDER BY posted DESC LIMIT '.$start_from.', '.$configuration['g_disp_img_default'];
  $result = $db->query($sql) or error('Unable to fetch pictures list', __FILE__, __LINE__, $db->error());
  if ($db->num_rows($result))
  {
    if ($configuration['g_thumbs_margin'] < 0) $configuration['g_thumbs_margin'] = '0';
    else $configuration['g_thumbs_margin'] = $configuration['g_thumbs_margin']*2;
    require FORUM_ROOT.'include/parser.php';
  	while ($cur_img = $db->fetch_assoc($result))
  	{
  		if ($configuration['o_censoring'] == '1')
      	{
  			$cur_img['subject'] = censor_words($cur_img['subject']);
  			$cur_img['message'] = censor_words($cur_img['message']);
      	}
  		$icon_text = $lang_common['Normal icon'];
  		$item_status = '';
  		$icon_type = 'icon';
  		$subject = '<a href="gallery.php?pid='.$cur_img['id'].'">'.convert_htmlspecialchars($cur_img['subject']).'</a> <span class="byuser">'.$lang_common['by'].'&nbsp;'.convert_htmlspecialchars($cur_img['poster']).'</span>';
  		if (!$forum_user['is_guest'] && $cur_img['posted'] > $forum_user['last_visit'])
  		{
  			$icon_text .= ' '.$lang_common['New icon'];
  			$item_status .= ' inew';
  			$icon_type = 'icon inew';
  			$subject = '<strong>'.$subject.'</strong>';
  		}
      if($configuration['g_ftp_upload'] == 1)
      {
        if (@@fopen($configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.gif', 'r'))
          { $extension = '.gif'; $content_type = 'image/gif';}
        elseif (@@fopen($configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.jpg', 'r'))
          { $extension = '.jpg'; $content_type = 'image/jpeg';}
        elseif (@@fopen($configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.png', 'r'))
          { $extension = '.png'; $content_type = 'image/png';}
        else $extension = '';
      }
      else
      {
       if (file_exists($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.gif')) $extension = '.gif';
       elseif (file_exists($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.jpg')) $extension = '.jpg';
       elseif (file_exists($configuration['g_rep_upload'].'/'.$cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].'.png')) $extension = '.png';
       else $extension = '';
      }
      if($extension) $picture = $cur_img['poster_id'].'_thumbs_'.$cur_img['posted'].$extension;
      else $picture = 'pix.gif';
  ?>
  				<tr<?php if ($item_status != '') echo ' class="'.trim($item_status).'"'; ?>>
  					<td class="tcl">
  						<div class="intd">
  							<div class="<?php echo $icon_type ?>"><div class="nosize"><?php echo trim($icon_text) ?></div></div>
  							<div class="tclcon">
  								<?php echo $subject."\n" ?>
  								<br /><?php echo parse_message($cur_img['message'], 0)."\n" ?>
  <?php
  		if ($forum_user['g_id'] < USER_GUEST)
  		  {
        echo '<br />IP: <a href="gallery.php?get_host='.$cur_img['id'].'">'.$cur_img['poster_ip'].'</a>';
        if ($cur_img['poster_email']) echo ' | <a href="mailto:'.$cur_img['poster_email'].'">'.$lang_common['E-mail'].'</a>';
  			}
  ?>								
  							</div>
  						</div>
  					</td>
  					<td class="tc2"><?php echo $cur_img['num_views'] ?></td>
  					<td class="tc3">
              <div onclick="javascript:window.open('gallery.php?pid=<?php echo $cur_img['id'];?>', 'remote', 'menubar=no, toolbar=no, location=no, directories=no, status=no, scrollbars=no, resizable=no, dependent, width=800, height=600, left=50, top=50');" style="cursor:hand;width:<?php echo $configuration['g_max_width_thumbs']+$configuration['g_thumbs_margin']; ?>px;height:<?php echo $configuration['g_max_height_thumbs']+$configuration['g_thumbs_margin']; ?>px;background-color: #<?php echo $configuration['g_thumbs_bgcolor']; ?>;background-image: url(<?php if ($configuration['g_ftp_upload'] == '1') { echo $configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$picture; } else { echo $configuration['g_rep_upload'].'/'.$picture; }?>);background-repeat: no-repeat;background-position: center center;text-align:center;">
                &nbsp;
              </div>       
            </td>
  					<td class="tcr"><?php echo format_time($cur_img['posted'])  ?></td>
  <?php
  if (isset($_GET['mod']) && $is_admmod)
    echo '					<td class="tcmod"><input type="checkbox" name="pictures['.$cur_img['id'].']" value="1" /></td>'."\n";
  ?>
  				</tr>
  <?php
  	}
  }
  else
  {
  ?>
  				<tr>
  					<td class="tcl" colspan="4"><?php echo $lang_gallery['Empty gallery'] ?></td>
  				</tr>
  <?php
  }
  ?>
  			</tbody>
  			</table>
  		</div>
  	</div>
  </div>
  <div class="linksb">
  	<div class="inbox">
  		<p class="pagelink conl"><?php echo $lang_common['Pages'].': '.paginate($num_pages, $p, 'gallery.php?cid='.$id.$is_admmod_plink) ?></p>
  <?php
  if (isset($_GET['mod']) && $is_admmod) echo '		<p class="conr"><input type="submit" class="b1" name="move_img" value="'.$lang_gallery['Modo Move'].'" />&nbsp;&nbsp;<input class="b1" type="submit" name="delete_img" value="'.$lang_gallery['Modo Del'].'" /></p>'."\n";
  else
    {
  ?>  
      <?php echo $post_link.' '.$is_admmod_link ?>
  		<ul><li><a href="gallery.php"><?php echo $lang_gallery['Index'] ?></a>&nbsp;</li><li>&raquo;&nbsp;<?php echo convert_htmlspecialchars($cur_cat['cat_name']) ?></li></ul>
  <?php  
    }
  ?>
  		<div class="clearer"></div>
  	</div>
  </div>
  <?php
  if (isset($_GET['mod']) && $is_admmod) echo '</form>'."\n";
  require FORUM_ROOT.'footer.php';
}
else
{
  $page_title = convert_htmlspecialchars($lang_gallery['Page_title']);
  define('ALLOW_INDEX', 1);
  require FORUM_ROOT.'header.php';
  $result = $db->query('SELECT c.id AS cid, c.cat_name, c.cat_desc, c.moderators, c.num_img, c.last_post, c.last_poster, c.last_poster_id FROM '.$db->prefix.'gallery_cat AS c LEFT JOIN '.$db->prefix.'gallery_perms AS gp ON (gp.cat_id=c.id AND gp.group_id='.$forum_user['g_id'].') WHERE gp.read_cat IS NULL OR gp.read_cat=1 ORDER BY c.disp_position, c.id', true) or error('Unable to fetch category/gallery list', __FILE__, __LINE__, $db->error());
  $cur_category = 0;
  $cat_count = 0;
  if ($configuration['g_thumbs_margin'] < 0) $configuration['g_thumbs_margin'] = '0';
  else $configuration['g_thumbs_margin'] = $configuration['g_thumbs_margin']*2;
  while ($cur_cat = $db->fetch_assoc($result))
  {
  	$moderators = '';
  	if ($cat_count == 0)
  	{
  		++$cat_count;
  ?>
  <div id="idx<?php echo $cat_count ?>" class="blocktable">
  	<h2><span><?php echo $lang_gallery['Page_title'] ?></span></h2>
  	<div class="box">
  		<div class="inbox">
  			<table cellspacing="0">
  			<thead>
  				<tr>
  					<th class="tcl" scope="col"><?php echo $lang_gallery['Gallery'] ?></th>
  					<th class="tc2" scope="col"><?php echo $lang_gallery['Pictures'] ?></th>
  					<th class="tc2" style="width:<?php echo $configuration['g_max_width_thumbs']+$configuration['g_thumbs_margin']*2; ?>px;" scope="col"><?php echo $lang_gallery['Last post'] ?></th>
  					<th class="tcr" scope="col"><?php echo $lang_gallery['Date post'] ?></th>
  				</tr>
  			</thead>
  			<tbody>
  <?php
  	}
  	$item_status = '';
  	$icon_text = $lang_common['Normal icon'];
  	$icon_type = 'icon';
  	if (!$forum_user['is_guest'] && $cur_cat['last_post'] > $forum_user['last_visit'])
  	{
  		$item_status = 'inew';
  		$icon_text = $lang_common['New icon'];
  		$icon_type = 'icon inew';
  	}
  		$cat_field = '<h3><a href="gallery.php?cid='.$cur_cat['cid'].'">'.convert_htmlspecialchars($cur_cat['cat_name']).'</a></h3>';
  		$num_img = $cur_cat['num_img'];
  	if ($cur_cat['cat_desc'] != '') $cat_field .= "\n\t\t\t\t\t\t\t\t".$cur_cat['cat_desc'];
  	if ($cur_cat['last_post'] != '') $last_post = '<a href="gallery.php?cid='.$cur_cat['cid'].'">'.format_time($cur_cat['last_post']).'</a> <span class="byuser">'.$lang_common['by'].' '.convert_htmlspecialchars($cur_cat['last_poster']).'</span>';
  	else $last_post = '&nbsp;';
  	if ($cur_cat['moderators'] != '')
  	{
  		$mods_array = unserialize($cur_cat['moderators']);
  		$moderators = array();
  		while (list($mod_username, $mod_id) = @@each($mods_array))
  			$moderators[] = '<a href="profile.php?id='.$mod_id.'">'.convert_htmlspecialchars($mod_username).'</a>';
  		$moderators = "\t\t\t\t\t\t\t\t".'<p><em>('.$lang_common['Moderated by'].'</em> '.implode(', ', $moderators).')</p>'."\n";
  	}
      if($configuration['g_ftp_upload'] == 1)
      {
        if (@@fopen($configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$cur_cat['last_poster_id'].'_thumbs_'.$cur_cat['last_post'].'.gif', 'r'))
          { $extension = '.gif'; $content_type = 'image/gif';}
        elseif (@@fopen($configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$cur_cat['last_poster_id'].'_thumbs_'.$cur_cat['last_post'].'.jpg', 'r'))
          { $extension = '.jpg'; $content_type = 'image/jpeg';}
        elseif (@@fopen($configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$cur_cat['last_poster_id'].'_thumbs_'.$cur_cat['last_post'].'.png', 'r'))
          { $extension = '.png'; $content_type = 'image/png';}
        else $extension = '';
      }
      else
      {
       if (file_exists($configuration['g_rep_upload'].'/'.$cur_cat['last_poster_id'].'_thumbs_'.$cur_cat['last_post'].'.gif')) $extension = '.gif';
       elseif (file_exists($configuration['g_rep_upload'].'/'.$cur_cat['last_poster_id'].'_thumbs_'.$cur_cat['last_post'].'.jpg')) $extension = '.jpg';
       elseif (file_exists($configuration['g_rep_upload'].'/'.$cur_cat['last_poster_id'].'_thumbs_'.$cur_cat['last_post'].'.png')) $extension = '.png';
       else $extension = '';
      }  
      if($extension) $picture = $cur_cat['last_poster_id'].'_thumbs_'.$cur_cat['last_post'].$extension;
      else $picture = 'pix.gif';
  ?>
   				<tr<?php if ($item_status != '') echo ' class="'.$item_status.'"'; ?>>
  					<td class="tcl">
  						<div class="intd">
  							<div class="<?php echo $icon_type ?>"><div class="nosize"><?php echo $icon_text ?></div></div>
  							<div class="tclcon">
  								<?php echo $cat_field."\n".$moderators ?>
  							</div>
  						</div>
  					</td>
  					<td class="tc2"><?php echo $num_img ?></td>
  					<td class="tc3">
              <div style="width:<?php echo $configuration['g_max_width_thumbs']+$configuration['g_thumbs_margin']; ?>px;height:<?php echo $configuration['g_max_height_thumbs']+$configuration['g_thumbs_margin']; ?>px;background-color: #<?php echo $configuration['g_thumbs_bgcolor']; ?>;background-image: url(<?php if ($configuration['g_ftp_upload'] == '1') { echo $configuration['g_ftp_site'].'/'.$configuration['g_ftp_rep'].'/'.$picture; } else { echo $configuration['g_rep_upload'].'/'.$picture; }?>);background-repeat: no-repeat;background-position: center center;text-align:center;">
                <img style="width:<?php echo $configuration['g_max_width_thumbs']+$configuration['g_thumbs_margin']; ?>px;height:<?php echo $configuration['g_max_height_thumbs']+$configuration['g_thumbs_margin']; ?>px;border:1px solid #<?php echo $configuration['g_thumbs_bordercolor']; ?>;" src="<?php echo $configuration['g_rep_upload'].'/'; ?>pix.gif">
              </div>       
             </td>
  					<td class="tcr"><?php echo $last_post ?></td>
  				</tr>
  <?php
  }
  if ($cat_count > 0) echo "\t\t\t".'</tbody>'."\n\t\t\t".'</table>'."\n\t\t".'</div>'."\n\t".'</div>'."\n".'</div>'."\n\n";
  else echo '<div id="idx0" class="block"><div class="box"><div class="inbox"><p>'.$lang_gallery['Empty gallery'].'</p></div></div></div>';
  require FORUM_ROOT.'footer.php';
}
?>