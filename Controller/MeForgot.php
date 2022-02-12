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
use FacturaScripts\Dinamic\Lib\Email\ButtonBlock;
use FacturaScripts\Dinamic\Lib\Email\NewMail;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Controller\Me;

/**
 * Description of MeForgot
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class MeForgot extends Me
{
    const FORGOT_TEMPLATE = 'WebCreator/Public/Forgot';

    protected function createViews()
    {
        if (empty($this->contact)) {
            $this->setTemplate(self::FORGOT_TEMPLATE);
            $this->title = $this->toolBox()->i18n()->trans('forgot-password');
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
            case 'forgot':
                return $this->forgotAction();
        }

        return parent::execPreviousAction($action);
    }

    /**
     * @return bool
     */
    protected function forgotAction(): bool
    {
        $contact = new Contacto();
        $this->emailContact = strtolower(trim($this->request->request->get('email', '')));

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
        }

        $this->sendRecoveryMail($contact);
        return true;
    }

    /**
     * @param Contacto $contact
     *
     * @return bool
     */
    protected function sendRecoveryMail($contact): bool
    {
        $i18n = $this->toolBox()->i18n();
        $link = $this->toolBox()->appSettings()->get('webcreator', 'siteurl') . '/Me?action=recover'
            . '&email=' . rawurlencode($contact->email)
            . '&key=' . $this->getActivationCode($contact);

        $mail = new NewMail();
        $mail->fromName = $this->toolBox()->appSettings()->get('portal', 'title');
        $mail->addAddress($contact->email);
        $mail->title = $i18n->trans('recover-your-account-name', ['%name%' => $contact->nombre]);
        $mail->text = $i18n->trans('recover-your-account-body');
        $mail->addMainBlock(new ButtonBlock($i18n->trans('recover-your-account'), $link));
        if ($mail->send()) {
            $this->toolBox()->i18nLog()->notice('recover-email-send-ok');
            return true;
        }

        $this->toolBox()->i18nLog()->critical('send-mail-error');
        return false;
    }
}