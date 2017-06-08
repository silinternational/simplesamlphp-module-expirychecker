<?php

SimpleSAML_Logger::info('expirychecker - User has been warned that their password will expire soon.');

if ( ! array_key_exists('StateId', $_REQUEST)) {
    throw new SimpleSAML_Error_BadRequest('Missing required StateId query parameter.');
}

$id = $_REQUEST['StateId'];
$state = SimpleSAML_Auth_State::loadState($id, 'expirywarning:about2expire');

//  Check if they're on their way to change password page and if so,
//   let them straight through
$get_data = $_GET;
if (array_key_exists("StateId", $get_data)) {
    $stateId = $get_data["StateId"];
    $chgPwdUrl = "&RelayState=" .  urlencode($state['changepwdurl']);
    
    if (strpos($stateId, $chgPwdUrl) !==false) {
        SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
    };
}

if (array_key_exists('continue', $_REQUEST)) {
    
    // The user has pressed the continue button.
    SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
}

if (array_key_exists('changepwd', $_REQUEST)) {
    
    // The user has pressed the change-password button.
    $changePwdUrl = $state['changepwdurl'];
    
    // Add the original url as a parameter
    if (array_key_exists('saml:RelayState', $state)) {
        $id = SimpleSAML_Auth_State::saveState($state, 
              'expirywarning:about2expire');
        $stateId = "StateId=" . $id;
      
        $returnTo = sspmod_expirychecker_Utilities::getUrlFromRelayState(
                                                    $state['saml:RelayState']);
        if ($returnTo) {                                 
            $changePwdUrl = $changePwdUrl . "?returnTo=" . $returnTo;
        }
    }

    $change_pwd_session = 'sent_to_change_password';
    $session = SimpleSAML_Session::getInstance();
    
    // set a value to tell us they've probably changed
    // their password, in order to allow password to get propagated
    $session->setData('expirychecker', $change_pwd_session, 1, (60*10));
    $session->saveSession();
    SimpleSAML_Utilities::redirect($changePwdUrl, array());
}


$globalConfig = SimpleSAML_Configuration::getInstance();

$t = new SimpleSAML_XHTML_Template($globalConfig, 'expirychecker:about2expire.php');
$t->data['formTarget'] = SimpleSAML_Module::getModuleURL('expirychecker/about2expire.php');
$t->data['formData'] = array('StateId' => $id);
$t->data['daysleft'] = $state['daysleft'];
$t->data['expireOnDate'] = $state['expireOnDate'];
$t->data['accountName'] = $state['accountName'];
$t->show();
