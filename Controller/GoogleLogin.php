<?php
/**
 * This file is part of Portal plugin for FacturaScripts.
 * Copyright (C) 2020-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Plugins\WebCreator\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Controller\Me;
use FacturaScripts\Dinamic\Model\Contacto;
use Google_Client;
use Exception;

/**
 * Description of HybridLogin
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class GoogleLogin extends Me
{
    protected $returnUrl;

    /**
     * Execute the public part of the controller.
     *
     * @param Response $response
     */
    public function publicCore(&$response)
    {
        parent::publicCore($response);

        $this->returnUrl = $this->request->get('return', 'Me');
        $clientID = $this->toolBox()->appSettings()->get('webcreator', 'googleappid');

        $csrf_token_cookie = $this->request->cookies->get('g_csrf_token', '');
        $csrf_token_body = $this->request->get('g_csrf_token', '');

        if (empty($csrf_token_cookie)) {
            $this->toolBox()->i18nLog()->warning('no-csrf-cookie');
            $this->returnAfterLogin();
        } else if (empty($csrf_token_body)) {
            $this->toolBox()->i18nLog()->warning('no-csrf-post');
            $this->returnAfterLogin();
        } else if ($csrf_token_cookie != $csrf_token_body) {
            $this->toolBox()->i18nLog()->warning('failed-verify-csrf');
            $this->returnAfterLogin();
        }

        try {
            $client = new Google_Client(['client_id' => $clientID]);
            $payload = $client->verifyIdToken($this->request->get('credential'));
            if ($payload) {
                $this->checkContact($payload);
            }
        } catch (Exception $exp) {
            $this->toolBox()->i18nLog()->error($exp->getLine() . ' -> ' . $exp->getMessage());
        }

        $this->returnAfterLogin();
    }

    /**
     * Check contact data and update if needed.
     */
    protected function checkContact(array $userProfile)
    {
        $contact = new Contacto();
        $where = [new DataBaseWhere('email', $userProfile['email'])];
        if (!$contact->loadFromCode('', $where)) {
            $contact->email = $userProfile['email'];
            $contact->nombre = $userProfile['given_name'];
            $contact->apellidos = $userProfile['family_name'];
        } elseif (!$contact->habilitado) {
            $this->toolBox()->i18nLog()->warning('email-disabled', ['%email%' => $contact['email']]);
            $this->setIPWarning();
            $this->returnAfterLogin();
        }

        if ($contact->save()) {
            $this->contact = $contact;
            // add the contact to the token generation seed
            $this->multiRequestProtection->addSeed($this->contact->email);
            $this->saveCookies();
            $this->returnAfterLogin();
        }
    }

    protected function returnAfterLogin()
    {
        $this->redirect($this->returnUrl);
    }
}