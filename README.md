# ExpiryChecker simpleSAMLphp Module #
A simpleSAMLphp module for warning users that their password will expire soon 
or that it has already expired.

**NOTE:** This module does *not* prevent the user from logging in. It merely 
shows a warning page (if their password is about to expire), with the option to 
change their password now or later, or it tells the user that their password has
already expired, with the only option being to go change their password now. 
Both of these pages will be bypassed (for varying lengths of time) if the user 
has recently seen one of those two pages, in order to allow the user to get to 
the change-password website (assuming it is also behind this IdP). If the user 
should not be allowed to log in at all, the simpleSAMLphp Auth. Source should 
consider the credentials provided by the user to be invalid.

The expirychecker module is implemented as an Authentication Processing Filter, 
or AuthProc. That means it can be configured in the global config.php file or 
the SP remote or IdP hosted metadata.

It is recommended to run the expirychecker module at the IdP, and configure the
filter to run before all the other filters you may have enabled.

## How to use the module ##
Simply include `simplesamlphp/composer-module-installer` and this module as 
required in your `composer.json` file. The `composer-module-installer` package 
will discover this module and copy it into the `modules` folder within 
`simplesamlphp`.

You will then need to set filter parameters in your config. We recommend adding 
them to the `'authproc'` array in your `metadata/saml20-idp-hosted.php` file, 
but you are also able to put them in the `'authproc.idp'` array in your 
`config/config.php` file.

Example (in `metadata/saml20-idp-hosted.php`):

    'authproc' => [
        10 => [
            // Required:
            'class' => 'expirychecker:ExpiryDate',
            'accountNameAttr' => 'cn',
            'expiryDateAttr' => 'schacExpiryDate',
            'changePwdUrl' => 'https://idm.example.com/pwdmgr/',

            // Optional:
            'warnDaysBefore' => 14,
            'originalUrlParam' => 'originalurl',
            'dateFormat' => 'm.d.Y', // Use PHP's date syntax.
            'loggerClass' => '\\Sil\\Psr3Adapters\\Psr3SamlLogger',
        ],
        
        // ...
    ],

The `accountNameAttr` parameter represents the SAML attribute name which has 
the user's account name stored in it. In certain situations, this will be 
displayed to the user, as well as being used in log messages.

The `expiryDateAttr` parameter represents the SAML attribute name which has 
the user's expiry date, which must be formated as YYYYMMDDHHMMSSZ (e.g. 
`20111011235959Z`). Those two attributes need to be part of the attribute set 
returned when the user successfully authenticates.

The `warnDaysBefore` parameter should be an integer representing how many days 
before the expiry date the "about to expire" warning will be shown to the user.

The `dateFormat` parameter specifies how you want the date to be formatted, 
using PHP `date()` syntax. See <http://php.net/manual/en/function.date.php>.

The `loggerClass` parameter specifies the name of a PSR-3 compatible class that 
can be autoloaded, to use as the logger within ExpiryDate.

## Contributing ##
To contribute, please submit issues or pull requests at 
https://github.com/silinternational/simplesamlphp-module-expirychecker

## Acknowledgements ##
This is adapted from the `ssp-iidp-expirycheck` and `expirycheck` modules. 
Thanks to Alex Mihičinac, Steve Moitozo, and Steve Bagwell for the initial work 
they did on those two modules.
