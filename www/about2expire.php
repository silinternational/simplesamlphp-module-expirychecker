<?php

$stateId = filter_input(INPUT_GET, 'StateId') ?? null;
if (empty($stateId)) {
    throw new SimpleSAML_Error_BadRequest('Missing required StateId query parameter.');
}

$state = SimpleSAML_Auth_State::loadState($stateId, 'expirychecker:about2expire');

/* See if they're on their way to the change password page, and if so, let them
 * straight through.   */
$chgPwdUrlQueryParam = "&RelayState=" .  urlencode($state['changePwdUrl']);
if (strpos($stateId, $chgPwdUrlQueryParam) !== false) {
    SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
}

if (array_key_exists('continue', $_REQUEST)) {
    
    // The user has pressed the continue button.
    SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
}

if (array_key_exists('changepwd', $_REQUEST)) {
    
    // The user has pressed the change-password button.
    $changePwdUrl = $state['changePwdUrl'];
    
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
            $changePwdUrl .= '?ReturnTo=' . $returnTo;
        }
    }

    $changePwdSession = 'sent_to_change_password';
    $session = SimpleSAML_Session::getSession();
    
    // set a value to tell us they've probably changed
    // their password, in order to allow password to get propagated
    $session->setData('expirychecker', $changePwdSession, 1, (60*10));
    $session->save();
    SimpleSAML_Utilities::redirect($changePwdUrl, array());
}


$globalConfig = SimpleSAML_Configuration::getInstance();

$t = new SimpleSAML_XHTML_Template($globalConfig, 'expirychecker:about2expire.php');
$t->data['formTarget'] = SimpleSAML_Module::getModuleURL('expirychecker/about2expire.php');
$t->data['formData'] = array('StateId' => $stateId);
$t->data['daysleft'] = $state['daysleft'];
$t->data['dayOrDays'] = (intval($state['daysleft']) === 1 ? 'day' : 'days');
$t->data['expireOnDate'] = $state['expireOnDate'];
$t->data['accountName'] = $state['accountName'];
$t->show();

SimpleSAML_Logger::info('expirychecker - User has been warned that their password will expire soon.');
