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


use Symfony\Component\DependencyInjection\ContainerInterface;

class AuthSourceRegistry
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var array
     */
    private $authSources;

    /**
     * AuthSourceRegistry constructor.
     *
     * @param ContainerInterface $container
     * @param array $authSources
     */
    public function __construct(ContainerInterface $container, array $authSources)
    {
        $this->container = $container;
        $this->authSources = $authSources;
    }

    /**
     * Get SSPAuthSource.
     *
     * @param $key
     *
     * @return SSPAuthSource
     */
    public function getAuthSource($key)
    {
        if (!isset($this->authSources[$key])) {
            throw new \InvalidArgumentException(sprintf(
                'There is no AuthSource called "%s". Available are: %s',
                $key,
                implode(', ', array_keys($this->authSources))
            ));
        }

        return $this->container->get($this->authSources[$key]);
    }

    /**
     * Get all auth source keys.
     *
     * @return array
     */
    public function getAuthSources()
    {
        return array_keys($this->authSources);
    }
}