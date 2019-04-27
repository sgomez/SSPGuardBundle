<?php
/*
 * This file is part of the SSPGuardBundle.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sgomez\Bundle\SSPGuardBundle\Security\Authenticator;


use Sgomez\Bundle\SSPGuardBundle\SimpleSAMLphp\AuthSourceRegistry;
use Sgomez\Bundle\SSPGuardBundle\SimpleSAMLphp\SSPAuthSource;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

abstract class SSPGuardAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var Router
     */
    protected $router;
    /**
     * @var AuthSourceRegistry
     */
    protected $authSourceRegistry;
    /**
     * @var SSPAuthSource
     */
    protected $authSource = null;

    /**
     * SSPGuardAuthenticator constructor.
     *
     * @param Router $router
     * @param AuthSourceRegistry $authSourceRegistry
     */
    public function __construct(Router $router, AuthSourceRegistry $authSourceRegistry)
    {
        $this->router = $router;
        $this->authSourceRegistry = $authSourceRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        $match = $this->router->match($request->getPathInfo());
        return 'ssp_guard_check' === $match['_route'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        $match = $this->router->match($request->getPathInfo());
        $this->authSource = $this->authSourceRegistry->getAuthSource($match['authSource']);

        return $this->authSource->getCredentials();
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->authSource && $this->authSource->isAuthenticated();
    }

    /**
     * Helper to save the authentication exception into the session
     *
     * @param Request $request
     * @param AuthenticationException $exception
     */
    protected function saveAuthenticationErrorToSession(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
    }

    /**
     * Returns the URL (if any) the user visited that forced them to login.
     *
     * @param Request $request
     * @param $providerKey
     *
     * @return string|null
     */
    protected function getTargetPath(Request $request, $providerKey)
    {
        return $request->getSession()->get('_security.'.$providerKey.'.target_path');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return true;
    }
}
