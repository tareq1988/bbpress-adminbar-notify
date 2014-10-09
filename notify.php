<?php
/*
Plugin Name: bbPress admin bar notification
Plugin URI: http://tareq.wedevs.com/
Description: Facebook like notification system for bbPress
Version: 0.1
Author: Tareq Hasan
Author URI: http://tareq.wedevs.com/
License: GPL2
*/

/**
 * Copyright (c) 2014 Tareq Hasan (email: tareq@wedevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * BBP_AB_Notification class
 *
 * @class BBP_AB_Notification The class that holds the entire BBP_AB_Notification plugin
 */
class BBP_AB_Notification {

    /**
     * Constructor for the BBP_AB_Notification class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
      * @uses add_action()
     */
    public function __construct() {

        // require the files
        $this->includes();
        $this->init_classes();

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu'), 130 );
    }

    /**
     * Initializes the BBP_AB_Notification() class
     *
     * Checks for an existing BBP_AB_Notification() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new BBP_AB_Notification();
        }

        return $instance;
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {

    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    private function create_db() {
        global $wpdb;

        $table = bbp_ab_get_table();

        $sql= "CREATE TABLE IF NOT EXISTS $table (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` bigint(20) DEFAULT NULL,
          `type` varchar(10) DEFAULT NULL,
          `object_id` bigint(20) DEFAULT NULL,
          `parent_object` bigint(20) DEFAULT NULL,
          `created` timestamp NULL DEFAULT NULL,
          `last_read` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    }

    /**
     * Include the required files
     *
     * @return void
     */
    private function includes() {
        require_once dirname( __FILE__ ) . '/includes/functions.php';

        require_once dirname( __FILE__ ) . '/includes/class-heartbeat.php';
        require_once dirname( __FILE__ ) . '/includes/class-bbp.php';
    }

    /**
     * Initialize the required classes
     *
     * @return void
     */
    private function init_classes() {
        new BBP_AB_Heartbeat();
        new BBP_AB_bbPress();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'bbpab', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Enqueue our scripts and styles
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {

        if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'bbp_moderator' ) ) {
            return;
        }

        /**
         * All styles goes here
         */
        wp_enqueue_style( 'bbpab-styles', plugins_url( 'assets/css/style.css', __FILE__ ), false, date( 'Ymd' ) );

        /**
         * All scripts goes here
         */
        wp_enqueue_script( 'heartbeat' );
        wp_enqueue_script( 'tinygrowl', plugins_url( 'assets/js/tinygrowl.min.js', __FILE__ ), array( 'jquery' ), false, true );
        wp_enqueue_script( 'bbpab-scripts', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery' ), false, true );
    }

    /**
     * Add the notification menu to the menubar
     *
     * @return void
     */
    function admin_bar_menu() {
        global $wp_admin_bar, $current_blog;

        if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'bbp_moderator' ) ) {
            return;
        }

        if ( ! is_object( $wp_admin_bar ) ) {
            return;
        }

        $classes = 'bbpab-loading wpn-read';
        $wp_admin_bar->add_menu( array(
            'id'     => 'notes',
            'title'  => '<span id="bbpab-notes-unread-count" class="' . esc_attr( $classes ) . '">
                    <span class="noticon noticon-notification"></span>
                    </span>',
            'meta'   => array(
                'html'  => $this->render_admin_bar(),
                'class' => 'menupop',
            ),
            'parent' => 'top-secondary',
        ) );
    }

    /**
     * Render the admin bar notification area
     *
     * @return string the generated HTML markup
     */
    public function render_admin_bar() {
        $notifications = bbp_ab_get_user_notifications( get_current_user_id(), 20, 0, 'all' );

        ob_start();
        include dirname( __FILE__ ) . '/includes/render-admin-bar.php';

        return ob_get_clean();
    }

}

$bbpab = BBP_AB_Notification::init();