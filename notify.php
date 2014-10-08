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
if ( !defined( 'ABSPATH' ) ) exit;

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
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu'), 130 );

        add_filter( 'heartbeat_received', array( $this, 'heartbeat_response' ), 10, 2 );
        add_filter( 'heartbeat_settings', array( $this, 'heartbeat_settings' ) );
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

    public function create_db() {
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
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'bbpab', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {

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


        /**
         * Example for setting up text strings from Javascript files for localization
         *
         * Uncomment line below and replace with proper localization variables.
         */
        // $translation_array = array( 'some_string' => __( 'Some string to translate', 'bbpab' ), 'a_value' => '10' );
        // wp_localize_script( 'base-plugin-scripts', 'bbpab', $translation_array ) );

    }

    function admin_bar_menu() {
        global $wp_admin_bar, $current_blog;

        if ( !is_object( $wp_admin_bar ) )
            return;

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

    public function render_admin_bar() {
        $notifications = bbp_ab_get_user_notifications( get_current_user_id(), 20, 0, 'all' );

        ob_start();
        include dirname( __FILE__ ) . '/includes/render-admin-bar.php';

        return ob_get_clean();
    }

    public function heartbeat_response( $response, $data ) {

        if ( array_key_exists( 'bbpabn', $data ) ) {
            $notifications = bbp_ab_get_user_notifications( get_current_user_id() );

            if ( $notifications ) {
                foreach ($notifications as $notif) {

                    if ( $notif->type == 'topic' ) {
                        $title = __( 'New Topic:', 'bbpab' );
                        $text = sprintf( '<a href="%s">%s</a>', bbp_get_topic_permalink( $notif->object_id ), bbp_get_topic_title( $notif->object_id ) );
                        $text .= sprintf( ' ' . __( 'by %s', 'bbpab' ), get_userdata( $notif->author )->display_name );

                    } elseif ( $notif->type == 'reply' ) {
                        $title = __( 'New reply:', 'bbpab' );
                        $text = sprintf( '<a href="%s">%s</a>', bbp_get_reply_permalink( $notif->object_id ), bbp_get_topic_title( $notif->parent_object ) );
                        $text .= sprintf( ' ' . __( 'by %s', 'bbpab' ), get_userdata( $notif->author )->display_name );
                    }

                    $response['bbpabn'][] = array(
                        'title' => $title,
                        'text'  => $text,
                        'delay' => 5000,
                        'class' => 'bbpabn-alert'
                    );

                    // mark as notified
                    bbp_ab_mark_notified( $notif->id );
                }
            }
        }

        return $response;
    }

    /**
     * Sets heartbeat tick interval.
     * @since  1.0.0
     * @return void
     */
    public function heartbeat_settings( $settings ) {

        $settings['interval'] = 15; //Anything between 15-60
        // $settings['autostart'] = false;
        return $settings;

    }

} // BBP_AB_Notification

$bbpab = BBP_AB_Notification::init();

function bbp_ab_get_table() {
    global $wpdb;

    return $wpdb->prefix . 'bbp_notifications';
}

function bbp_ab_get_users() {
    $roles     = array( 'administrator', 'bbp_moderator' );
    $all_users = array();

    foreach ($roles as $role) {
        $users     = get_users( array( 'role' => $role, 'fields' => array('ID', 'user_login' ) ) );
        $all_users = array_merge( $users, $all_users );
    }

    return array_unique( wp_list_pluck( $all_users, 'ID' ) );
}

function bbp_ab_insert_notify( $user_id, $type, $object_id, $parent_id ) {
    global $wpdb;

    $wpdb->insert( bbp_ab_get_table(),
        array(
            'user_id'       => $user_id,
            'type'          => $type,
            'object_id'     => $object_id,
            'parent_object' => $parent_id,
            'created'       => current_time( 'mysql' )
        ),
        array(
            '%d',
            '%s',
            '%d',
            '%d',
            '%s'
        )
    );
}

function bbp_ab_mark_read( $notify_id ) {
    global $wpdb;

    $wpdb->update( bbp_ab_get_table(),
        array( 'last_read' => current_time( 'mysql' ) ),
        array( 'id' => $notify_id, 'user_id' => get_current_user_id() ),
        array( '%s' ),
        array( '%d', '%d' )
    );
}

function bbp_ab_mark_notified( $notify_id ) {
    global $wpdb;

    $wpdb->update( bbp_ab_get_table(),
        array( 'notify' => 1 ),
        array( 'id' => $notify_id, 'user_id' => get_current_user_id() ),
        array( '%d' ),
        array( '%d', '%d' )
    );
}

function bbp_ab_on_new_topic( $topic_id, $forum_id ) {
    $users        = bbp_ab_get_users();
    $current_user = get_current_user_id();

    if ( $users ) {
        foreach ($users as $user_id) {
            if ( $user_id != $current_user ) {
                bbp_ab_insert_notify( $user_id, 'topic', $topic_id, $forum_id );
            }
        }
    }
}

add_action( 'bbp_new_topic', 'bbp_ab_on_new_topic', 10, 2 );

function bbp_ab_on_new_reply( $reply_id, $topic_id ) {
    $users        = bbp_ab_get_users();
    $current_user = get_current_user_id();

    if ( $users ) {
        foreach ($users as $user_id) {
            if ( $user_id != $current_user ) {
                bbp_ab_insert_notify( $user_id, 'reply', $reply_id, $topic_id );
            }
        }
    }
}

add_action( 'bbp_new_reply', 'bbp_ab_on_new_reply', 10, 2 );

function bbp_ab_get_user_notifications( $user_id, $limit = 20, $offset = 0, $notification = 0 ) {
    global $wpdb;

    $where = ' WHERE user_id = %d ';
    if ( $notification !== 'all' ) {
        $where .= ' AND n.notify = ' . $notification;
    }

    $sql = $wpdb->prepare( 'SELECT n.*, object.post_title as object_title, object.post_author as author, parent.post_title as parent_title
            FROM ' . bbp_ab_get_table() . ' as n ' .
           "LEFT JOIN $wpdb->posts as object ON n.object_id = object.ID
            LEFT JOIN $wpdb->posts as parent ON n.parent_object = parent.ID
            $where
            ORDER BY created DESC
            LIMIT %d, %d", $user_id, $offset, $limit );

    return $wpdb->get_results( $sql );
}