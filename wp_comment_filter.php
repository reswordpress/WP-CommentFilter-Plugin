<?php
/*
Plugin Name: WP Comment Filter
Plugin URI: https://www.google.com
Description: A simple censorship filter for wordpress that works with comments and e-commerce product reviews! Lets you decide what to swap the words with. Doesn't change the actual comments in the database, but replaces words before they're displayed on a page using wordpress hooks/filters. A settings panel lets you add and remove rules easily.
Version: 0.1
Author: Andy Muniz
Author URI: http://www.andymuniz.com
License: GPL2
*/
/*
Copyright 2016  Andy Muniz  (email : munizandy94@gmail.com)

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

if (!defined('ABSPATH'))
    exit();

if (!class_exists('Comment_Filter')) {
    
    class Comment_Filter
    {
        var $name = 'Comment Filter';
        var $version = '0.1';
        var $table_name = 'comment_filter';
        var $base_name = 'commentfilter';
        var $comment_filter_hook = ''; //will store generated options_page hook_suffix so javascript only loads on the settings page
        
        var $fields = Array('original' => 'Original', 'replacement' => 'Replacement');
        
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Initialize Settings
            require_once(sprintf("%s/settings.php", dirname(__FILE__)));
            $Comment_Filter_Settings = new Comment_Filter_Settings();
            
            $plugin = plugin_basename(__FILE__);
            add_filter("plugin_action_links_$plugin", array(
                &$this,
                'plugin_settings_link'
            ));
            add_filter('comment_text', array(
                &$this,
                'comment_filter'
            ), 200, 2);
            //addfilter admin_head 'comment_filter script'
        } // END public function __construct
        
        
        /**
         * Activate the plugin
         */
        public static function activate()
        {
            global $wpdb;
            $commentfilter = new Comment_Filter;
            $tablename     = $wpdb->prefix . $commentfilter->table_name;
            
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE " . $tablename . " (
                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  original TEXT NOT NULL,
                  replacement TEXT NOT NULL,
                  UNIQUE KEY id (id)
                ) $charset_collate;";
            
            //Include upgrade.php
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            //Create table/checks if a new version exists
            if ($wpdb->get_var('show tables like "' . $tablename . '"') !== $tablename) {
                dbDelta($sql);
                add_option("comment_filter_ver", $commentfilter->version);
            } elseif (get_option("comment_filter_ver") !== $commentfilter->version) {
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
            $settings_link = '<a href="options-general.php?page=commentfilter">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        }
        
        //strips backslashes and converts special chars to html entities
        private function esc_textarea($string)
        {
            //return htmlspecialchars(stripcslashes($string));
            $string = stripcslashes($string);
            return htmlspecialchars($string, ENT_QUOTES);
        }
        
        //get or create transient db
        private function comment_filter_db()
        {
            global $wpdb;
            
            $comment_filter_db = get_transient('comment_filter_db');
            if (empty($comment_filter_db)) {
                $comment_filter_db = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . $this->table_name . " ORDER BY id", ARRAY_A);
                set_transient('comment_filter_db', $comment_filter_db);
            }
            return $comment_filter_db;
        }
        
        //DECODE FUNCTION
        function base64($string)
        {
            return base64_decode($string, true) ? base64_decode($string) : $string;
        }
        
        //Function to filter words
        private function replace_words($content)
        {
            delete_transient('comment_filter_db');
            
            $original = $replacement = Array(); //initialize arrays
            $n        = 1; //counter
            
            global $wpdb;
            $comment_filter_db = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . $this->table_name . " ORDER BY id", ARRAY_A);
            
            foreach ($comment_filter_db as $cfdb) {
                
                $ori             = $this->base64($cfdb['original']);
                $original[$n]    = "/" . $ori . "/i";
                $replacement[$n] = htmlspecialchars_decode($this->esc_textarea($cfdb['replacement']));
                
                $n++;
            }
            
            //PHP regex find and replace
            $content = preg_replace($original, $replacement, $content);
            // delete_transient('comment_filter_db');
            return $content;
        }
        
        //REPLACE WORDS IN COMMENTS
        function comment_filter($content, $comment = '')
        {
            if ($comment) {
                if ($comment->comment_approved == '1') {
                    $content = $this->replace_words($content);
                }
            }
            return $content;
        }
    } // END class Comment_Filter
} // END if(!class_exists('Comment_Filter'))


if (class_exists('Comment_Filter')) {
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array(
        'Comment_Filter',
        'activate'
    ));
    register_deactivation_hook(__FILE__, array(
        'Comment_Filter',
        'deactivate'
    ));
    
    // instantiate the plugin class
    $comment_filter = new Comment_Filter();
    
}