<?php
/*
 * This file is part of the SSPGuardBundle.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sgomez\Bundle\SSPGuardBundle;


use Sgomez\Bundle\SSPGuardBundle\DependencyInjection\SSPGuardExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SSPGuardBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            return new SSPGuardExtension();
        }

        return $this->extension;
    }
}