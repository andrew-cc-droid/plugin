/**
 * WOPEN-OS Presale Public JS
 */
(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initPresaleForm();
        startCountdownTimer();
    });
    
    // Initialize presale form
    function initPresaleForm() {
        var $form = $('.wopen-os-swap-form');
        if (!$form.length) return;
        
        // Input fields
        var $wopenInput = $('#wopen-os-wopen-amount');
        var $osReceive = $('#wopen-os-receive-amount');
        var $osAddress = $('#wopen-os-os-address');
        var $generateBtn = $('#wopen-os-generate-address');
        var $resultContainer = $('.wopen-os-result-container');
        var $errorMessage = $('.wopen-os-error-message');
        
        // Set up amount calculation
        $wopenInput.on('input', function() {
            calculateTokenAmount();
        });
        
        // Generate deposit address
        $generateBtn.on('click', function(e) {
            e.preventDefault();
            generateDepositAddress();
        });
        
        // Calculate token amount based on current rate
        function calculateTokenAmount() {
            var wopenAmount = parseFloat($wopenInput.val()) || 0;
            var rate = getCurrentRate();
            var osAmount = (wopenAmount * rate).toFixed(8);
            $osReceive.text(osAmount);
        }
        
        // Get current rate based on countdown status
        function getCurrentRate() {
            var currentTime = Date.now();
            var countdownTime = wopen_os_presale.countdown_time;
            
            if (currentTime >= countdownTime) {
                return wopen_os_presale.next_rate;
            } else {
                return wopen_os_presale.current_rate;
            }
        }
        
        // Generate deposit address via AJAX
        function generateDepositAddress() {
            var wopenAmount = parseFloat($wopenInput.val());
            var osAddress = $osAddress.val().trim();
            
            // Validate inputs
            if (!wopenAmount || wopenAmount <= 0) {
                showError(wopen_os_presale.texts.amount_required);
                return;
            }
            
            if (!osAddress) {
                showError(wopen_os_presale.texts.address_required);
                return;
            }
            
            // Set button state to loading
            $generateBtn.prop('disabled', true).text(wopen_os_presale.texts.generating_address);
            $errorMessage.hide();
            
            // Make AJAX request
            $.ajax({
                url: wopen_os_presale.ajax_url,
                type: 'POST',
                data: {
                    action: 'wopen_os_generate_address',
                    nonce: wopen_os_presale.nonce,
                    amount: wopenAmount,
                    os_address: osAddress
                },
                success: function(response) {
                    if (response.success) {
                        showDepositInfo(response.data);
                    } else {
                        showError(response.data.message);
                        $generateBtn.prop('disabled', false).text(wopen_os_presale.texts.generate_address);
                    }
                },
                error: function() {
                    showError('An error occurred. Please try again.');
                    $generateBtn.prop('disabled', false).text(wopen_os_presale.texts.generate_address);
                }
            });
        }
        
        // Show error message
        function showError(message) {
            $errorMessage.text(message).show();
        }
        
        // Show deposit information
        function showDepositInfo(data) {
            // Format HTML for deposit info
            var html = '<div class="wopen-os-deposit-info">' +
                '<div class="wopen-os-deposit-address">' +
                '<h4>' + wopen_os_presale.texts.deposit_address_generated + '</h4>' +
                '<div class="address">' + data.deposit_address + '</div>' +
                '</div>' +
                '<div class="wopen-os-access-token">' +
                '<h4>Your Access Identifier</h4>' +
                '<div class="token">' + data.access_identifier + '</div>' +
                '<div class="warning">' + wopen_os_presale.texts.save_identifier_warning + '</div>' +
                '</div>' +
                '</div>';
            
            // Display the result
            $form.hide();
            $resultContainer.html(html).show();
            
            // Store in local storage for later retrieval
            try {
                localStorage.setItem('wopen_os_order_' + data.access_identifier, JSON.stringify({
                    order_id: data.order_id,
                    deposit_address: data.deposit_address,
                    access_identifier: data.access_identifier,
                    amount: data.amount,
                    token_amount: data.token_amount,
                    created_at: Date.now()
                }));
            } catch (e) {
                console.error('Failed to save order to local storage:', e);
            }
        }
    }
    
    // Start countdown timer
    function startCountdownTimer() {
        var $countdown = $('.wopen-os-countdown');
        if (!$countdown.length) return;
        
        var $hours = $countdown.find('.hours .wopen-os-countdown-value');
        var $minutes = $countdown.find('.minutes .wopen-os-countdown-value');
        var $seconds = $countdown.find('.seconds .wopen-os-countdown-value');
        var $alert = $('.wopen-os-rate-change-alert');
        
        var countdownTime = wopen_os_presale.countdown_time;
        var currentTime = Date.now();
        
        // If countdown already ended
        if (currentTime >= countdownTime) {
            $alert.hide();
            updateRateDisplay(wopen_os_presale.next_rate);
            return;
        }
        
        // Update countdown every second
        var interval = setInterval(function() {
            var now = Date.now();
            var timeLeft = countdownTime - now;
            
            if (timeLeft <= 0) {
                clearInterval(interval);
                $alert.hide();
                updateRateDisplay(wopen_os_presale.next_rate);
                return;
            }
            
            // Calculate remaining time
            var hours = Math.floor(timeLeft / (1000 * 60 * 60));
            var minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
            
            // Update display
            $hours.text(padZero(hours));
            $minutes.text(padZero(minutes));
            $seconds.text(padZero(seconds));
        }, 1000);
        
        // Helper to pad with zero
        function padZero(num) {
            return num < 10 ? '0' + num : num;
        }
    }
    
    // Update rate display after countdown
    function updateRateDisplay(newRate) {
        $('.wopen-os-exchange-rate span').text('1 WOPEN = ' + newRate + ' OS');
        
        // Recalculate token amount if needed
        var $wopenInput = $('#wopen-os-wopen-amount');
        var $osReceive = $('#wopen-os-receive-amount');
        
        if ($wopenInput.length && $osReceive.length) {
            var wopenAmount = parseFloat($wopenInput.val()) || 0;
            var osAmount = (wopenAmount * newRate).toFixed(8);
            $osReceive.text(osAmount);
        }
    }
    
})(jQuery);
