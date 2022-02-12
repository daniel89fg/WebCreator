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
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Controller\Me;

/**
 * Description of MeLogin
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class MeLogin extends Me
{
    const LOGIN_TEMPLATE = 'WebCreator/Public/Login';

    protected function createViews()
    {
        if (empty($this->contact)) {
            $this->setTemplate(self::LOGIN_TEMPLATE);
            $this->title = $this->toolBox()->i18n()->trans('login');
            return;
        }

        $this->redirect('Me');
    }

    /**
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'login':
                return $this->loginAction();
        }

        return parent::execPreviousAction($action);
    }

    /**
     * @return bool
     */
    protected function loginAction(): bool
    {
        $contact = new Contacto();
        $this->emailContact = strtolower(trim($this->request->request->get('email', '')));
        $passwd = $this->request->request->get('passwd', '');

        if (false === $this->validateFormToken()) {
            return true;
        } elseif (false === $contact->loadFromCode('', [new DataBaseWhere('email', $this->emailContact)])) {
            $this->toolBox()->i18nLog()->warning('email-not-registered', ['%email%' => $this->emailContact]);
            $this->setIPWarning();
            return true;
        } elseif (false === $contact->habilitado) {
            $this->toolBox()->i18nLog()->warning('email-disabled', ['%email%' => $this->emailContact]);
            $this->setIPWarning();
            return true;
        } elseif (false === $contact->verifyPassword($passwd)) {
            $this->toolBox()->i18nLog()->warning('login-password-fail');
            $this->setIPWarning();
            return true;
        }

        $this->contact = $contact;
        // add the contact to the token generation seed
        $this->multiRequestProtection->addSeed($this->contact->email);
        $this->saveCookies();
        $this->createViewsAccount();
        return true;
    }
}