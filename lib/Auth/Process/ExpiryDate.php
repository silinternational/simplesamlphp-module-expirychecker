<?php

/**
 * Filter which shows "about to expire" warning or deny access if netid is expired.
 *
 * Based on preprodwarning module by rnd.feide.no
 *
 * <code>
 * // show about2expire warning or deny access if netid is expired
 * 17 => array(
 *     'class' => 'expirychecker:ExpiryDate',
 *     'netid_attr' => 'cn',
 *     'expirydate_attr' => 'pwdExpiryTime',
 *     'warndaysbefore' => 21,
 *     'redirectdaysbefore' => 7,
 *     'pwdGraceAuthNLimit' => 60*60*24*30,  // added by GTIS
 *     'original_url_param' => 'originalurl',
 *     'changepwdurl' => 'https://idm.example.com/pwdmgr/',
 *     'date_format' => 'm.d.Y', # php date syntax
 * ),
 * </code>
 *
 * @author Alex Mihičinac, ARNES. <alexm@arnes.si>; Modified by Steve Moitozo <steve_moitozo@sil.org>
 * @package simpleSAMLphp
 * @version $Id$
 */

class sspmod_expirychecker_Auth_Process_ExpiryDate extends SimpleSAML_Auth_ProcessingFilter {

    private $warndaysbefore = 0;
    private $redirectdaysbefore = 0;
    private $original_url_param = 'originalurl';
    private $changepwdurl = NULL;
    private $accountNameAttr = NULL;
    private $expirydate_attr = NULL;
    private $date_format = 'd.m.Y';
    private $expireOnDate = NULL;


    /**
     * Initialize this filter.
     *
     * @param array $config  Configuration information about this filter.
     * @param mixed $reserved  For future use.
     */
    public function __construct($config, $reserved) {
        parent::__construct($config, $reserved);

        assert('is_array($config)');
        
        if (array_key_exists('pwdGraceAuthNLimit', $config)) {
            $this->pwdGraceAuthNLimit = $config['pwdGraceAuthNLimit'];
            if (!is_int($this->warndaysbefore)) {
                throw new Exception('Invalid value for number of seconds for ' . 
                                    'pwdGraceAuthNLimit given to ' . 
                                    'expirychecker::ExpiryDate filter.');
            }
        } else {
            $this->pwdGraceAuthNLimit = 0;
        }

        if (array_key_exists('warndaysbefore', $config)) {
            $this->warndaysbefore = $config['warndaysbefore'];
            if (!is_int($this->warndaysbefore)) {
                throw new Exception(sprintf(
                    'Invalid value for number of days (%s) given to '
                    . 'expirychecker::ExpiryDate filter.',
                    var_export($this->warndaysbefore, true)
                ), 1496770709);
            }
        } else {
            $this->warndaysbefore = 0;
        }

        if (array_key_exists('redirectdaysbefore', $config)) {
            $this->redirectdaysbefore = $config['redirectdaysbefore'];
            if (!is_int($this->redirectdaysbefore)) {
                throw new Exception('Invalid value for the redirect threshold ' . 
                                    'days given to expirychecker::ExpiryDate filter.');
            }
        } else {
            $this->redirectdaysbefore = 0;
        }

        if (array_key_exists('original_url_param', $config)) {
            $this->original_url_param = $config['original_url_param'];
            if (!is_string($this->original_url_param)) {
                throw new Exception('Invalid paramater name for the ' . 
                                    'original url provided to ' . 
                                    'expirychecker::ExpiryDate filter.');
            }
        }

        if (array_key_exists('changepwdurl', $config)) {
            $this->changepwdurl = $config['changepwdurl'];
            if (!is_string($this->changepwdurl)) {
                throw new Exception('Invalid password change URL provided to ' . 
                                    'expirychecker::ExpiryDate filter.');
            }
        }

        if (array_key_exists('accountNameAttr', $config)) {
            $this->accountNameAttr = $config['accountNameAttr'];
            if ( ! is_string($this->accountNameAttr)) {
                throw new Exception('Invalid accountNameAttr given to ' . 
                                    'expirychecker::ExpiryDate filter.');
            }
        }

        if (array_key_exists('expirydate_attr', $config)) {
            $this->expirydate_attr = $config['expirydate_attr'];
            if (!is_string($this->expirydate_attr)) {
                throw new Exception('Invalid attribute name given as ExpiryDate ' . 
                                    'to expirychecker::ExpiryDate filter.');
            }
        }

        if (array_key_exists('date_format', $config)) {
            $this->date_format = $config['date_format'];
            if (!is_string($this->date_format)) {
                throw new Exception('Invalid date format given to ' . 
                                    'expirychecker::ExpiryDate filter.');
            }
        }
    }

    /**
     *  Check if given date is older than today
     *  @param time $checkDate
     *  @return bool
     *
     */
    public function isDateInPast($checkDate) {
        $now = time();

        if ($now > $checkDate) {
          return true;
        } else {
          return false;
        }
    }    
    
    /**
     * Check if it's time to warn user to change their password
     *  based on whether the remaining days is equal or under 
     *  the warndaysbefore value.
     * @param array $state
     * @return bool
     *
     */
    public function isTimeToWarn(&$state) {
        #date_default_timezone_set('Europe/Ljubljana');
        $now = time();
        $end = $this->expireOnDate;
        $days = round(($end - $now) / (24*60*60));
        
        if ($days <= $this->warndaysbefore) {
            $state['daysleft'] = $days;
            return true;
        }
        
        return false;
    }

    /**
     * Check if it's time to redirect the user to the change password page
     *   based on whether the remaining days is equal or under defined $redirectdaysbefore
     * @param array $state
     * @return bool
     *
     */
    public function isTimeToChangePassword(&$state) {
      #date_default_timezone_set('Europe/Ljubljana');
      $now = time();
      $end = $this->expireOnDate;

      $days = (int)(($end - $now) / (24*60*60));
      if ($days <= $this->redirectdaysbefore) {
          return true;
      }

      return false;
    }
    
    /**
     * Redirect the user to the expired-password page, if it's past
     * the grace period
     * @param array $state
     * @param string $accountName
     */     
    public function redirectIfExpired(&$state, $accountName) {
     
        $hardExpireDate = $this->expireOnDate + $this->pwdGraceAuthNLimit;        
        
        if (self::isDateInPast($hardExpireDate)) {
            SimpleSAML_Logger::error('expirychecker: Password for ' . $accountName .
                                     ' has expired [' . 
                                     date($this->date_format, $this->expireOnDate) . 
                                     ']. Access denied!');
            $globalConfig = SimpleSAML_Configuration::getInstance();

            /* Save state and redirect. */
            $state['expireOnDate'] = date($this->date_format, $this->expireOnDate);
            $state['accountName'] = $accountName;
            $id = SimpleSAML_Auth_State::saveState($state, 'expirywarning:expired');
            $url = SimpleSAML_Module::getModuleURL('expirychecker/expired.php');
            SimpleSAML_Utilities::redirect($url, array('StateId' => $id));
        }        
    }
    
    /**
     * Redirect the user to the change password url if they haven't gone
     *   there in the last 10 minutes
     * @param array $state
     * @param string $accountName
     * @param string $changePwdUrl
     * @param string $change_pwd_session
     */
    public function redirect2PasswordChange(&$state, $accountName,
                                            $changePwdUrl, $change_pwd_session) {
                        
        $sessionType = 'expirychecker';
        /* Save state and redirect. */
        $state['expireOnDate'] = date($this->date_format, $this->expireOnDate);
        $state['accountName'] = $accountName;
        $id = SimpleSAML_Auth_State::saveState($state,
            'expirywarning:redirected_to_password_change_url');
        $ignoreMinutes = 60;

        $session = SimpleSAML_Session::getInstance();     
        $idpExpirySession = $session->getData($sessionType,$change_pwd_session);
        
        // If the session shows that the User already passed this way,
        //  don't redirect to change password page
        if ($idpExpirySession !== Null) {
            SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
        } else {
            // Otherwise, set a value to tell us they've probably changed
            // their password, in order to allow password to get propagated
            $session->setData($sessionType, $change_pwd_session, 
                              1, (60 * $ignoreMinutes));
            $session->saveSession();
        }
        
        
       //   If state already has the change password url, go straight there to 
       //   avoid eternal loop between that and the idp
        if (array_key_exists('saml:RelayState', $state)) {
            $relayState = $state['saml:RelayState'];
            if (strpos($relayState, $changePwdUrl) !==false) {                
                SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
        // otherwise add the original destination url as a parameter
            } else {
                $returnTo = sspmod_expirychecker_Utilities::getUrlFromRelayState(
                                                                        $relayState);
                if ($returnTo) {                                 
                    $changePwdUrl = $changePwdUrl . "?returnTo=" . $returnTo;
                }
            }
        }

        SimpleSAML_Logger::warning('expirychecker: Password for ' . $accountName .
                                   ' is about to expire, redirecting to ' .
                                   $changePwdUrl);

        SimpleSAML_Utilities::redirect($changePwdUrl, array());
    }
    
    
    
    /**
     * Apply filter
     *
     * @param array &$state  The current state.
     */
    public function process(&$state) {
        /*
         * UTC format: 20090527080352Z
         */
        $accountName = $state['Attributes'][$this->accountNameAttr][0];
        $this->expireOnDate = strtotime($state['Attributes'][$this->expirydate_attr][0]);
        $changePwdUrl = $this->changepwdurl;
        
        self::redirectIfExpired($state, $accountName);      

        // If we set a special session value to say they've already been redirected
        // to the change password page, then don't redirect them again.
        $change_pwd_session = 'sent_to_change_password';
        $session = SimpleSAML_Session::getInstance();
        $expiry_data = $session->getDataOfType('expirychecker');

        if (array_key_exists($change_pwd_session, $expiry_data)) {
            SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
        }

        // Redirect the user to the change password URL if it's time
        if (self::isTimeToChangePassword($state)) {
            self::redirect2PasswordChange($state, $accountName, $changePwdUrl, 
                                          $change_pwd_session);
        }

        // Display a password expiration warning page if it's time
        if (self::isTimeToWarn($state)) {
            assert('is_array($state)');
            if (isset($state['isPassive']) && $state['isPassive'] === TRUE) {
              /* We have a passive request. Skip the warning. */
              return;
            }

            SimpleSAML_Logger::warning('expirychecker: Password for ' . $accountName .
                                       ' is about to expire!');

            /* Save state and redirect. */
            $state['expireOnDate'] = date($this->date_format, $this->expireOnDate);
            $state['accountName'] = $accountName;
                              $state['changepwdurl'] = $this->changepwdurl;
            $state['original_url_param'] = $this->original_url_param;
            $id = SimpleSAML_Auth_State::saveState($state, 'expirywarning:about2expire');
            $url = SimpleSAML_Module::getModuleURL('expirychecker/about2expire.php');
            SimpleSAML_Utilities::redirect($url, array('StateId' => $id));
        }
    }
}

?>