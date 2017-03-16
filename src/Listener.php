<?php

namespace SiteMaster\Plugins\Auth_shib;

use SiteMaster\Core\Events\GetAuthenticationPlugins;
use SiteMaster\Core\Events\RoutesCompile;
use SiteMaster\Core\Events\User\Search;
use SiteMaster\Core\Plugin\PluginListener;

class Listener extends PluginListener
{
    public function onRoutesCompile(RoutesCompile $event)
    {
        $event->addRoute('/^auth\/shib\/$/', __NAMESPACE__ . '\Auth\Login');
        $event->addRoute('/^auth\/shib\/callback\/$/', __NAMESPACE__ . '\Auth\Login');
        $event->addRoute('/^auth\/shib\/logout\/$/', __NAMESPACE__ . '\Auth\Logout');
        $event->addRoute('/^auth\/shib\/logout\/callback\/$/', __NAMESPACE__ . '\Auth\Logout');
    }

    public function onGetAuthenticationPlugins(GetAuthenticationPlugins $event)
    {
        $event->addPlugin($this->plugin);
    }

    public function onUserSearch(Search $event)
    {
        //TODO: we could remove this?
        return [];
    }
}