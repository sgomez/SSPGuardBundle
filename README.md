# SimpleSAMLphp Integration for Symfony: SSPGuardBundle

This bundle helps you to use your [SimpleSAMLphp](https://simplesamlphp/) installation with Symfony. This bundle uses
[Guard](https://symfony.com/blog/new-in-symfony-2-8-guard-authentication-component) Component to authenticate
users. 

This package is based on these two bundles:

* [KnpUOAuth2ClientBundle](https://github.com/knpuniversity/oauth2-client-bundle)
* [SamlBundle](https://github.com/pdias/SamlBundle)

## Installation

### Step 1: Download the Bundle

Install the library via [Composer](https://getcomposer.org/) by
running the following command:

```bash
composer require sgomez/ssp-guard-bundle
```

### Step 2: Enable the Bundle

Next, enable the bundle in your `app/AppKernel.php` file:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Sgomez\Bundle\SSPGuardBundle\SSPGuardBundle(),
        // ...
    );
}
```

### Step 3: Load the routes of the Bundle

Load the routes of the bundle by adding this configuration at the very beginning of the `app/config/routing.yml` file:

```yaml
# app/config/routing.yml
ssp_bundle:
    resource: "@SSPGuardBundle/Resources/config/routing/connect.xml"
# ...
```

### Step 4: Configure the bundle

You need to configure the path where SimpleSAMLphp is installed and the authsources you want to use.
This is a sample configuration that you need in the `app/config/config.yml` file:

```yaml
ssp_guard:
    installation_path: /var/simplesamlphp
    auth_sources:
        admin:
            title: Admin
            user_id: user
        symfony:
            title: My IDP
            user_id: uid
```

Where in this example `admin` and `symfony` are names defined in your SSP's
`authsources.php`.

### Step 5: Create Guard Authentication classes

In order to authenticate you need to create a Guard authenticator for each authsource you use.

A `SSPGuardAuthenticator` base class exists to do it easy:

```php
<?php
// src/AppBundle/Security/AdminAuthenticator.php

namespace AppBundle\Security;

use Sgomez\Bundle\SSPGuardBundle\Security\Authenticator\SSPGuardAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdminAuthenticator extends SSPGuardAuthenticator
{
    public function start(Request $request, AuthenticationException $authException = null)
    {
        // Change it to your login path 
        return new RedirectResponse($this->router->generate('login'));
    }
    
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['user'][0]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->saveAuthenticationErrorToSession($request, $exception);

        return new RedirectResponse($this->router->generate('login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $targetPath = $this->getTargetPath($request, $providerKey);

        if (!$targetPath) {
            // Change it to your default target
            $targetPath = $this->router->generate('homepage');
        }

        return new RedirectResponse($targetPath);
    }
}
```

And create the service definition, e.g.:

```xml
<service id="app.admin.authenticator" class="AppBundle\Security\AdminAuthenticator">
    <argument type="service" id="router"/>
    <argument type="service" id="ssp.guard.registry"/>
    <argument>admin</argument> <!-- this is the authsource id -->
</service>
```

or in `app/config/services.yml`:

```yml
AppBundle\Security\AdminAuthenticator:
    arguments: ["@router", "@ssp.guard.registry", "admin"] 
```

### Step 6: Create a custom User Provider

If you use [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle) you can use it or you can create your own 
[custom User Provider](https://symfony.com/doc/current/cookbook/security/custom_provider.html).

Your user provider will be passed to `SSPGuardAuthenticator::getUser` method and it's used to search users.

### Step 7: Configure the Security

You need to configure the `app/config/security.yml` to use the Guard Authenticators:

```yml
# app/config/security.yml

    providers:
        fos_userbundle:
            id: fos_user.user_provider.username

    firewalls:
        main:
            guard:
                provider: fos_userbundle
                authenticators:
                    - app.admin.authenticator 
                    - ... # you can add as many authsources as you want

    access_control:
        - { path: ^/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/connect$, role: IS_AUTHENTICATED_ANONYMOUSLY }
```

### Step 8: The routes

To init the login proccess you need to put a link to `ssp_guard_connect`. There are two twig functions to help
you with this: `ssp_auth_sources` and `spp_auth_source`. This could be an example of a login template:

```twig
{% for source in ssp_auth_sources() %}
    {% set item = ssp_auth_source(source) %}
    <a href="{{ path('ssp_guard_connect', {'authSource': source}) }}">
        {{ item.title }}
    </a>
{% endfor %}
```

