<?php

use Psr\Log\LoggerInterface;
use Sil\Psr3Adapters\Psr3SamlLogger;
use Sil\SspExpiryChecker\Validator;

/**
 * Filter which either warns the user that their password is "about to expire"
 * (giving them the option of changing it now or later) or tells them that it
 * has expired (only allowing them to go change their password).
 *
 * See README.md for sample (and explanation of) expected configuration.
 */
class sspmod_expirychecker_Auth_Process_ExpiryDate extends SimpleSAML_Auth_ProcessingFilter
{
    private $warndaysbefore = 14;
    private $original_url_param = 'originalurl';
    private $changepwdurl = NULL;
    private $accountNameAttr = NULL;
    private $expirydate_attr = NULL;
    private $date_format = 'Y-m-d';
    
    /** @var LoggerInterface */
    protected $logger;
    
    /**
     * Initialize this filter.
     *
     * @param array $config  Configuration information about this filter.
     * @param mixed $reserved  For future use.
     */
    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);
        
        $this->initComposerAutoloader();
        
        assert('is_array($config)');
        
        $this->initLogger($config['logger'] ?? []);
        
        $this->loadValuesFromConfig($config, [
            'warndaysbefore' => [
                Validator::INT,
            ],
            'original_url_param' => [
                Validator::STRING,
                Validator::NOT_EMPTY,
            ],
            'changepwdurl' => [
                Validator::STRING,
                Validator::NOT_EMPTY,
            ],
            'accountNameAttr' => [
                Validator::STRING,
                Validator::NOT_EMPTY,
            ],
            'expirydate_attr' => [
                Validator::STRING,
                Validator::NOT_EMPTY,
            ],
            'date_format' => [
                Validator::STRING,
                Validator::NOT_EMPTY,
            ],
        ]);
    }
    
    protected function loadValuesFromConfig($config, $attributeRules)
    {
        foreach ($attributeRules as $attribute => $rules) {
            
            if (array_key_exists($attribute, $config)) {
                $this->$attribute = $config[$attribute];
            }
            
            Validator::validate($this->$attribute, $rules, $this->logger, $attribute);
        }
    }
    
    protected function temp()
    {
        if (array_key_exists('warndaysbefore', $config)) {
            $this->warndaysbefore = $config['warndaysbefore'];
            if ( ! is_int($this->warndaysbefore)) {
                $exception = new Exception(sprintf(
                    'Invalid value for number of days (%s) given to '
                    . 'expirychecker::ExpiryDate filter.',
                    var_export($this->warndaysbefore, true)
                ), 1496770709);
                $this->logger->critical($exception->getMessage());
                throw $exception;
            }
        }
        
        if (array_key_exists('original_url_param', $config)) {
            $this->original_url_param = $config['original_url_param'];
            if ( ! is_string($this->original_url_param)) {
                throw new Exception('Invalid paramater name for the ' . 
                                    'original url provided to ' . 
                                    'expirychecker::ExpiryDate filter.');
            }
        }

        if (array_key_exists('changepwdurl', $config)) {
            $this->changepwdurl = $config['changepwdurl'];
            if ( ! is_string($this->changepwdurl)) {
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
            if ( ! is_string($this->expirydate_attr)) {
                throw new Exception('Invalid attribute name given as ExpiryDate ' . 
                                    'to expirychecker::ExpiryDate filter.');
            }
        }

        if (array_key_exists('date_format', $config)) {
            $this->date_format = $config['date_format'];
            if ( ! is_string($this->date_format)) {
                throw new Exception('Invalid date format given to ' . 
                                    'expirychecker::ExpiryDate filter.');
            }
        }
    }
    
    /**
     * Get the specified attribute from the given state data.
     *
     * NOTE: If the attribute's data is an array, the first value will be
     *       returned. Otherwise, the attribute's data will simply be returned
     *       as-is.
     *
     * @param string $attributeName The name of the attribute.
     * @param array $state The state data.
     * @return mixed The attribute value, or null if not found.
     */
    protected function getAttribute($attributeName, $state)
    {
        $attributeData = $state['Attributes'][$attributeName] ?? null;
        
        if (is_array($attributeData)) {
            return $attributeData[0] ?? null;
        }
        
        return $attributeData;
    }
    
    /**
     * Calculate how many days remain between now and when the password will
     * expire.
     *
     * @param int $expiryTimestamp The timestamp for when the password will
     *     expire.
     * @return int The number of days remaining
     */
    protected function getDaysLeftBeforeExpiry($expiryTimestamp)
    {
        $now = time();
        $end = $expiryTimestamp;
        return round(($end - $now) / (24*60*60));
    }
    
    /**
     * Get the timestamp for when the user's password will expire, throwing an
     * exception if unable to do so.
     *
     * @param string $expirydate_attr The name of the attribute where the
     *     expiration date (as a string) is stored.
     * @param array $state The state data.
     * @return int The expiration timestamp.
     * @throws Exception
     */
    protected function getExpiryTimestamp($expirydate_attr, $state)
    {
        $expiryDateString = $this->getAttribute($expirydate_attr, $state);
        
        // Ensure that EVERY user login provides a usable password expiration date.
        $expiryTimestamp = strtotime($expiryDateString) ?: null;
        if (empty($expiryTimestamp)) {
            throw new Exception(sprintf(
                "We could not understand the expiration date (%s, from %s) for "
                . "the user's password, so we do not know whether their "
                . "password is still valid.",
                var_export($expiryDateString, true),
                var_export($expirydate_attr, true)
            ), 1496843359);
        }
        return $expiryTimestamp;
    }
    
    protected function initComposerAutoloader()
    {
        $path = __DIR__ . '/../../../vendor/autoload.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
    
    protected function initLogger($loggerConfig)
    {
        $loggerClass = $loggerConfig['class'] ?? Psr3SamlLogger::class;
        $this->logger = new $loggerClass();
    }
    
    /**
     * See if the given timestamp is in the past.
     *
     * @param int $timestamp The timestamp to check.
     * @return bool
     */
    public function isDateInPast(int $timestamp)
    {
        return ($timestamp < time());
    }    
    
    /**
     * Check whether the user's password has expired.
     *
     * @param int $expiryTimestamp The timestamp for when the user's password
     *     will expire.
     * @return bool
     */
    public function isExpired(int $expiryTimestamp)
    {
        return $this->isDateInPast($expiryTimestamp);
    }
    
    /**
     * Check whether it's time to warn the user that they will need to change
     * their password soon.
     *
     * @param int $expiryTimestamp The timestamp for when the password expires.
     * @param int $warnDaysBefore How many days before the expiration we should
     *     warn the user.
     * @return boolean
     */
    public function isTimeToWarn($expiryTimestamp, $warnDaysBefore)
    {
        $daysLeft = $this->getDaysLeftBeforeExpiry($expiryTimestamp);
        return ($daysLeft <= $warnDaysBefore);
    }

    /**
     * Redirect the user to the change password url if they haven't gone
     *   there in the last 10 minutes
     * @param array $state
     * @param string $accountName
     * @param string $changePwdUrl
     * @param string $change_pwd_session
     */
    public function redirect2PasswordChange(
        &$state,
        $accountName,
        $changePwdUrl,
        $change_pwd_session,
        $expiryTimestamp
    ) {
        $sessionType = 'expirychecker';
        /* Save state and redirect. */
        $state['expireOnDate'] = date($this->date_format, $expiryTimestamp);
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

        $this->logger->warning(sprintf(
            'expirychecker: Password for %s is about to expire, redirecting to %s',
            var_export($accountName, true),
            var_export($changePwdUrl, true)
        ));

        SimpleSAML_Utilities::redirect($changePwdUrl, array());
    }
    
    /**
     * Apply this AuthProc Filter.
     *
     * @param array &$state The current state.
     */
    public function process(&$state)
    {
        // Get the necessary info from the state data.
        $accountName = $this->getAttribute($this->accountNameAttr, $state);
        $expiryTimestamp = $this->getExpiryTimestamp($this->expirydate_attr, $state);
        
        if ($this->isExpired($expiryTimestamp)) {
            $this->redirectToExpiredPage($state, $accountName, $expiryTimestamp);
        }
        
        // If we set a special session value to say they've already been redirected
        // to the change password page, then don't redirect them again.
        $change_pwd_session = 'sent_to_change_password';
        $session = SimpleSAML_Session::getInstance();
        $expiry_data = $session->getDataOfType('expirychecker');

        if (array_key_exists($change_pwd_session, $expiry_data)) {
            SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
        }

        // Redirect the user to the change password URL if it's time to do so.
        if ($this->isTimeToChangePassword($expiryTimestamp)) {
            self::redirect2PasswordChange(
                $state,
                $accountName,
                $this->changepwdurl,
                $change_pwd_session,
                $expiryTimestamp
            );
        }

        // Display a password expiration warning page if it's time to do so.
        if ($this->isTimeToWarn($expiryTimestamp, $this->warndaysbefore)) {
            $this->redirectToWarningPage($state, $accountName, $expiryTimestamp);
        }
    }
    
    /**
     * Redirect the user to the expired-password page.
     *
     * @param array $state The state data.
     * @param string $accountName The name of the user account.
     * @param int $expiryTimestamp When the password expired.
     */
    public function redirectToExpiredPage(&$state, $accountName, $expiryTimestamp)
    {
        $expireOnDate = date($this->date_format, $expiryTimestamp);
        $this->logger->error(sprintf(
            'expirychecker: Password for %s has expired [%s]. Access denied!',
            $accountName,
            $expireOnDate
        ));
        $globalConfig = SimpleSAML_Configuration::getInstance();

        /* Save state and redirect. */
        $state['expireOnDate'] = $expireOnDate;
        $state['accountName'] = $accountName;
        $id = SimpleSAML_Auth_State::saveState($state, 'expirywarning:expired');
        $url = SimpleSAML_Module::getModuleURL('expirychecker/expired.php');
        SimpleSAML_Utilities::redirect($url, array('StateId' => $id));
    }
    
    /**
     * Redirect the user to the warning page.
     *
     * @param array $state The state data.
     * @param string $accountName The name of the user account.
     * @param int $expiryTimestamp When the password will expire.
     */
    protected function redirectToWarningPage(&$state, $accountName, $expiryTimestamp)
    {
        assert('is_array($state)');
        
        $daysLeft = $this->getDaysLeftBeforeExpiry($expiryTimestamp);
        $state['daysleft'] = $daysLeft;
        
        if (isset($state['isPassive']) && $state['isPassive'] === TRUE) {
          /* We have a passive request. Skip the warning. */
          return;
        }
        
        $this->logger->warning(sprintf(
            'expirychecker: Password for %s is about to expire!',
            var_export($accountName, true)
        ));
        
        /* Save state and redirect. */
        $state['expireOnDate'] = date($this->date_format, $expiryTimestamp);
        $state['accountName'] = $accountName;
        $state['changepwdurl'] = $this->changepwdurl;
        $state['original_url_param'] = $this->original_url_param;
        $id = SimpleSAML_Auth_State::saveState($state, 'expirywarning:about2expire');
        $url = SimpleSAML_Module::getModuleURL('expirychecker/about2expire.php');
        SimpleSAML_Utilities::redirect($url, array('StateId' => $id));
    }
}
