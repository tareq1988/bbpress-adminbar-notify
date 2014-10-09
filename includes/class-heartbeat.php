<?php

/**
 * Heartbeat handler class
 *
 * @author Tareq Hasan <tareq@wedevs.com>
 */
class BBP_AB_Heartbeat {

    function __construct() {
        add_filter( 'heartbeat_received', array( $this, 'heartbeat_response' ), 10, 2 );
        add_filter( 'heartbeat_settings', array( $this, 'heartbeat_settings' ) );
    }

    /**
     * Heartbeat ajax request handler
     *
     * @param  array $response
     * @param  array $data
     *
     * @return array
     */
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
     *
     * @param  array $settings
     *
     * @return array
     */
    public function heartbeat_settings( $settings ) {

        $settings['interval'] = 15; //Anything between 15-60

        return $settings;
    }
}