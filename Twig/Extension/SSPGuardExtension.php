<?php
/*
 * This file is part of the SSPGuardBundle.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sgomez\Bundle\SSPGuardBundle\Twig\Extension;


use Sgomez\Bundle\SSPGuardBundle\SimpleSAMLphp\AuthSourceRegistry;

class SSPGuardExtension extends \Twig_Extension
{
    /**
     * @var AuthSourceRegistry
     */
    private $registry;

    /**
     * SSPGuardExtension constructor.
     */
    public function __construct(AuthSourceRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('ssp_auth_sources', [$this, 'getAuthSources']),
            new \Twig_SimpleFunction('ssp_auth_source', [$this, 'getAuthSource']),
        ];
    }

    public function getAuthSources()
    {
        return $this->registry->getAuthSources();
    }

    public function getAuthSource($authSource)
    {
        return $this->registry->getAuthSource($authSource);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'ssp_guard';
    }
}