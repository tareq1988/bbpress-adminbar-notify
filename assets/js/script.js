jQuery(document).ready(function($) {

    // Initial pulse data
    var bbpabn = {
        debug: true
    };

    // Change default beat tick period
    wp.heartbeat.interval('fast'); // slow (1 beat every 60 seconds), standard (1 beat every 15 seconds), fast (1 beat every 5 seconds)

    // Initiate namespace with bbpabn data
    wp.heartbeat.enqueue('bbpabn', bbpabn, false);

    // // Hook into the heartbeat-send
    // jQuery(document).on('heartbeat-send.bbpabn', function(e, data) {

    //     // Send data to Heartbeat
    //     if (data.hasOwnProperty('bbpabn')) {

    //         if (data.bbpabn.debug === 'true') {

    //             console.log('Data Sent: ');
    //             console.log(data);
    //             console.log('------------------');

    //         } // End If Statement

    //     } // End If Statement

    // });

    // Listen for the custom event "heartbeat-tick" on $(document).
    jQuery(document).on('heartbeat-tick.bbpabn', function(e, data) {

        // // Receive Data back from Heartbeat
        // if (data.hasOwnProperty('bbpabn')) {

        //     if (data.bbpabn.debug === 'true') {

        //         console.log('Data Received: ');
        //         console.log(data);
        //         console.log('------------------');

        //     } // End If Statement

        // } // End If Statement

        if ( typeof data.bbpabn !== 'undefined' ) {
            jQuery.each(data.bbpabn, function(index, val) {
                jQuery.growl(val);
            });
        }

        // Pass data back into namespace
        wp.heartbeat.enqueue('bbpabn', bbpabn, false);

    });

    $('#wp-admin-bar-notes').on('click', function(event) {
        event.preventDefault();

        $('#bbpab-notes-panel').toggle();
    });

});