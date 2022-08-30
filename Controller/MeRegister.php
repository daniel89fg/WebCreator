<?php
/**
 * This file is part of WebCreator plugin for FacturaScripts.
 * Copyright (C) 2022 Carlos Garcia Gomez <carlos@facturascripts.com>
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
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Dinamic\Model\CodeModel;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Controller\Me;
use voku\helper\EmailCheck;

/**
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class MeRegister extends Me
{

    const REGISTER_TEMPLATE = 'WebCreator/Public/Register';

    /**
     * @var string
     */
    public $countryContact;

    /**
     * @var string
     */
    public $nameContact;

    /**
     * @var string
     */
    public $phoneContact;

    public function getSelectValues($table, $code, $description, $empty = false): array
    {
        $values = $empty ? ['' => '------'] : [];
        foreach (CodeModel::all($table, $code, $description, $empty) as $row) {
            $values[$row->code] = $row->description;
        }
        return $values;
    }

    protected function createViews()
    {
        if (ToolBox::appSettings()->get('webcreator', 'registeravailable', true) === false) {
            $this->redirect('/');
            return;
        }

        if (empty($this->contact)) {
            $this->setTemplate(self::REGISTER_TEMPLATE);
            $this->title = $this->toolBox()->i18n()->trans('register-me');
            return;
        }

        $this->redirect($this->pageComposer->getPagesDefault()['accountpage']);
    }

    /**
     * @param string $action
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'register':
                return $this->registerAction();
        }

        return parent::execPreviousAction($action);
    }

    protected function registerAction(): bool
    {
        $newContact = new Contacto();
        $this->nameContact = $this->request->request->get('name', '');
        $this->emailContact = strtolower(trim($this->request->request->get('email', '')));
        $newPasswd = $this->request->request->get('newpasswd', '');
        $newPasswd2 = $this->request->request->get('newpasswd2', '');
        $this->phoneContact = $this->request->request->get('telefono1', null);
        $this->countryContact = $this->request->request->get('codpais', null);
        $okprivacy = (bool)$this->request->request->get('okprivacy', '0');

        if (false === $this->validateFormToken()) {
            return true;
        } elseif (empty($this->emailContact)) {
            $this->toolBox()->i18nLog()->warning('email-missing');
            return true;
        } elseif (false === EmailCheck::isValid($this->emailContact, true, true, true, true)) {
            $this->toolBox()->i18nLog()->warning('not-valid-email', ['%email%' => $this->emailContact]);
            $this->setIPWarning();
            return true;
        } elseif ($newContact->loadFromCode('', [new DataBaseWhere('email', $this->emailContact)])) {
            $this->toolBox()->i18nLog()->warning('email-contact-already-used', ['%email%' => $this->emailContact]);
            $this->setIPWarning();
            return true;
        } elseif (empty($newPasswd) || empty($newPasswd2)) {
            $this->toolBox()->i18nLog()->warning('new-password-missing');
            return true;
        } elseif (false === $okprivacy) {
            $this->toolBox()->i18nLog()->warning('you-must-accept-privacy-policy');
            return true;
        }

        $newContact->aceptaprivacidad = $okprivacy;
        $newContact->email = $this->emailContact;
        $newContact->newPassword = $newPasswd;
        $newContact->newPassword2 = $newPasswd2;
        $newContact->nombre = $this->nameContact;
        $newContact->telefono1 = $this->phoneContact;
        $newContact->codpais = $this->countryContact;
        if (false === $newContact->save()) {
            $this->toolBox()->i18nLog()->warning('record-save-error');
            return true;
        }

        $sendmail = $this->sendEmailConfirmation($newContact);

        $this->redirect($this->pageComposer->getPagesDefault()['loginpage'] . '?action=register-ok&sendmail=' . $sendmail . '&email=' . $this->emailContact);
        return true;
    }
}