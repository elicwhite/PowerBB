<?php
if ($post_count == '1'&& strpos($ads_config['ads_exclude_forums'], ','.$cur_topic['forum_id'].',') === FALSE && strpos($ads_config['ads_exclude_groups'], ','.$forum_user['g_id'].',') === FALSE)
{
	if ($ads_config['google_adsense_enabled'] == '1' || $ads_config['yahoo_ads_enabled'] == '1' || $ads_config['clicksor_ads_enabled'] == '1' || $ads_config['other_ads_enabled'] == '1')
		{
?>
			<div class="blockpost<?php echo $vtbg ?>">
				<h2><span><?php echo format_time($cur_post['posted']) ?></span></h2>
				<div class="box">
					<div class="inbox">
						<div class="postleft">
							<dl>
								<dt><strong><?php echo $ads_config['ads_bot_name'] ?></strong></dt>
								<dd class="usertitle"><?php echo $ads_config['ads_bot_tag'] ?></dd>
							</dl>
						</div>
						<div class="postright">
							<div class="postmsg">
								<?php
								if  ($ads_config['google_adsense_enabled'] == '1')
								{
								echo "
									<br />
									<div style=\"text-align: center\">
										<script type=\"text/javascript\">
										<!--
											google_ad_client = \"".$ads_config['google_ad_client']."\";
											google_ad_width = ".$ads_config['google_ad_width'].";
											google_ad_height = ".$ads_config['google_ad_height'].";
											google_ad_format = \"".$ads_config['google_ad_format']."\";
											google_ad_channel =\"".$ads_config['google_ad_channel']."\";
											google_ad_type = \"".$ads_config['google_ad_type']."\";
											google_color_border = \"".trim($ads_config['google_color_border'], '#')."\";
											google_color_bg = \"".trim($ads_config['google_color_bg'], '#')."\";
											google_color_link = \"".trim($ads_config['google_color_link'], '#')."\";
											google_color_url = \"".trim($ads_config['google_color_url'], '#')."\";
											google_color_text = \"".trim($ads_config['google_color_text'], '#')."\";
											google_alternate_color = \"".trim($ads_config['google_alternate_color'], '#')."\";
										//-->
										</script>
										<script type=\"text/javascript\" src=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\"></script>						
										</br>
										<span class=\"ads_message\">
											".$ads_config['ads_message']."
										</span>
									</div>
									<br />
									\n";
								}
								elseif ($ads_config['yahoo_ads_enabled'] == '1')
								{
									echo "<br />
									<div style=\"text-align: center\">
										<script type=\"text/javascript\">
										<!--
											ctxt_ad_partner = \"".$ads_config['yahoo_ad_client']."\";
											ctxt_ad_width = ".$ads_config['yahoo_ad_width'].";
											ctxt_ad_height = ".$ads_config['yahoo_ad_height'].";
											ctxt_ad_section =\"".$ads_config['yahoo_ad_channel']."\";
											ctxt_ad_bc = \"".trim($ads_config['yahoo_color_border'], '#')."\";
											ctxt_ad_bg = \"".trim($ads_config['yahoo_color_bg'], '#')."\";
											ctxt_ad_lc = \"".trim($ads_config['yahoo_color_link'], '#')."\";
											ctxt_ad_uc = \"".trim($ads_config['yahoo_color_url'], '#')."\";
											ctxt_ad_tc = \"".trim($ads_config['yahoo_color_text'], '#')."\";
											ctxt_ad_cc = \"".trim($ads_config['yahoo_alternate_color'], '#')."\";
										//-->
										</script>
										<script type=\"text/javascript\" src=\"http://ypn-js.overture.com/partner/js/ypn.js\"></script>
										</br><span class=\"ads_message\">".$ads_config['ads_message']."</span>
									</div><br />\n";
								} 
								elseif ($ads_config['clicksor_ads_enabled'] == '1')
								{
									echo "<br /><div style=\"text-align: center\">
										<script type=\"text/javascript\">
										<!--
											clicksor_default_url = \"".$ads_config['clicksor_default_url']."\";
											clicksor_enable_text_link = false;
											clicksor_layer_border_color = \"".$ads_config['clicksor_layer_border_color']."\";
											clicksor_layer_ad_bg = \"".$ads_config['clicksor_layer_ad_bg']."\";
											clicksor_layer_ad_link_color = \"".$ads_config['clicksor_layer_ad_link_color']."\";
											clicksor_layer_ad_text_color = \"".$ads_config['clicksor_layer_ad_text_color']."\";
											clicksor_text_link_bg = \"".$ads_config['clicksor_text_link_bg']."\";
											clicksor_text_link_color = \"".$ads_config['clicksor_text_link_color']."\";
											clicksor_banner_image_banner = \"".$ads_config['clicksor_banner_image_banner']."\";
											clicksor_banner_border = \"".$ads_config['clicksor_banner_border']."\";
											clicksor_banner_ad_bg = \"".$ads_config['clicksor_banner_ad_bg']."\";
											clicksor_banner_link_color = \"".$ads_config['clicksor_banner_link_color']."\";
											clicksor_banner_text_color = \"".$ads_config['clicksor_banner_text_color']."\";
										//-->
										</script>
										<script type=\"text/javascript\" src=\"http://ads.clicksor.com/showAd.php?pid=".$ads_config['clicksor_pid']."&sid=".$ads_config['clicksor_sid']."&adtype=".$ads_config['clicksor_ad_format']."\"></script>  						
										</br><span class=\"ads_message\">".$ads_config['ads_message']."</span>
										</div><br />\n";
								}
								elseif ($ads_config['other_ads_enabled'] == '1')
								{
									echo "<br /><div style=\"text-align: center\">
										".$ads_config['other_message']."
										</div><br />\n";
								}						
								?>
							</div>
						</div>
						<div class="clearer"></div>
					</div>
				</div>
			</div>
<?php
	}
}
?>