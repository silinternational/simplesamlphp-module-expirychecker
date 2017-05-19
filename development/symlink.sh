#!/usr/bin/env bash

rm -rf /data/vendor/simplesamlphp/simplesamlphp/modules/expirychecker
ln -s /expirychecker /data/vendor/simplesamlphp/simplesamlphp/modules/

#ln -s /expirychecker/development/ssp/authsources.php /data/vendor/simplesamlphp/simplesamlphp/config/authsources.php
#ln -s /expirychecker/development/ssp/saml20-idp-hosted.php /data/vendor/simplesamlphp/simplesamlphp/metadata/saml20-idp-hosted.php
#mkdir -p /data/vendor/simplesamlphp/simplesamlphp/cert/
#ln -s /expirychecker/development/ssp/saml.crt /data/vendor/simplesamlphp/simplesamlphp/cert/saml.crt
#ln -s /expirychecker/development/ssp/saml.pem /data/vendor/simplesamlphp/simplesamlphp/cert/saml.pem
