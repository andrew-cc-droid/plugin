# WOPEN-OS Presale WordPress Plugin

This plugin adds WOPEN to OS cross-chain presale functionality to your WordPress site, allowing users to deposit Solana WOPEN tokens and receive ChainOS OS tokens.

## Features

- 12-hour countdown timer for special rate promotion
- 5x price increase after the countdown ends (configurable)
- Automatically generates unique Solana deposit addresses for each user
- Creates a random access identifier for users to track and manage their orders
- Admin dashboard to manage and track all presale orders
- Easy integration with shortcode: `[wopen_os_presale]`

## Installation

1. Upload the `wopen-os-presale` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings under the 'WOPEN-OS Presale' menu in the admin dashboard

## Usage

### Adding the Presale Form to a Page

Simply add the shortcode `[wopen_os_presale]` to any page or post where you want the presale form to appear.

### Configuration

In the WordPress admin dashboard, go to 'WOPEN-OS Presale' → 'Settings' to configure:

- Current exchange rate (WOPEN to OS)
- Future exchange rate after countdown (WOPEN to OS)
- Countdown end time
- Contract addresses for WOPEN and OS tokens

### Viewing Orders

All presale orders can be viewed in the 'WOPEN-OS Presale' → 'Orders' section of the admin dashboard.

## Technical Implementation

- The plugin creates a unique Solana keypair for each deposit using the Ed25519 algorithm
- Each order is stored in the WordPress database for easy management
- Access identifiers use a secure random generation method to avoid collisions
- The countdown timer is synchronized between server and client for accurate timing

## Security Considerations

- Secret keys are stored securely in the database
- Access identifiers are designed to be unpredictable
- User inputs are properly sanitized to prevent security vulnerabilities

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## Support

For support questions, please contact the ChainOS team.

## License

GPL v2 or later
