<?php

use sspmod_expirychecker_Auth_Process_ExpiryDate as ExpiryDate;

$stateId = filter_input(INPUT_GET, 'StateId') ?? null;
if (empty($stateId)) {
    throw new SimpleSAML_Error_BadRequest('Missing required StateId query parameter.');
}

$state = SimpleSAML_Auth_State::loadState($stateId, 'expirychecker:about2expire');

/* Skip the splash pages for awhile, both to let the user get to the
 * change-password website and to avoid annoying them with constant warnings. */
ExpiryDate::skipSplashPagesFor(14400); // 14400 seconds = 4 hours

if (array_key_exists('continue', $_REQUEST)) {
    
    // The user has pressed the continue button.
    SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
}

if (array_key_exists('changepwd', $_REQUEST)) {
    
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
        if ( ! empty($returnTo)) {                                 
            $passwordChangeUrl .= '?returnTo=' . $returnTo;
        }
    }
    
    SimpleSAML_Utilities::redirect($passwordChangeUrl, array());
}

$globalConfig = SimpleSAML_Configuration::getInstance();

$t = new SimpleSAML_XHTML_Template($globalConfig, 'expirychecker:about2expire.php');
$t->data['formTarget'] = SimpleSAML_Module::getModuleURL('expirychecker/about2expire.php');
$t->data['formData'] = ['StateId' => $stateId];
$t->data['daysLeft'] = $state['daysLeft'];
$t->data['dayOrDays'] = (intval($state['daysLeft']) === 1 ? 'day' : 'days');
$t->data['expiresAtTimestamp'] = $state['expiresAtTimestamp'];
$t->data['accountName'] = $state['accountName'];
$t->show();

SimpleSAML\Logger::info('expirychecker - User has been warned that their password will expire soon.');
