<?php

/**
 * bbPress related functionalities
 *
 * @author Tareq Hasan <tareq@wedevs.com>
 */
class BBP_AB_bbPress {

    function __construct() {
        add_action( 'bbp_new_topic', array( $this, 'on_new_topic' ), 10, 2 );
        add_action( 'bbp_new_reply', array( $this, 'on_new_reply' ), 10, 2 );
    }

    /**
     * Insert notification on a new topic creation
     *
     * @param  int $topic_id
     * @param  int $forum_id
     *
     * @return void
     */
    function on_new_topic( $topic_id, $forum_id ) {
        $users        = bbp_ab_get_users();
        $current_user = get_current_user_id();

        if ( $users ) {
            foreach ($users as $user_id) {
                if ( $user_id != $current_user ) {
                    $this->insert_notification( $user_id, 'topic', $topic_id, $forum_id );
                }
            }
        }
    }

    /**
     * Insert notification on a new reply creation
     *
     * @param  int $reply_id
     * @param  int $topic_id
     *
     * @return void
     */
    function on_new_reply( $reply_id, $topic_id ) {
        $users        = bbp_ab_get_users();
        $current_user = get_current_user_id();

        if ( $users ) {
            foreach ($users as $user_id) {
                if ( $user_id != $current_user ) {
                    $this->insert_notification( $user_id, 'reply', $reply_id, $topic_id );
                }
            }
        }
    }

    /**
     * Insert a notification on the database
     *
     * @param  int $user_id
     * @param  string $type
     * @param  int $object_id
     * @param  int $parent_id
     *
     * @return boolean
     */
    function insert_notification( $user_id, $type, $object_id, $parent_id ) {
        global $wpdb;

        return $wpdb->insert( bbp_ab_get_table(),
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
}