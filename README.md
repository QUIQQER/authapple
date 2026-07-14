![QUIQQER Auth Apple](bin/images/Readme.png)

# Apple authentication for QUIQQER

`quiqqer/authapple` adds Sign in with Apple to QUIQQER. It provides both a primary authenticator and a registration option for `quiqqer/frontend-users`.

## Requirements

- PHP 8.2 or newer
- QUIQQER Core 2.24 or newer
- `quiqqer/frontend-users` 2.8 or newer
- An Apple Developer account with Sign in with Apple configured

## Installation

Install the package through the QUIQQER package manager or with Composer:

```bash
composer require quiqqer/authapple
```

Run the QUIQQER setup after installation so the package database table, settings, events, and providers are registered.

## Configuration

Open the frontend-users settings in the QUIQQER administration and select the Apple authentication section. Enter the following values from the Apple Developer portal:

- Client ID (Service ID)
- Team ID
- Key ID
- Private key from the `.p8` file

Register this package URL as the OAuth return URL for the Service ID:

```text
https://your-domain.example/opt/quiqqer/authapple/bin/oauth_callback.php
```

The exact `/opt/` path may differ if the installation uses a custom QUIQQER optional-package URL.

## Usage

After configuration, the Apple button is available in the QUIQQER login and frontend registration flows. A successful registration links the Apple subject identifier to the created QUIQQER user. Users can subsequently authenticate with the same Apple account.

## Development

Initialize and run the package-local quality tools with:

```bash
composer dev:init
composer test
```

The test command runs PSR-12 checks, PHPStan level 8, and PHPUnit.

## Support

- Issues: https://dev.quiqqer.com/quiqqer/authapple/issues
- Source: https://dev.quiqqer.com/quiqqer/authapple
- Email: support@pcsg.de

## License

GPL-3.0-or-later
