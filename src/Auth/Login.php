<?php
namespace SiteMaster\Plugins\Auth_shib\Auth;

use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\UnexpectedValueException;
use SiteMaster\Core\User\Session;
use SiteMaster\Core\User\User;
use SiteMaster\Core\ViewableInterface;

class Login implements ViewableInterface
{
    /**
     * @var \SiteMaster\Core\Plugin\AuthenticationInterface
     */
    
    protected $plugin;
    /**
     * @param array $options
     */
    function __construct($options = array())
    {
        $this->plugin = PluginManager::getManager()->getPluginInfo('auth_shib');
        $plugin_options = $this->plugin->getOptions();
        
        if (!isset($plugin_options['login_url'])) {
            throw new InvalidArgumentException('the login_url must be set for auth_shib');
        }

        if (!isset($plugin_options['logout_url'])) {
            throw new InvalidArgumentException('the logout_url must be set for auth_shib');
        }

        if (strpos($options['current_url'], 'callback') !== false) {
            //handle callback
            $this->handleCallback();
            exit();
        }
        
        //Otherwise, we are trying to log in
        Controller::redirect($plugin_options['login_url'] . '?target=' . urlencode($this->getURL().'callback/'));
    }
    
    protected function handleCallback()
    {
        if (!isset($_SERVER['REMOTE_USER']) || empty($_SERVER['REMOTE_USER'])) {
            throw new UnexpectedValueException('Missing the auth_shib user environment variable');
        }

        $parts = explode('@', $_SERVER['REMOTE_USER']);

        if (count($parts) !== 2) {
            throw new UnexpectedValueException('Invalid username. Please make sure your institution is sending a valid EPPN value.');
        }
        
        //Try to get the user record if it exsists
        $user = User::getByUIDAndProvider($parts[0], $parts[1]);
        
        if (!$user) {
            //Return the user
            $user = User::createUser($parts[0], $parts[1]);
        }
        
        //Update email if we need to
        if (
            isset($_SERVER['mail'])
            && filter_var($_SERVER['mail'], FILTER_VALIDATE_EMAIL)
            && $_SERVER['mail'] != $user->email
        ) {
            $user->email = $_SERVER['mail'];
        }

        //first name
        if (isset($_SERVER['givenName']) && $_SERVER['givenName'] != $user->first_name) {
            $user->first_name = $_SERVER['givenName'];
        }

        //last name
        if (isset($_SERVER['sn']) && $_SERVER['sn'] != $user->last_name) {
            $user->last_name = $_SERVER['sn'];
        }
        
        //Make changes
        $user->save();
        
        //Log the user in
        Session::logIn($user, $this->plugin->getProviderMachineName());

        if (isset($_GET['r'])) {
            //redirect if we need to
            Controller::redirect($_GET['r']);
        } else {
            Controller::redirect($user->getURL());
        }
    }

    /**
     * The URL for this page
     *
     * @return string
     */
    public function getURL()
    {
        return Config::get('URL') . 'auth/shib/';
    }

    /**
     * The page title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return "Shibboleth Authentication";
    }
}