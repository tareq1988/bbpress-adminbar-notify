/* jshint devel:true */
/* global wp */

jQuery(document).ready(function($) {

    // Initial pulse data
    var bbpabn = {
        debug: true
    };

    // Change default beat tick period
    // wp.heartbeat.interval('fast'); // slow (1 beat every 60 seconds), standard (1 beat every 15 seconds), fast (1 beat every 5 seconds)

    // Initiate namespace with bbpabn data
    wp.heartbeat.enqueue('bbpabn', bbpabn, false);

    // Listen for the custom event "heartbeat-tick" on $(document).
    jQuery(document).on('heartbeat-tick.bbpabn', function(e, data) {

        if ( typeof data.bbpabn !== 'undefined' ) {
            jQuery.each(data.bbpabn, function(index, val) {
                jQuery.growl(val);
            });
        }

        // Pass data back into namespace
        wp.heartbeat.enqueue('bbpabn', bbpabn, false);

    });

    // show/hide admin bar
    $('#wp-admin-bar-notes').on('click', function(e) {
        $('#bbpab-notes-panel').toggle();
        $(this).toggleClass('open');

        e.stopPropagation();
    });

    $(document.body).click(function() {
        $('#bbpab-notes-panel').hide();
        $('#wp-admin-bar-notes').removeClass('open');
    });

    $('#bbpab-notes-panel').click(function(e) {
        e.stopPropagation();
    });

});