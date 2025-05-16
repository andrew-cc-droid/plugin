/**
 * WOPEN-OS Presale Admin JS
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Handle datetime-local input for countdown time
        var $timeInput = $('input[name="wopen_os_rate_increase_time"]');
        
        if ($timeInput.length) {
            $timeInput.on('change', function() {
                // Convert the datetime to timestamp on form submit
                $('form').on('submit', function() {
                    var datetimeValue = $timeInput.val();
                    if (datetimeValue) {
                        // Create a hidden input with the timestamp value
                        var timestamp = Math.floor(new Date(datetimeValue).getTime() / 1000);
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'wopen_os_rate_increase_time',
                            value: timestamp
                        }).appendTo('form');
                        
                        // Remove the original input from form submission
                        $timeInput.attr('name', '');
                    }
                });
            });
        }
    });
    
})(jQuery);
