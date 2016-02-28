<?php
/*
 * This file is part of the SSPGuardBundle.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sgomez\Bundle\SSPGuardBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ConnectController extends Controller
{
    public function connectAction(Request $request, $authSource)
    {
        $user = $this->getUser();
        if ($user instanceof UserInterface) {
            if ($targetPath = $request->getSession()->get('_security.target_path')) {
                return new RedirectResponse($targetPath);
            }

            return $this->redirectToRoute('homepage');
        }

        $authSourceRegistry = $this->get('ssp.guard.registry');
        $url = $authSourceRegistry->getAuthSource($authSource)->getLoginUrl();

        return $this->redirect($url);
    }

    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}