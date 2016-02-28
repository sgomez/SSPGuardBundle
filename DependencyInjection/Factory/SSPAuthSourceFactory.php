<?php
/*
 * This file is part of the SSPGuardBundle.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sgomez\Bundle\SSPGuardBundle\DependencyInjection\Factory;


use Sgomez\Bundle\SSPGuardBundle\SimpleSAMLphp\SSPAuthSource;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SSPAuthSourceFactory
{
    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * AuthSourceFactory constructor.
     */
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function createAuthSource($authSource, $options)
    {
        $redirectUri = $this->generator
            ->generate('ssp_guard_check', ['authSource' => $authSource], UrlGeneratorInterface::ABSOLUTE_URL)
        ;

        $options['redirect_uri'] = $redirectUri;

        return new SSPAuthSource($authSource, $options);
    }
}