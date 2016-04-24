<?php
/*
Plugin Name: WP Plugin Template
Plugin URI: https://github.com/fyaconiello/wp_plugin_template
Description: A simple wordpress plugin template
Version: 1.0
Author: Francis Yaconiello
Author URI: http://www.yaconiello.com
License: GPL2
*/
/*
Copyright 2012  Francis Yaconiello  (email : francis@yaconiello.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!defined('ABSPATH')) exit();

if(!class_exists('WP_Plugin_Template'))
{

	class WP_Plugin_Template
	{
		var $name = 'Comment Filter';
		var $version = '0.1';
		var $table_name = 'comment_filter';
		var $base_name = 'commentfilter';
		var $comment_filter_hook = '';	//will store generated options_page hook_suffix

		var $fields = Array(
			'original'		=>	'Original',
			'replacement'	=>	'Replacement',
			'in_comments'	=>	'Comments',
			'in_sensitive'	=>	'InSensitive',
			'in_wordonly'	=>	'Whole Word',
			'in_regex'		=>	'Regex'
		);

		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// Initialize Settings
			require_once(sprintf("%s/settings.php", dirname(__FILE__)));
			$WP_Plugin_Template_Settings = new WP_Plugin_Template_Settings();

			$plugin = plugin_basename(__FILE__);
			add_filter("plugin_action_links_$plugin", array( $this, 'plugin_settings_link' ));
			add_filter('comment_text', array( $this, 'comment_filter'), 200, 2);
			//addfilter admin_head 'comment_filter script'
		} // END public function __construct


		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
			global $wbdb;
			$commentfilter = new WP_Plugin_Template;
			$tablename = $wpdb->prefix.$commentfilter->table_name;

			$sql = "CREATE TABLE " . $tablename . " (
				  id mediumint(9) NOT NULL AUTO_INCREMENT,
				  original TEXT NOT NULL,
				  replacement TEXT NOT NULL,
				  in_comments VARCHAR(3) NOT NULL,
				  in_sensitive VARCHAR(3) NOT NULL,
				  in_wordonly VARCHAR(3) NOT NULL,
				  in_regex VARCHAR(3) NOT NULL,
				  in_bbpress VARCHAR(3) DEFAULT '0' NOT NULL,
				  UNIQUE KEY id (id)
				);";

			//Include upgrade.php
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			//Create table
			if($wpdb->get_var('show tables like "'.$tablename.'"') !== $tablename) {
					dbDelta($sql);
					add_option("comment_filter_ver", $commentfilter->version);
			}
			elseif (get_option("comment_filter_ver") !== $commentfilter->version) {
					dbDelta($sql);
					update_option("comment_filter_ver", $commentfilter->version);
			}
			delete_transient('comment_filter_db');
		} // END public static function activate

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate()
		{
			// Do nothing
		} // END public static function deactivate

		// Add the settings link to the plugins page
		function plugin_settings_link($links)
		{
			$settings_link = '<a href="options-general.php?page=wp_plugin_template">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		//strips backslashes and converts special chars to html entities
		private function esc_textarea($string) {
			//return htmlspecialchars(stripcslashes($string));
			$string = stripcslashes($string);
			return htmlspecialchars($string,ENT_QUOTES);
		}

		//get or create transient db
		private function comment_filter_db(){
			global $wpdb;

			$comment_filter_db = get_transient('comment_filter_db');
			if(empty($comment_filter_db)){
				$comment_filter_db = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix . $this->table_name." ORDER BY id", ARRAY_A);
				set_transient('comment_filter_db', $comment_filter_db);
			}
			return $comment_filter_db;
		}

		//DECODE FUNCTION
		function base64($string) {
			return base64_decode($string,true) ? base64_decode($string) : $string;
		}

		//Function to filter words
		private function replace_words ($content, $type = '') {
			$original = $replacement = Array();	//initialize arrays
			$b = $i = '';	//init strings
			$n = 1;	//counter

			foreach ($this->comment_filter_db() as $wrdb) {
				$n++;
				$b = (wrdb['in_wordonly'] == 'yes') ? '\b' : '';
				$i = (wrdb['in_sensitive'] == 'yes') ? 'i' : '';
			

				$replace = false;

				switch ($type):
					case 'comment' :
						if('yes' == $wrdb['in_comments']) {
							$replace = 1;
						}
					break;

					default: break;
				endswitch;

				if ($replace) {
					$ori = $this->base64($wrdb['original']);
					$ori = ('yes' !== $wrdb['in_regex']) ? preg_quote($ori) : $ori;	//if using regex put backslash in front of every regex special character
					$original[$n] = "/$b".$ori."$b/$i";
					$replacement[$n] = htmlspecialchars_decode($this->esc_textarea($wrdb['replacement']));
				}
			}

			//PHP regex find and replace
			$content = preg_replace($original, $replacement, $content);
			return $content;
		}

		//REPLACE WORDS IN COMMENTS
		function comment_filter($content, $comment=''){
			if($comment) {
				if($comment->comment_approved == '1'){
					$content = $this->replace_words($content, 'comment');
				}
			}
			return $content;
		} 
	} // END class WP_Plugin_Template
} // END if(!class_exists('WP_Plugin_Template'))


if(class_exists('WP_Plugin_Template'))
{
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('WP_Plugin_Template', 'activate'));
	register_deactivation_hook(__FILE__, array('WP_Plugin_Template', 'deactivate'));

	// instantiate the plugin class
	$wp_plugin_template = new WP_Plugin_Template();

}
