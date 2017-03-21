# auth_shib

This plugin adds support for shibboleth login.

## Requirements:

* mod_shib installed and a service provider configured

## Install

1) set up mod_shib on your server
2) Configure apache to use shibboleth but also allow browsing anonymously. See the apache config example:
3) Add the plugin to your config.inc.php file and set the `login_url` and `logout_url` for your shibboleth service provider.
4) From the root of sitemaster, run `php scripts/update.php` to install the plugin
5) optional: add a login link in your theme

Apache config example:
```
<Location />
  AuthType shibboleth
  #use shibboleth auth, but allow browsing anonymously (don't require a session)
  require shibboleth
</Location>
```

Example Configuration
```
Config::set('PLUGINS', [
    //... other plugins
    
    //the shib plugin
    'auth_shib' => [
        'login_url' => 'https://localhost/Shibboleth.sso/Login',
        'logout_url' => 'https://localhost/Shibboleth.sso/Logout',
    ],
]);
```

## How it works:

Once mod_shib is installed and a user has a session with your service provider, the `REMOTE_USER` environment variable will be passed to the plugin. A user will be created for that `REMOTE_USER`, and information will be updated on later logins if available and changed.

`REMOTE_USER` should be an EPPN value of the format `username@domain.edu`. In this case, username would map to the user's `uid` and `domain.edu` would map to the user's `provider` value. This is done to support logging in the same user with multiple authentication plugins (both CAS and Shib for example). However, it is important for the system admin to NEVER install another authentication plugin that might have a uid/provider name collision.

Tracked user information:

| Shib Attribute | User record |
|----------------|-------------|
| mail | email |
| givenName | first_name |
| sn | last_name |

