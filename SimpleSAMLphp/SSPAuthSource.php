<?php
/*
 * This file is part of the SSPGuardBundle.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sgomez\Bundle\SSPGuardBundle\SimpleSAMLphp;


use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class SSPAuthSource
{
    private $redirectUri;
    /**
     * @var array
     */
    private $options;

    /**
     * SSPAuthSource constructor.
     *
     * @param $authSource
     * @param array $options
     */
    public function __construct($authSource, array $options = [])
    {
        $this->auth = new \SimpleSAML\Auth\Simple($authSource);
        $this->options = $options;
    }

    public function isAuthenticated()
    {
        return $this->auth->isAuthenticated();
    }

    public function getAttributes()
    {
        return $this->auth->getAttributes();
    }

    public function getCredentials()
    {
        $credentials = [];

        foreach ($this->auth->getAttributes() as $key => $value) {
            if (1 === count($value)) {
                $credentials[$key] = $value;
            } else {
                $credentials[$key] = $value[0];
            }
        }

        $credentials['username'] = $this->getUsername();

        return $credentials;
    }

    public function getLoginUrl()
    {
        return $this->auth->getLoginURL($this->getRedirectUri());
    }

    public function getUsername()
    {
        if ($this->isAuthenticated()) {
            if (array_key_exists($this->getUserId(), $this->getAttributes())) {
                $attributes = $this->getAttributes();

                return $attributes[$this->getUserId()][0];
            }

            throw new InvalidArgumentException(sprintf(
                'Your Identity Provider must return attribute "%s".',
                $this->getUserId()
            ));
        }
    }

    public function getRedirectUri()
    {
        return $this->options['redirect_uri'];
    }

    public function getTitle()
    {
        return $this->options['title'];
    }

    public function getUserId()
    {
        return $this->options['user_id'];
    }
}