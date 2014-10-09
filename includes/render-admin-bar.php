<div id="bbpab-notes-panel" style="display:none;">

    <div class="bbpab-notes-panel-header">
        <span class="wpnt-notes-header"><?php echo __( 'Notifications', 'bbpab' ); ?></span>
    </div>
    <div class="bbpab-notifications">
        <ul>
            <?php
            if ( $notifications ) {
                foreach ($notifications as $notif) {
                    ?>
                    <li class="bbpab-notif-item">
                        <div class="bbpab-author">
                            <?php echo get_avatar( $notif->author, 32 ); ?>
                        </div>
                        <div class="bbpab-notif-body">
                            <?php
                            $text = '';

                            if ( $notif->type == 'topic' ) {
                                $text = sprintf( '<a href="%s">%s', bbp_get_topic_permalink( $notif->object_id ), __( 'Topic: ', 'bbpab' ) . bbp_get_topic_title( $notif->object_id ) );
                                $text .= sprintf( ' <span>' . __( 'by %s', 'bbpab' ) . '</span></a>', get_userdata( $notif->author )->display_name );

                            } elseif ( $notif->type == 'reply' ) {
                                $text = sprintf( '<a href="%s">%s', bbp_get_reply_permalink( $notif->object_id ), __( 'Reply: ', 'bbpab' ) . bbp_get_topic_title( $notif->parent_object ) );
                                $text .= sprintf( ' <span>' . __( 'by %s', 'bbpab' ) . '</span></a>', get_userdata( $notif->author )->display_name );
                            }

                            echo $text;
                            ?>
                        </div>
                    </li>
                    <?
                }
            }
            ?>
        </ul>
    </div>
</div>