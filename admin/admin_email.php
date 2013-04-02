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
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin.php';
if ($forum_user['g_id'] > USER_MOD) message($lang_common['No permission']);
$page_title = convert_htmlspecialchars($configuration['o_board_name'])." (Powered By PowerBB Forums) / Logs";
require FORUM_ROOT.'header.php';
if (isset($_POST['confirm']))
{
	if (trim($_POST['message_body']) == '')
		message('You didn\'t enter a message body!');
	if (trim($_POST['message_subject']) == '')
		message('You didn\'t enter a subject!');
	generate_admin_menu("email");
	
	$preview_message_body = nl2br(htmlspecialchars($_POST['message_body']));
	
	/* Grab all the info about users from the database */
	$sql = "SELECT * FROM ".$db->prefix."users
				WHERE username != 'Guest'
				ORDER BY username";	
	
	/* put that data in the result variable */
	$result = $db->query($sql) or error('Could not get user count from database', __FILE__, __LINE__, $db->error());
	
?>

<div class="blockform">
  <h2><span>Mass Email - Confirm</span></h2>
  <div class="box">
    <div class="inbox">
      <p>Please confirm your message below.<br />
        <br />
        If something is not correct, please <a href="javascript: history.go(-1)">Go Back</a>.</p>
    </div>
  </div>
  <h2 class="block2"><span>Confirm Message</span></h2>
  <div class="box">
    <form id="massemail" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
      <div class="inform">
        <input type="hidden" name="message_subject" value="<?php echo htmlspecialchars($_POST['message_subject']) ?>" />
        <input type="hidden" name="message_body" value="<?php echo htmlspecialchars($_POST['message_body']) ?>" />
        <?php
		
		/* Cycle through the groups, and check the id's */
		while ($row = $db->fetch_assoc($result)){
			if ($_POST['group_'.$row['group_id']] == "on"){ //If the ID group was selected
				$usercount++; // Increase user count
				// Create a hidden input variable for next form : *Does not make multiple values for same group*
				echo "<input type=\"hidden\" name=\"group_".$row['group_id']."\" value=\"on\" />"; 
			}
		}
				?>
        <fieldset>
        <legend>Message Recipients</legend>
        <div class="infldset"> [ <strong><?php echo $usercount; ?></strong> ] Registered Users will receive this message (including the Administrator). </div>
        </fieldset>
      </div>
      <div class="inform">
        <fieldset>
        <legend>Message Contents</legend>
        <div class="infldset">
          <table class="aligntop" cellspacing="0">
            <tr>
              <th scope="row">Subject</th>
              <td><?php echo htmlspecialchars($_POST['message_subject']) ?> </td>
            </tr>
            <tr>
              <th scope="row">Body</th>
              <td><?php 
			  /* Format HTML Tags if option's chosen */
			  if($_POST['format'] == 'html') {
			  	echo $_POST['message_body']; 
			  }
			  /* Preview in plain text */
			  else {
			  	echo $preview_message_body;
			  } 
			   ?></td>
            </tr>
            <tr>
              <td colspan="2"><?php
			  /* If user chose HTML, set up hidden variable to pass to e-mail script and notify of format being used */
				if($_POST['format'] == 'html') {
					echo "<input type=\"hidden\" name=\"format\" value=\"html\" />";
					echo "<p>E-mail will be sent in HTML format.</p>";
				}
		
				/* Else set hidden variable to pass as plain text */
				else {
					echo "<input type=\"hidden\" name=\"format\" value=\"plain\" />";
					echo "<p>E-mail will be sent in Plain Text format.</p>";
				}
				?>
              </td>
            </tr>
          </table>
          <div class="fsetsubmit">
            <input type="submit" name="send_message" value="Confirmed - Send It." tabindex="3" />
          </div>
          <p class="topspace">Please hit this button only once. Patience is key.</p>
        </div>
        </fieldset>
      </div>
    </form>
  </div>
</div>
<?php

}

// --------------------------------------------------------------------

// Send the Message

else if (isset($_POST['send_message']))
{
	require_once FORUM_ROOT.'include/email.php';
	generate_admin_menu("email");

	/* Snag the data again */
	$sql = "SELECT username, email, group_id
				FROM ".$db->prefix."users
				WHERE username != 'Guest'
				ORDER BY username";	
				
	/* Slap it into a variable */
	$result = $db->query($sql) or error('Could not get users from the database', __FILE__, __LINE__, $db->error());
	
	/* Start count of users with e-mails sent to them */
	$usercount = 0;
	
	/*********************
	Set up e-mail headers
	*********************/
	
		/* Check to see if it should be in HTML */
		if($_POST['format'] == 'html') {
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		}
			
		else {
			/* blank the headers */
			$headers = "";
		}
			
		// Put the sender's e-mail in the from section
		$headers .= 'From: '.$forum_user['email']."\r\n";
		
	/* Cycle through SQL Data */
	while ($row = mysql_fetch_assoc($result)){
	
		/* Check to see if a hiden variable for that user group has been placed
			If so, send that user an e-mail and increase usercount */
		if ($_POST['group_'.$row['group_id']] == "on"){
				
			// Build rest of mail function
			$to        = $row['username']." <".$row['email'].">";
			$subject   = $_POST['message_subject'];
			$message   = $_POST['message_body'];

			/* Fire the e-mail off */
			mail($to, $subject, $message, $headers);
			$usercount++;		
	
		}
	}
?>
<div class="block">
  <h2><span>Mass Email - Message Sent</span></h2>
  <div class="box">
    <div class="inbox">
      <p>The message was sent to [ <strong><?php echo $usercount; ?></strong> ] Registered Users.</p>
      <p>You should receive the Administrator's copy in a few moments.</p>
      <p>Please use the Administrator's copy as a record of this event.</p>
    </div>
  </div>
</div>
<?php

}

// --------------------------------------------------------------------

// Display the Main Page

else
{
	// Display the admin navigation menu
	generate_admin_menu("email");

?>
<div id="exampleplugin" class="blockform">
  <h2><span>Mass Email</span></h2>
  <div class="box">
    <div class="inbox">
      <p>This plugin allows the Administrator to send a Mass Email to all registered users.</p>
      <p>There will be a confirmation page after this one - to make sure you have not made any mistakes.</p>
	  <?php //Added this statement ?>
	  <p>You can use HTML in this form. If you do so, remember to include all the proper HTML tags.</p>
    </div>
  </div>
  <h2 class="block2"><span>Compose Message</span></h2>
  <div class="box">
    <form id="Massemail" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
      <div class="inform">
        <fieldset>
        <legend>Message Contents</legend>
        <div class="infldset">
          <table class="aligntop" cellspacing="0">
            <tr>
              <th scope="row">Subject</th>
              <td><input type="text" name="message_subject" size="50" tabindex="1" />
              </td>
            </tr>
            <tr>
              <th scope="row">Body</th>
              <td><textarea name="message_body" rows="14" cols="48" tabindex="2"></textarea>
              </td>
            </tr>
            <th scope="row">Groups</th>
              <?php	
			  		/* Grab the user groups and their ID numbers */
					$sql = "SELECT g_title, g_id 
					FROM ".$db->prefix."groups
					ORDER BY g_title";
					
					/* Throw the result into a variable */	
					$result = $db->query($sql) or error('Could not get users from the database', __FILE__, __LINE__, $db->error());
					
					$tabcounter=3; //Start tabindex on 3
					
					/* Cycle through the results, displaying each usergroup */
					while ($row = mysql_fetch_assoc($result)){
					echo "<tr>
					<td>".$row['g_title']."</td>
					<td><input type=\"checkbox\" name=\"group_".$row['g_id']."\" tabindex=\"".$tabcounter."\" />
					</tr>";
					$tabcounter++; //bump up the tab counter
					}			
					?>
            <tr>
              <th scope="row">E-Mail Format</th>
            <tr>
              <td>HTML:</td><td>
                <input type="radio" name="format" value="html" checked="checked" /></td>
            </tr>
            <tr>
              <td>Plain Text:</td><td>
                <input type="radio" name="format" value="plain" /></td>
            </tr>
            </tr>
            
          </table>
          <div class="fsetsubmit">
            <!--Tab counter here set from previous PHP script-->
            <input type="submit" name="confirm" value="Continue to Confirmation" tabindex="<?php echo $tabcounter; ?>" />
          </div>
        </div>
        </fieldset>
      </div>
    </form>
  </div>
</div>
<?php

}
?>
</div>
<div class="clearer"> </div>
</div>
<?php
require FORUM_ROOT.'admin/admin_footer.php'; ?>
