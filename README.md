# ExpiryChecker simpleSAMLphp Module #
A simpleSAMLphp module for warning users that their password will expire soon 
or that it has already expired.

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

You will then need to set filter parameters in your config.php file.

Example:

    10 => array(
        'class' => 'expirychecker:ExpiryDate',
        'netid_attr' => 'eduPersonPrincipalName',
        'expirydate_attr' => 'schacExpiryDate',
        'warndaysbefore' => '60',
        'date_format' => 'd.m.Y',
    ),

The `netid_attr` parameter represents the (ldap) attribute name which has the 
user's NetID stored in it.

The `expirydate_attr` parameter represents the (ldap) attribute name which has 
the user's expiry date, which must be formated as YYYYMMDDHHMMSSZ (e.g. 
`20111011235959Z`). Those two attributes need to be part of the attribute set 
returned when the user successfully authenticates.

The `warndaysbefore` parameter should be an integer representing how many days 
before the expiry date the "about to expire" warning will be shown to the user.

The `date_format` parameter specifies how you want the date to be formatted, 
using PHP `date()` syntax. See <http://php.net/manual/en/function.date.php>.

## Contributing ##
To contribute, please submit issues or pull requests at 
https://github.com/silinternational/simplesamlphp-module-expirychecker

## Acknowledgements ##
This is adapted from the `ssp-iidp-expirycheck` and `expirycheck` modules. 
Thanks to Alex Mihičinac, Steve Moitozo, and Steve Bagwell for the initial they 
did on those two modules.
