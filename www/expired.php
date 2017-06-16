<?php

/**
 * about2expire.php
 *
 * @package simpleSAMLphp
 * @version $Id$
 */

SimpleSAML_Logger::info('expirychecker - User has been warned that their password has expired.');

if (!array_key_exists('StateId', $_REQUEST)) {
	throw new SimpleSAML_Error_BadRequest('Missing required StateId query parameter.');
}

$id = $_REQUEST['StateId'];
$state = SimpleSAML_Auth_State::loadState($id, 'expirywarning:expired');

$globalConfig = SimpleSAML_Configuration::getInstance();

$t = new SimpleSAML_XHTML_Template($globalConfig, 'expirychecker:expired.php');
$t->data['expireOnDate'] = $state['expireOnDate'];
$t->data['accountName'] = $state['accountName'];
$t->show();
