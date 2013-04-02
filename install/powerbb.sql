-- phpMyAdmin SQL Dump
-- version 2.7.0-beta1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Feb 06, 2006 at 05:46 AM
-- Server version: 5.0.4
-- PHP Version: 4.4.2
-- 
-- Database: `revo`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `revo_advertising_config`
-- 

CREATE TABLE `revo_advertising_config` (
  `conf_name` varchar(255) NOT NULL default '',
  `conf_value` text,
  PRIMARY KEY  (`conf_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `revo_advertising_config`
-- 

INSERT INTO `revo_advertising_config` VALUES ('ads_bot_name', 'AdBot');
INSERT INTO `revo_advertising_config` VALUES ('ads_bot_tag', 'AdsPosting Bot');
INSERT INTO `revo_advertising_config` VALUES ('ads_exclude_forums', '');
INSERT INTO `revo_advertising_config` VALUES ('ads_exclude_groups', '');
INSERT INTO `revo_advertising_config` VALUES ('google_adsense_enabled', '0');
INSERT INTO `revo_advertising_config` VALUES ('google_ad_client', 'pub-0000000000000000');
INSERT INTO `revo_advertising_config` VALUES ('google_ad_width', '468');
INSERT INTO `revo_advertising_config` VALUES ('google_ad_height', '60');
INSERT INTO `revo_advertising_config` VALUES ('google_ad_format', '468x60_as');
INSERT INTO `revo_advertising_config` VALUES ('google_ad_channel', '');
INSERT INTO `revo_advertising_config` VALUES ('google_ad_type', 'text');
INSERT INTO `revo_advertising_config` VALUES ('google_color_border', '#000000');
INSERT INTO `revo_advertising_config` VALUES ('google_color_bg', '#FFFFFF');
INSERT INTO `revo_advertising_config` VALUES ('google_color_link', '#005CB1');
INSERT INTO `revo_advertising_config` VALUES ('google_color_url', '#005CB1');
INSERT INTO `revo_advertising_config` VALUES ('google_color_text', '#333333');
INSERT INTO `revo_advertising_config` VALUES ('google_alternate_color', '#CCCCCC');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_ads_enabled', '0');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_default_url', '');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_layer_border_color', '#B4D0DC');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_layer_ad_bg', '#ECF8FF');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_layer_ad_link_color', '#0000CC');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_layer_ad_text_color', '#000000');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_text_link_bg', '');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_text_link_color', '#000FFF');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_enable_text_link', 'true');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_banner_image_banner', 'true');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_banner_border', '#6666FF');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_banner_ad_bg', '#CCCCFF');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_banner_link_color', '#FF0000');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_banner_text_color', '#000000');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_pid', '42355');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_sid', '55444');
INSERT INTO `revo_advertising_config` VALUES ('clicksor_ad_format', '1');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_ads_enabled', '0');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_ad_client', '0000000000');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_ad_width', '468');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_ad_height', '60');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_ad_channel', '');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_color_border', '#FFFFFF');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_color_bg', '#FFFFFF');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_color_link', '#005CB1');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_color_url', '#005CB1');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_color_text', '#333333');
INSERT INTO `revo_advertising_config` VALUES ('yahoo_alternate_color', '#FFFFFF');

-- --------------------------------------------------------

-- 
-- Table structure for table `revo_bans`
-- 

CREATE TABLE `revo_bans` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(200) default NULL,
  `ip` varchar(255) default NULL,
  `email` varchar(50) default NULL,
  `message` varchar(255) default NULL,
  `expire` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_bans`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_bots`
-- 

CREATE TABLE `revo_bots` (
  `id` int(7) unsigned NOT NULL auto_increment,
  `bot_alias` varchar(60) NOT NULL default '',
  `bot_string` varchar(60) NOT NULL default '',
  `time_stamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- 
-- Dumping data for table `revo_bots`
-- 

INSERT INTO `revo_bots` VALUES (1, 'Googlebot', 'googlebot', 1134690263);
INSERT INTO `revo_bots` VALUES (2, 'MSNBot', 'msnbot', 1134817569);
INSERT INTO `revo_bots` VALUES (3, 'WISENutbot', 'wisenutbot', 0);
INSERT INTO `revo_bots` VALUES (4, 'Alexa', 'ia_archiver', 1133644125);
INSERT INTO `revo_bots` VALUES (5, 'Yahoo Slurp', 'yahoo!', 1134248023);
INSERT INTO `revo_bots` VALUES (6, 'AskJeeves', 'ask jeeves/teoma', 1133839205);
INSERT INTO `revo_bots` VALUES (7, 'Google AdSense', 'mediapartners-google', 1133452288);

-- --------------------------------------------------------

-- 
-- Table structure for table `revo_botsconfig`
-- 

CREATE TABLE `revo_botsconfig` (
  `id` int(7) unsigned NOT NULL auto_increment,
  `display_time` int(4) NOT NULL default '5',
  `isCaseSensitive` int(1) NOT NULL default '0',
  `isEnabled` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `revo_botsconfig`
-- 

INSERT INTO `revo_botsconfig` VALUES (1, 10, 0, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `revo_calendar`
-- 

CREATE TABLE `revo_calendar` (
  `id` int(100) NOT NULL auto_increment,
  `date` date NOT NULL default '0000-00-00',
  `title` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `user_id` int(100) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_calendar`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_categories`
-- 

CREATE TABLE `revo_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `cat_name` varchar(80) NOT NULL default 'New Category',
  `disp_position` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `revo_categories`
-- 

INSERT INTO `revo_categories` VALUES (1, 'Test category', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `revo_censoring`
-- 

CREATE TABLE `revo_censoring` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `search_for` varchar(60) NOT NULL default '',
  `replace_with` varchar(60) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_censoring`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_config`
-- 

CREATE TABLE `revo_config` (
  `conf_name` varchar(255) NOT NULL default '',
  `conf_value` text,
  PRIMARY KEY  (`conf_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `revo_config`
-- 

INSERT INTO `revo_config` VALUES ('o_cur_version', '1.8.0');
INSERT INTO `revo_config` VALUES ('o_board_title', 'My Revo Forum');
INSERT INTO `revo_config` VALUES ('o_board_desc', 'Binary Arts - www.binary-arts.com.');
INSERT INTO `revo_config` VALUES ('o_board_meta', '(C) 2005 Binary ARTS. All rights reserved.');
INSERT INTO `revo_config` VALUES ('o_server_timezone', '0');
INSERT INTO `revo_config` VALUES ('o_time_format', 'H:i:s');
INSERT INTO `revo_config` VALUES ('o_date_format', 'Y-m-d');
INSERT INTO `revo_config` VALUES ('o_timeout_visit', '600');
INSERT INTO `revo_config` VALUES ('o_timeout_online', '300');
INSERT INTO `revo_config` VALUES ('o_redirect_delay', '1');
INSERT INTO `revo_config` VALUES ('o_show_version', '1');
INSERT INTO `revo_config` VALUES ('o_show_user_info', '1');
INSERT INTO `revo_config` VALUES ('o_show_post_count', '1');
INSERT INTO `revo_config` VALUES ('o_smilies', '1');
INSERT INTO `revo_config` VALUES ('o_smilies_sig', '1');
INSERT INTO `revo_config` VALUES ('o_make_links', '1');
INSERT INTO `revo_config` VALUES ('o_default_lang', 'English');
INSERT INTO `revo_config` VALUES ('o_default_style', 'Default');
INSERT INTO `revo_config` VALUES ('o_default_user_group', '4');
INSERT INTO `revo_config` VALUES ('o_topic_review', '15');
INSERT INTO `revo_config` VALUES ('o_disp_topics_default', '30');
INSERT INTO `revo_config` VALUES ('o_disp_posts_default', '25');
INSERT INTO `revo_config` VALUES ('o_indent_num_spaces', '4');
INSERT INTO `revo_config` VALUES ('o_quickpost', '1');
INSERT INTO `revo_config` VALUES ('o_users_online', '1');
INSERT INTO `revo_config` VALUES ('o_censoring', '0');
INSERT INTO `revo_config` VALUES ('o_ranks', '1');
INSERT INTO `revo_config` VALUES ('o_show_dot', '0');
INSERT INTO `revo_config` VALUES ('o_quickjump', '1');
INSERT INTO `revo_config` VALUES ('o_gzip', '0');
INSERT INTO `revo_config` VALUES ('o_additional_navlinks', '');
INSERT INTO `revo_config` VALUES ('o_report_method', '0');
INSERT INTO `revo_config` VALUES ('o_regs_report', '0');
INSERT INTO `revo_config` VALUES ('o_mailing_list', 'you@yourdomain.com');
INSERT INTO `revo_config` VALUES ('o_avatars', '1');
INSERT INTO `revo_config` VALUES ('o_avatars_dir', 'img/avatars');
INSERT INTO `revo_config` VALUES ('o_avatars_width', '60');
INSERT INTO `revo_config` VALUES ('o_avatars_height', '60');
INSERT INTO `revo_config` VALUES ('o_avatars_size', '10240');
INSERT INTO `revo_config` VALUES ('o_search_all_forums', '1');
INSERT INTO `revo_config` VALUES ('o_base_url', 'http://www.yourdomain.com/forum');
INSERT INTO `revo_config` VALUES ('o_admin_email', 'you@yourdomain.com');
INSERT INTO `revo_config` VALUES ('o_webmaster_email', 'you@yourdomain.com');
INSERT INTO `revo_config` VALUES ('o_subscriptions', '1');
INSERT INTO `revo_config` VALUES ('o_smtp_host', NULL);
INSERT INTO `revo_config` VALUES ('o_smtp_user', NULL);
INSERT INTO `revo_config` VALUES ('o_smtp_pass', NULL);
INSERT INTO `revo_config` VALUES ('o_regs_allow', '1');
INSERT INTO `revo_config` VALUES ('o_regs_verify', '0');
INSERT INTO `revo_config` VALUES ('o_announcement', '0');
INSERT INTO `revo_config` VALUES ('o_announcement_message', 'Enter your announcement here.');
INSERT INTO `revo_config` VALUES ('o_rules', '0');
INSERT INTO `revo_config` VALUES ('o_rules_message', 'Enter your rules here.');
INSERT INTO `revo_config` VALUES ('o_maintenance', '0');
INSERT INTO `revo_config` VALUES ('o_maintenance_message', 'The forums are temporarily down for maintenance. Please try again in a few minutes.<br />\n<br />\n/Administrator');
INSERT INTO `revo_config` VALUES ('p_mod_edit_users', '1');
INSERT INTO `revo_config` VALUES ('p_mod_rename_users', '0');
INSERT INTO `revo_config` VALUES ('p_mod_change_passwords', '0');
INSERT INTO `revo_config` VALUES ('p_mod_ban_users', '0');
INSERT INTO `revo_config` VALUES ('p_message_bbcode', '1');
INSERT INTO `revo_config` VALUES ('p_message_img_tag', '1');
INSERT INTO `revo_config` VALUES ('p_message_all_caps', '1');
INSERT INTO `revo_config` VALUES ('p_subject_all_caps', '1');
INSERT INTO `revo_config` VALUES ('p_sig_all_caps', '1');
INSERT INTO `revo_config` VALUES ('p_sig_bbcode', '1');
INSERT INTO `revo_config` VALUES ('p_sig_img_tag', '0');
INSERT INTO `revo_config` VALUES ('p_sig_length', '400');
INSERT INTO `revo_config` VALUES ('p_sig_lines', '4');
INSERT INTO `revo_config` VALUES ('p_allow_banned_email', '1');
INSERT INTO `revo_config` VALUES ('p_allow_dupe_email', '0');
INSERT INTO `revo_config` VALUES ('p_force_guest_email', '1');
INSERT INTO `revo_config` VALUES ('p_math_gen', '0');
INSERT INTO `revo_config` VALUES ('p_ext_editor', '0');
INSERT INTO `revo_config` VALUES ('p_is_upload', '0');
INSERT INTO `revo_config` VALUES ('iu_allowed_ext', 'gif,png,jpg,jpeg');
INSERT INTO `revo_config` VALUES ('iu_max_width', '800');
INSERT INTO `revo_config` VALUES ('iu_max_height', '600');
INSERT INTO `revo_config` VALUES ('iu_max_size', '524288');
INSERT INTO `revo_config` VALUES ('iu_thumb_width', '100');
INSERT INTO `revo_config` VALUES ('iu_thumb_height', '100');
INSERT INTO `revo_config` VALUES ('iu_table_cols', '4');
INSERT INTO `revo_config` VALUES ('iu_max_post_images', '4');
INSERT INTO `revo_config` VALUES ('iu_upload_path', '');
INSERT INTO `revo_config` VALUES ('o_reputation_timeout', '65530');
INSERT INTO `revo_config` VALUES ('o_reputation_enabled', '1');
INSERT INTO `revo_config` VALUES ('o_guest_information_message', 'Please register!');
INSERT INTO `revo_config` VALUES ('o_guest_information', '0');
INSERT INTO `revo_config` VALUES ('o_advertisement_message', 'Enter your advertisement here.');
INSERT INTO `revo_config` VALUES ('o_advertisement', '0');
INSERT INTO `revo_config` VALUES ('o_information_message', 'Enter your information here.');
INSERT INTO `revo_config` VALUES ('o_information', '0');
INSERT INTO `revo_config` VALUES ('o_poll_max_fields', '6');
INSERT INTO `revo_config` VALUES ('o_regs_verify_image', '1');
INSERT INTO `revo_config` VALUES ('o_most_active', '10');
INSERT INTO `revo_config` VALUES ('o_boardstats_enable', '1');
INSERT INTO `revo_config` VALUES ('o_onlist_enable', '1');
INSERT INTO `revo_config` VALUES ('o_um_enable', '0');
INSERT INTO `revo_config` VALUES ('o_um_key', '');
INSERT INTO `revo_config` VALUES ('o_um_default_zoom', '12');
INSERT INTO `revo_config` VALUES ('o_um_default_lat', '44.80');
INSERT INTO `revo_config` VALUES ('o_um_default_lng', '25.70');
INSERT INTO `revo_config` VALUES ('o_board_meta_author', '1');
INSERT INTO `revo_config` VALUES ('o_board_meta_keywords', 'revo, revo forum, binary arts, php script, mysql, integration');
INSERT INTO `revo_config` VALUES ('o_rewrite_urls', '0');
INSERT INTO `revo_config` VALUES ('o_lic_name', 'Someone');
INSERT INTO `revo_config` VALUES ('o_lic_company', 'Some Company');
INSERT INTO `revo_config` VALUES ('o_lic_code', '8jkfsdi548if84');
INSERT INTO `revo_config` VALUES ('o_notes', '');
INSERT INTO `revo_config` VALUES ('o_notes_todo', '');
INSERT INTO `revo_config` VALUES ('o_pms_enabled', '1');
INSERT INTO `revo_config` VALUES ('o_pms_mess_per_page', '10');
INSERT INTO `revo_config` VALUES ('o_coll_cat', '1');
INSERT INTO `revo_config` VALUES ('o_click_row', '1');
INSERT INTO `revo_config` VALUES ('o_invitations_enable', '0');
INSERT INTO `revo_config` VALUES ('o_digests_enable', '0');
INSERT INTO `revo_config` VALUES ('o_enable_country', '1');
INSERT INTO `revo_config` VALUES ('o_rss_type', '2');
INSERT INTO `revo_config` VALUES ('o_active_topics_nr', '5');
INSERT INTO `revo_config` VALUES ('o_invitation_message', '---------------');
INSERT INTO `revo_config` VALUES ('o_forum_email_divider', '*****************************************************************');
INSERT INTO `revo_config` VALUES ('o_topic_email_divider', '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -');
INSERT INTO `revo_config` VALUES ('o_message_email_divider', '------');
INSERT INTO `revo_config` VALUES ('o_weekly_digest_day', '0');
-- --------------------------------------------------------

-- 
-- Table structure for table `revo_digest_subscribed_forums`
-- 

CREATE TABLE `revo_digest_subscribed_forums` (
  `user_id` int(10) NOT NULL default '0',
  `forum_id` int(10) NOT NULL default '0',
  UNIQUE KEY `user_id` (`user_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `revo_digest_subscribed_forums`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_digest_subscriptions`
-- 

CREATE TABLE `revo_digest_subscriptions` (
  `user_id` int(10) NOT NULL default '0',
  `digest_type` enum('DAY','WEEK') NOT NULL default 'DAY',
  `show_text` enum('YES','NO') NOT NULL default 'YES',
  `show_mine` enum('YES','NO') NOT NULL default 'YES',
  `new_only` enum('TRUE','FALSE') NOT NULL default 'TRUE',
  `send_on_no_messages` enum('YES','NO') NOT NULL default 'NO',
  `text_length` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `revo_digest_subscriptions`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_expertise_links`
-- 

CREATE TABLE `revo_expertise_links` (
  `tagger_id` int(10) unsigned default NULL,
  `tag_id` int(10) unsigned default NULL,
  `taggee_id` int(10) unsigned default NULL,
  `created_at` datetime default NULL,
  `confirmed` tinyint(1) NOT NULL default '0',
  `confirmed_at` datetime default NULL,
  UNIQUE KEY `tuple` (`tagger_id`,`tag_id`,`taggee_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `revo_expertise_links`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_expertise_tags`
-- 

CREATE TABLE `revo_expertise_tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_expertise_tags`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_forum_perms`
-- 

CREATE TABLE `revo_forum_perms` (
  `group_id` int(10) NOT NULL default '0',
  `forum_id` int(10) NOT NULL default '0',
  `read_forum` tinyint(1) NOT NULL default '1',
  `post_replies` tinyint(1) NOT NULL default '1',
  `post_topics` tinyint(1) NOT NULL default '1',
  `image_upload` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `revo_forum_perms`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_forums`
-- 

CREATE TABLE `revo_forums` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `forum_name` varchar(80) NOT NULL default 'New forum',
  `forum_desc` text,
  `redirect_url` varchar(100) default NULL,
  `moderators` text,
  `num_topics` mediumint(8) unsigned NOT NULL default '0',
  `num_posts` mediumint(8) unsigned NOT NULL default '0',
  `last_post` int(10) unsigned default NULL,
  `last_post_id` int(10) unsigned default NULL,
  `last_poster` varchar(200) default NULL,
  `sort_by` tinyint(1) NOT NULL default '0',
  `disp_position` int(10) NOT NULL default '0',
  `cat_id` int(10) unsigned NOT NULL default '0',
  `parent_forum_id` int(10) unsigned default '0',
  `protected` int(11) NOT NULL default '0',
  `password` varchar(255) NOT NULL default '',
  `valide` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `revo_forums`
-- 

INSERT INTO `revo_forums` VALUES (1, 'Test forum', 'This is just a test forum', NULL, NULL, 1, 1, 1139197540, 1, 'Admin', 0, 1, 1, 0, 0, '', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `revo_groups`
-- 

CREATE TABLE `revo_groups` (
  `g_id` int(10) unsigned NOT NULL auto_increment,
  `g_title` varchar(50) NOT NULL default '',
  `g_user_title` varchar(50) default NULL,
  `g_read_board` tinyint(1) NOT NULL default '1',
  `g_post_replies` tinyint(1) NOT NULL default '1',
  `g_post_topics` tinyint(1) NOT NULL default '1',
  `g_post_polls` tinyint(1) NOT NULL default '1',
  `g_edit_posts` tinyint(1) NOT NULL default '1',
  `g_delete_posts` tinyint(1) NOT NULL default '1',
  `g_delete_topics` tinyint(1) NOT NULL default '1',
  `g_set_title` tinyint(1) NOT NULL default '1',
  `g_search` tinyint(1) NOT NULL default '1',
  `g_search_users` tinyint(1) NOT NULL default '1',
  `g_edit_subjects_interval` smallint(6) NOT NULL default '300',
  `g_post_flood` smallint(6) NOT NULL default '30',
  `g_search_flood` smallint(6) NOT NULL default '30',
  `g_color` varchar(10) NOT NULL default '',
  `g_view_users` tinyint(1) NOT NULL default '1',
  `g_chat` tinyint(4) NOT NULL default '1',
  `g_pm` int(11) NOT NULL default '1',
  `g_pm_limit` int(11) NOT NULL default '20',
  `g_invitations` int(11) NOT NULL default '0',
  PRIMARY KEY  (`g_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `revo_groups`
-- 

INSERT INTO `revo_groups` VALUES (1, 'Administrators', 'Administrator', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, '', 1, 1, 1, 50, 999);
INSERT INTO `revo_groups` VALUES (2, 'Moderators', 'Moderator', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, '', 1, 1, 1, 20, 50);
INSERT INTO `revo_groups` VALUES (3, 'Guest', NULL, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, '', 1, 1, 0, 0, 0);
INSERT INTO `revo_groups` VALUES (4, 'Members', NULL, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 300, 60, 30, '', 1, 1, 1, 20, 10);

-- --------------------------------------------------------

-- 
-- Table structure for table `revo_invitations`
-- 

CREATE TABLE `revo_invitations` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `code` varchar(32) NOT NULL default '',
  `recipient` varchar(96) NOT NULL default '',
  `recipient_text` text,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `sent` timestamp NOT NULL default '0000-00-00 00:00:00',
  `used` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_invitations`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_logs`
-- 

CREATE TABLE `revo_logs` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(60) NOT NULL default '',
  `userid` int(11) NOT NULL default '0',
  `page` varchar(35) NOT NULL default '',
  `type` int(11) NOT NULL default '0',
  `ip` varchar(100) NOT NULL default '',
  `time` int(11) NOT NULL default '0',
  `data` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_logs`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_messages`
-- 

CREATE TABLE `revo_messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `owner` int(10) NOT NULL default '0',
  `subject` varchar(120) NOT NULL default '',
  `message` text,
  `sender` varchar(120) NOT NULL default '',
  `sender_id` int(10) NOT NULL default '0',
  `posted` int(10) NOT NULL default '0',
  `sender_ip` varchar(120) default NULL,
  `smileys` tinyint(4) default '1',
  `status` tinyint(4) default '0',
  `showed` tinyint(4) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_messages`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_online`
-- 

CREATE TABLE `revo_online` (
  `user_id` int(10) unsigned NOT NULL default '1',
  `ident` varchar(200) NOT NULL default '',
  `logged` int(10) unsigned NOT NULL default '0',
  `idle` tinyint(1) NOT NULL default '0',
  `color` varchar(10) NOT NULL default '',
  `current_page` varchar(100) default NULL,
  `current_page_id` int(10) default NULL,
  `current_ip` varchar(20) default NULL,
  KEY `revo_online_user_id_idx` (`user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `revo_online`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_pages`
-- 

CREATE TABLE `revo_pages` (
  `id` int(10) NOT NULL auto_increment,
  `title` varchar(75) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_pages`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_polls`
-- 

CREATE TABLE `revo_polls` (
  `id` int(11) NOT NULL auto_increment,
  `pollid` int(11) NOT NULL default '0',
  `options` longtext NOT NULL,
  `voters` longtext NOT NULL,
  `ptype` tinyint(4) NOT NULL default '0',
  `votes` longtext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_polls`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_posts`
-- 

CREATE TABLE `revo_posts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `poster` varchar(200) NOT NULL default '',
  `poster_id` int(10) unsigned NOT NULL default '1',
  `poster_ip` varchar(15) default NULL,
  `poster_email` varchar(50) default NULL,
  `message` text NOT NULL,
  `hide_smilies` tinyint(1) NOT NULL default '0',
  `posted` int(10) unsigned NOT NULL default '0',
  `edited` int(10) unsigned default NULL,
  `edited_by` varchar(200) default NULL,
  `topic_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `revo_posts_topic_id_idx` (`topic_id`),
  KEY `revo_posts_multi_idx` (`poster_id`,`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `revo_posts`
-- 

INSERT INTO `revo_posts` VALUES (1, 'Admin', 2, '127.0.0.1', NULL, 'If you are looking at this (which I guess you are), the install of Revo Forum appears to have worked! Now log in and head over to the administration control panel to configure your forum.', 0, 1139197540, NULL, NULL, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `revo_ranks`
-- 

CREATE TABLE `revo_ranks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `rank` varchar(50) NOT NULL default '',
  `min_posts` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `revo_ranks`
-- 

INSERT INTO `revo_ranks` VALUES (1, 'New member', 0);
INSERT INTO `revo_ranks` VALUES (2, 'Member', 10);

-- --------------------------------------------------------

-- 
-- Table structure for table `revo_reports`
-- 

CREATE TABLE `revo_reports` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `post_id` int(10) unsigned NOT NULL default '0',
  `topic_id` int(10) unsigned NOT NULL default '0',
  `forum_id` int(10) unsigned NOT NULL default '0',
  `reported_by` int(10) unsigned NOT NULL default '0',
  `created` int(10) unsigned NOT NULL default '0',
  `message` text NOT NULL,
  `zapped` int(10) unsigned default NULL,
  `zapped_by` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `revo_reports_zapped_idx` (`zapped`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_reports`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_search_cache`
-- 

CREATE TABLE `revo_search_cache` (
  `id` int(10) unsigned NOT NULL default '0',
  `ident` varchar(200) NOT NULL default '',
  `search_data` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `revo_search_cache_ident_idx` (`ident`(8))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `revo_search_cache`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_search_matches`
-- 

CREATE TABLE `revo_search_matches` (
  `post_id` int(10) unsigned NOT NULL default '0',
  `word_id` mediumint(8) unsigned NOT NULL default '0',
  `subject_match` tinyint(1) NOT NULL default '0',
  KEY `revo_search_matches_word_id_idx` (`word_id`),
  KEY `revo_search_matches_post_id_idx` (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `revo_search_matches`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_search_words`
-- 

CREATE TABLE `revo_search_words` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `word` varchar(20) character set latin1 collate latin1_bin NOT NULL default '',
  PRIMARY KEY  (`word`),
  KEY `revo_search_words_id_idx` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_search_words`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_spelling_words`
-- 

CREATE TABLE `revo_spelling_words` (
  `id` mediumint(9) NOT NULL auto_increment,
  `word` varchar(30) NOT NULL default '',
  `sound` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `word` (`word`),
  KEY `sound` (`sound`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `revo_spelling_words`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_subscriptions`
-- 

CREATE TABLE `revo_subscriptions` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `topic_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `revo_subscriptions`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `revo_topics`
-- 

CREATE TABLE `revo_topics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `poster` varchar(200) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `posted` int(10) unsigned NOT NULL default '0',
  `last_post` int(10) unsigned NOT NULL default '0',
  `last_post_id` int(10) unsigned NOT NULL default '0',
  `last_poster` varchar(200) default NULL,
  `num_views` mediumint(8) unsigned NOT NULL default '0',
  `num_replies` mediumint(8) unsigned NOT NULL default '0',
  `closed` tinyint(1) NOT NULL default '0',
  `sticky` tinyint(1) NOT NULL default '0',
  `moved_to` int(10) unsigned default NULL,
  `forum_id` int(10) unsigned NOT NULL default '0',
  `question` varchar(255) NOT NULL default '',
  `yes` varchar(30) NOT NULL default '',
  `no` varchar(30) NOT NULL default '',
  `icon_topic` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `revo_topics_forum_id_idx` (`forum_id`),
  KEY `revo_topics_moved_to_idx` (`moved_to`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `revo_topics`
-- 

INSERT INTO `revo_topics` VALUES (1, 'Admin', 'Test post', 1139197540, 1139197540, 1, 'Admin', 0, 0, 0, 0, NULL, 1, '', '', '', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `revo_users`
-- 

CREATE TABLE `revo_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL default '4',
  `username` varchar(200) NOT NULL default '',
  `password` varchar(40) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `title` varchar(50) default NULL,
  `realname` varchar(40) default NULL,
  `url` varchar(100) default '',
  `jabber` varchar(75) default NULL,
  `icq` varchar(12) default NULL,
  `msn` varchar(50) default NULL,
  `aim` varchar(30) default '',
  `yahoo` varchar(30) default '',
  `gtalk` varchar(30) default NULL,
  `skype` varchar(30) default NULL,
  `location` varchar(30) default NULL,
  `use_avatar` tinyint(1) NOT NULL default '0',
  `signature` text,
  `disp_topics` tinyint(3) unsigned default NULL,
  `disp_posts` tinyint(3) unsigned default NULL,
  `email_setting` tinyint(1) NOT NULL default '1',
  `save_pass` tinyint(1) NOT NULL default '1',
  `notify_with_post` tinyint(1) NOT NULL default '0',
  `show_smilies` tinyint(1) NOT NULL default '1',
  `show_img` tinyint(1) NOT NULL default '1',
  `show_img_sig` tinyint(1) NOT NULL default '1',
  `show_avatars` tinyint(1) NOT NULL default '1',
  `show_sig` tinyint(1) NOT NULL default '1',
  `timezone` float NOT NULL default '0',
  `language` varchar(25) NOT NULL default 'English',
  `style` varchar(25) NOT NULL default 'Default',
  `num_posts` int(10) unsigned NOT NULL default '0',
  `last_post` int(10) unsigned default NULL,
  `registered` int(10) unsigned NOT NULL default '0',
  `registration_ip` varchar(15) NOT NULL default '0.0.0.0',
  `last_visit` int(10) unsigned NOT NULL default '0',
  `admin_note` varchar(30) default NULL,
  `activate_string` varchar(50) default NULL,
  `activate_key` varchar(8) default NULL,
  `birthday` varchar(10) NOT NULL default '0-0-0',
  `read_topics` mediumtext,
  `sex` varchar(10) default NULL,
  `latitude` varchar(100) default NULL,
  `longitude` varchar(100) default NULL,
  `reputation_minus` int(11) unsigned default NULL,
  `reputation_plus` int(11) unsigned default NULL,
  `last_reputation_voice` int(10) unsigned default NULL,
  `invitedby` int(11) NOT NULL default '0',
  `country` varchar(40) default NULL,
  `reverse_posts` tinyint(1) NOT NULL default '0',
  `referral_count` int(10) default NULL,
  `email_alert` tinyint(1) NOT NULL default '0',
  `abs` tinyint(1) NOT NULL default '0',
  `abs_message` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `revo_users_registered_idx` (`registered`),
  KEY `revo_users_username_idx` (`username`(8))
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `revo_users`
-- 

INSERT INTO `revo_users` VALUES (1, 3, 'Guest', 'Guest', 'Guest', NULL, NULL, '', NULL, NULL, NULL, '', '', NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, 1, 0, 1, 1, 1, 1, 1, 0, 'English', 'Default', 0, NULL, 0, '0.0.0.0', 0, NULL, NULL, NULL, '0-0-0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 0, 0, '');
INSERT INTO `revo_users` VALUES (2, 1, 'Admin', '4e7afebcfbae000b22c7c85e5560f89a2a0280b4', 'you@yourdomain.com', NULL, NULL, '', NULL, NULL, NULL, '', '', NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, 1, 0, 1, 1, 1, 1, 1, 0, 'English', 'Default', 1, 1139197540, 1139197540, '127.0.0.1', 1139197540, NULL, NULL, NULL, '0-0-0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 0, 0, '');
