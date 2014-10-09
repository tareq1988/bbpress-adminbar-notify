<?php

/**
 * The database table name
 *
 * @return string
 */
function bbp_ab_get_table() {
    global $wpdb;

    return $wpdb->prefix . 'bbp_notifications';
}

/**
 * Ger all notify-able users
 *
 * @return array
 */
function bbp_ab_get_users() {
    $roles     = array( 'administrator', 'bbp_moderator' );
    $all_users = array();

    foreach ($roles as $role) {
        $users     = get_users( array( 'role' => $role, 'fields' => array('ID', 'user_login' ) ) );
        $all_users = array_merge( $users, $all_users );
    }

    return array_unique( wp_list_pluck( $all_users, 'ID' ) );
}

/**
 * Mark a notification as read
 *
 * @param  int $notify_id
 *
 * @return void
 */
function bbp_ab_mark_read( $notify_id ) {
    global $wpdb;

    $wpdb->update( bbp_ab_get_table(),
        array( 'last_read' => current_time( 'mysql' ) ),
        array( 'id' => $notify_id, 'user_id' => get_current_user_id() ),
        array( '%s' ),
        array( '%d', '%d' )
    );
}

/**
 * Mark a notification as notified
 *
 * @param  int $notify_id
 *
 * @return void
 */
function bbp_ab_mark_notified( $notify_id ) {
    global $wpdb;

    $wpdb->update( bbp_ab_get_table(),
        array( 'notify' => 1 ),
        array( 'id' => $notify_id, 'user_id' => get_current_user_id() ),
        array( '%d' ),
        array( '%d', '%d' )
    );
}

/**
 * Get notifications from a user
 *
 * @param  integer  $user_id
 * @param  integer $limit
 * @param  integer $offset
 * @param  mixed $notification the status of the notification. "all" returns everything
 *
 * @return array
 */
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