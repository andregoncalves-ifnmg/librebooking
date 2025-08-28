<?php

@define('LDAP_OPT_DIAGNOSTIC_MESSAGE', 0x0032);
@putenv('LDAPTLS_REQCERT=never');
require_once(ROOT_DIR . 'lib/Application/Authentication/namespace.php');
require_once(ROOT_DIR . 'plugins/Authentication/Ldap/namespace.php');

/**
 * Provides LDAP authentication/synchronization for LibreBooking
 * @see IAuthorization
 */
class Ldap extends Authentication implements IAuthentication
{
    /**
     * @var IAuthentication
     */
    private $authToDecorate;

    /**
     * @var Ldap2Wrapper
     */
    private $ldap;

    /**
     * @var LdapOptions
     */
    private $options;

    /**
     * @var IRegistration
     */
    private $_registration;

    /**
     * @var PasswordEncryption
     */
    private $_encryption;

    /**
     * @var LdapUser
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    public function SetRegistration($registration)
    {
        $this->_registration = $registration;
    }

    private function GetRegistration()
    {
        if ($this->_registration == null) {
            $this->_registration = new Registration();
        }

        return $this->_registration;
    }

    public function SetEncryption($passwordEncryption)
    {
        $this->_encryption = $passwordEncryption;
    }

    private function GetEncryption()
    {
        if ($this->_encryption == null) {
            $this->_encryption = new PasswordEncryption();
        }

        return $this->_encryption;
    }


    /**
     * @param IAuthentication $authentication Authentication class to decorate
     * @param Ldap2Wrapper $ldapImplementation The actual LDAP implementation to work against
     * @param LdapOptions $ldapOptions Options to use for LDAP configuration
     */
    public function __construct(IAuthentication $authentication, $ldapImplementation = null, $ldapOptions = null)
    {
        if (!function_exists('ldap_connect')) {
            echo 'No LDAP support for PHP.  See: http://www.php.net/ldap';
        }

        $this->authToDecorate = $authentication;

        $this->options = $ldapOptions;
        if ($ldapOptions == null) {
            $this->options = new LdapOptions();
        }

        if ($this->options->IsLdapDebugOn()) {
            ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
        }

        $this->ldap = $ldapImplementation;
        if ($ldapImplementation == null) {
            $this->ldap = new Ldap2Wrapper($this->options);
        }
    }

    public function Validate($username, $password)
    {
        $valid = false;
        $this->password = $password;

        try {
            $username = $this->CleanUsername($username);
            $connected = $this->ldap->Connect();

            if ($connected) {                
                $filter = $this->options->Filter();
                $isValid = $this->ldap->Authenticate($username, $password, $filter);
                Log::Debug("Result of LDAP Authenticate for user %s: %d", $username, $isValid);

                if ($isValid) {
                    $this->user = $this->ldap->GetLdapUser($username);
                    $valid = $this->LdapUserExists();

                    if (!$valid) 
                        Log::Error("Could not load user details from LDAP. Check your ldap settings. User: %s", $username);
                } 
                else 
                    Log::Error("LDAP authentication failed for user: %s", $username);                    
            } 
            else 
                Log::Error("LDAP connection failed for user: %s", $username);                
        } catch (Exception $ex) {
            Log::Error("Exception during LDAP validation for user %s: %s", $username, $ex->getMessage());
        }   

        try {
            if (!$valid && $this->options->RetryAgainstDatabase())
                $valid = $this->authToDecorate->Validate($username, $password);
        } catch (Exception $ex) {
            Log::Error("Database fallback authentication failed for user %s. Error: %s", $username, $ex->getMessage());
            $valid = false;
        }

        return $valid;
    }

    public function Login($username, $loginContext)
    {
        $username = $this->CleanUsername($username);

        if ($this->LdapUserExists()) {
            $this->Synchronize($username);
        }

        $repo = new UserRepository();
        $user = $repo->LoadByUsername($username);
        $user->Deactivate();
        $user->Activate();
        $repo->Update($user);

        return $this->authToDecorate->Login($username, $loginContext);
    }

    public function Logout(UserSession $user)
    {
        $this->authToDecorate->Logout($user);
    }

    public function AreCredentialsKnown()
    {
        return false;
    }

    private function LdapUserExists()
    {
        return $this->user != null;
    }

    private function Synchronize($username)
    {
        $password = $this->options->RetryAgainstDatabase() ? $this->password : Password::GenerateRandom();

        $registration = $this->GetRegistration();

        $registration->Synchronize(
            new AuthenticatedUser(
                $username,
                $this->user->GetEmail(),
                $this->user->GetFirstName(),
                $this->user->GetLastName(),
                $password,
                Configuration::Instance()->GetKey(ConfigKeys::LANGUAGE),
                Configuration::Instance()->GetDefaultTimezone(),
                $this->user->GetPhone(),
                $this->user->GetInstitution(),
                $this->user->GetTitle(),
                $this->user->GetGroups()
            )
        );
    }

    private function CleanUsername($username)
    {
        if (!$this->options->CleanUsername()) {
            return $username;
        }

        if (BookedStringHelper::Contains($username, '@')) {
            Log::Debug('LDAP - Username %s appears to be an email address. Cleaning...', $username);
            $parts = explode('@', $username);
            $username = $parts[0];
        }
        if (BookedStringHelper::Contains($username, '\\')) {
            Log::Debug('LDAP - Username %s appears contain a domain. Cleaning...', $username);
            $parts = explode('\\', $username);
            $username = $parts[1];
        }

        return $username;
    }

    public function AllowUsernameChange()
    {
        return false;
    }

    public function AllowEmailAddressChange()
    {
        return false;
    }

    public function AllowPasswordChange()
    {
        return false;
    }

    public function AllowNameChange()
    {
        return false;
    }

    public function AllowPhoneChange()
    {
        return true;
    }

    public function AllowOrganizationChange()
    {
        return true;
    }

    public function AllowPositionChange()
    {
        return true;
    }
}
