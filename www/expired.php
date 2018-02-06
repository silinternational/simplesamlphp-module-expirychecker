<?php

use sspmod_expirychecker_Auth_Process_ExpiryDate as ExpiryDate;

$stateId = filter_input(INPUT_GET, 'StateId') ?? null;
if (empty($stateId)) {
    throw new SimpleSAML_Error_BadRequest('Missing required StateId query parameter.');
}

$state = SimpleSAML_Auth_State::loadState($stateId, 'expirychecker:expired');

if (array_key_exists('changepwd', $_REQUEST)) {
    
    /* Now that they've clicked change-password, skip the splash pages very
     * briefly, to let the user get to the change-password website.  */
    ExpiryDate::skipSplashPagesFor(60); // 60 seconds = 1 minute
    
    // The user has pressed the change-password button.
    $passwordChangeUrl = $state['passwordChangeUrl'];
    
    // Add the original url as a parameter
    if (array_key_exists('saml:RelayState', $state)) {
        $stateId = SimpleSAML_Auth_State::saveState(
            $state,
            'expirychecker:about2expire'
        );
      
        $returnTo = sspmod_expirychecker_Utilities::getUrlFromRelayState(
            $state['saml:RelayState']
        );
        if (! empty($returnTo)) {
            $passwordChangeUrl .= '?returnTo=' . $returnTo;
        }
    }
    
    SimpleSAML_Utilities::redirect($passwordChangeUrl, array());
}

$globalConfig = SimpleSAML_Configuration::getInstance();

$t = new SimpleSAML_XHTML_Template($globalConfig, 'expirychecker:expired.php');
$t->data['formTarget'] = SimpleSAML\Module::getModuleURL('expirychecker/expired.php');
$t->data['formData'] = ['StateId' => $stateId];
$t->data['expiresAtTimestamp'] = $state['expiresAtTimestamp'];
$t->data['accountName'] = $state['accountName'];
$t->show();

SimpleSAML\Logger::info('expirychecker - User has been told that their password has expired.');
