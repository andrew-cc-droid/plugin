<?php
/**
 * Template for displaying the WOPEN-OS presale form
 */

// Get presale settings
$current_rate = floatval(get_option('wopen_os_presale_rate', 0.1));
$next_rate = floatval(get_option('wopen_os_next_rate', 0.02));
$rate_increase_time = intval(get_option('wopen_os_rate_increase_time', time() + (12 * 60 * 60)));
$wopen_contract = get_option('wopen_os_wopen_contract', '9KvS8EevsAK8kh8JVfFxbN5V58HHAxx8A6V6a4bqpump');
$os_contract = get_option('wopen_os_os_contract', 'chainos154l9u8pqz5uevarvupe9xpfh9dfg2xx7y5sstn');

// Check if countdown has ended
$countdown_active = time() < $rate_increase_time;
$current_display_rate = $countdown_active ? $current_rate : $next_rate;
?>

<div class="wopen-os-presale-container">
    <div class="wopen-os-presale-header">
        <div class="wopen-os-token-logo">
            OS
        </div>
        <div class="wopen-os-token-details">
            <h1><?php echo esc_html($atts['title']); ?></h1>
            <p>OS • ChainOS | Pay with WOPEN (Solana)</p>
        </div>
    </div>
    
    <?php if ($countdown_active): ?>
    <div class="wopen-os-rate-change-alert">
        <h4>🔥 <?php esc_html_e('LIMITED TIME OFFER', 'wopen-os-presale'); ?> 🔥</h4>
        <p><?php esc_html_e('Price increases in:', 'wopen-os-presale'); ?></p>
        
        <div class="wopen-os-countdown">
            <div class="wopen-os-countdown-item hours">
                <div class="wopen-os-countdown-value">00</div>
                <div class="wopen-os-countdown-label"><?php esc_html_e('Hours', 'wopen-os-presale'); ?></div>
            </div>
            <div class="wopen-os-countdown-item minutes">
                <div class="wopen-os-countdown-value">00</div>
                <div class="wopen-os-countdown-label"><?php esc_html_e('Minutes', 'wopen-os-presale'); ?></div>
            </div>
            <div class="wopen-os-countdown-item seconds">
                <div class="wopen-os-countdown-value">00</div>
                <div class="wopen-os-countdown-label"><?php esc_html_e('Seconds', 'wopen-os-presale'); ?></div>
            </div>
        </div>
        
        <div class="wopen-os-rate-comparison">
            <div class="wopen-os-rate-item">
                <div class="wopen-os-rate-label"><?php esc_html_e('Current Rate', 'wopen-os-presale'); ?></div>
                <div class="wopen-os-rate-value">1 WOPEN = <?php echo esc_html($current_rate); ?> OS</div>
            </div>
            <div class="wopen-os-rate-item">
                <div class="wopen-os-rate-label"><?php esc_html_e('After Countdown', 'wopen-os-presale'); ?></div>
                <div class="wopen-os-rate-value">1 WOPEN = <?php echo esc_html($next_rate); ?> OS</div>
            </div>
        </div>
        
        <p><?php esc_html_e('Get 5x more tokens by participating now!', 'wopen-os-presale'); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="wopen-os-contract-info">
        <p><strong><?php esc_html_e('WOPEN Token Contract:', 'wopen-os-presale'); ?></strong> <code><?php echo esc_html($wopen_contract); ?></code></p>
        <p><strong><?php esc_html_e('OS Token Contract:', 'wopen-os-presale'); ?></strong> <code><?php echo esc_html($os_contract); ?></code></p>
        <p><strong><?php esc_html_e('Exchange Rate:', 'wopen-os-presale'); ?></strong> 100,000 WOPEN = <?php echo number_format(100000 * $current_display_rate, 0); ?> OS</p>
        <?php if (!$countdown_active): ?>
        <p><strong><?php esc_html_e('Current Rate (after countdown):', 'wopen-os-presale'); ?></strong> 100,000 WOPEN = <?php echo number_format(100000 * $next_rate, 0); ?> OS</p>
        <?php endif; ?>
    </div>
    
    <div class="wopen-os-swap-form">
        <div class="wopen-os-swap-form-title"><?php esc_html_e('Swap WOPEN for OS Tokens', 'wopen-os-presale'); ?></div>
        
        <div class="wopen-os-input-group">
            <label for="wopen-os-wopen-amount"><?php esc_html_e('You Pay', 'wopen-os-presale'); ?></label>
            <div class="wopen-os-input-container">
                <input 
                    type="number" 
                    id="wopen-os-wopen-amount" 
                    placeholder="0.00" 
                    step="any"
                    min="0"
                />
                <div class="wopen-os-token-select">WOPEN</div>
            </div>
        </div>
        
        <div class="wopen-os-exchange-rate">
            <span>1 WOPEN = <?php echo esc_html($current_display_rate); ?> OS</span>
            <?php if ($countdown_active): ?>
            <div style="font-size: 0.8rem; color: #ffc107; margin-top: 0.5rem;">
                <?php esc_html_e('Price increases after countdown ends!', 'wopen-os-presale'); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="wopen-os-you-receive">
            <div class="wopen-os-you-receive-label"><?php esc_html_e('You Receive', 'wopen-os-presale'); ?></div>
            <div class="wopen-os-you-receive-value"><span id="wopen-os-receive-amount">0.00000000</span> OS</div>
        </div>
        
        <div class="wopen-os-input-group">
            <label for="wopen-os-os-address"><?php esc_html_e('Your ChainOS Wallet Address', 'wopen-os-presale'); ?></label>
            <div class="wopen-os-input-container">
                <input 
                    type="text" 
                    id="wopen-os-os-address" 
                    placeholder="chainos..." 
                />
            </div>
        </div>
        
        <button id="wopen-os-generate-address" class="wopen-os-button">
            <?php esc_html_e('Generate Deposit Address', 'wopen-os-presale'); ?>
        </button>
        
        <div class="wopen-os-error-message" style="display: none;"></div>
    </div>
    
    <div class="wopen-os-result-container" style="display: none;"></div>
</div>
