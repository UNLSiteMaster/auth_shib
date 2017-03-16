<?php
namespace SiteMaster\Plugins\Auth_shib\Auth;

use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\User\Session;
use SiteMaster\Core\ViewableInterface;

class Logout implements ViewableInterface
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

        if (strpos($options['current_url'], 'callback') === false) {
            //Handle the logout callback
            Session::logOut();
            Controller::redirect($this->getURL() . 'callback/');
        }
    }
    
    public function getServiceProviderLogout()
    {
        $options = $this->plugin->getOptions();
        return $options['logout_url'];
    }

    /**
     * The URL for this page
     *
     * @return string
     */
    public function getURL()
    {
        return Config::get('URL') . 'auth/shib/logout/';
    }

    /**
     * The page title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return "Shibboleth Logout";
    }
}