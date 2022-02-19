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
use FacturaScripts\Dinamic\Lib\ExtendedController\BaseView;
use FacturaScripts\Dinamic\Lib\Email\ButtonBlock;
use FacturaScripts\Dinamic\Lib\Email\NewMail;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Lib\WebCreator\PortalPanelController;
use FacturaScripts\Dinamic\Model\WebPage;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Description of Me
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class Me extends PortalPanelController
{
    const ACCOUNT_TEMPLATE = 'WebCreator/Private/MeAccount';

    /**
     * @var string
     */
    public $emailContact;

    /**
     * 
     * @return bool
     */
    protected function activateAction(): bool
    {
        $cod = $this->request->get('cod', '');
        $email = $this->request->get('email', '');
        if (empty($email) || empty($cod)) {
            return true;
        }

        $contact = new Contacto();
        $where = [new DataBaseWhere('email', \rawurldecode($email))];
        if (false === $contact->loadFromCode('', $where)) {
            $this->toolBox()->i18nLog()->warning('email-not-registered', ['%email%' => $email]);
            $this->setIPWarning();
            return true;
        }

        if ($cod !== $this->getActivationCode($contact)) {
            $this->toolBox()->i18nLog()->error('invalid-activation-code', ['%email%' => $email]);
            $this->setIPWarning();
            return true;
        }

        $contact->verificado = true;
        if ($contact->save()) {
            $this->toolBox()->i18nLog()->notice('record-updated-correctly');
            return true;
        }

        $this->toolBox()->i18nLog()->error('record-save-error');
        return true;
    }

    protected function createViews()
    {
        if (empty($this->contact)) {
            $this->redirect('MeLogin');
            return;
        }

        if ($this->contact->aceptaprivacidad === false) {
            $this->createViewsPrivacyContact();
            return;
        }

        $this->createViewsAccount();
        $this->createViewsBudgets();
        $this->createViewsOrders();
        $this->createViewsInvoices();
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewsAccount(string $viewName = 'Me')
    {
        $this->setTemplate(self::ACCOUNT_TEMPLATE);
        $this->title = $this->toolBox()->i18n()->trans('my-profile');
        $this->addHtmlView($viewName, 'WebCreator/Private/Me', 'Contacto', 'detail', 'fas fa-user-circle');
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewsBudgets(string $viewName = 'CardPresupuestoCliente')
    {
        $this->addCardListView($viewName, 'PresupuestoCliente', 'estimations', 'far fa-file-powerpoint');
        $this->views[$viewName]->addOrderBy(['fecha', 'hora'], 'date', 2);
        $this->views[$viewName]->addOrderBy(['total'], 'total');
        $this->views[$viewName]->addSearchFields(['codigo', 'direccion', 'nombrecliente', 'observaciones']);
        $this->disableButtons($viewName);
    }

    /**
     *
     * @param string $viewName
     */
    protected function createViewsInvoices(string $viewName = 'CardFacturaCliente')
    {
        $this->addCardListView($viewName, 'FacturaCliente', 'invoices', 'fas fa-file-invoice-dollar');
        $this->views[$viewName]->addOrderBy(['fecha', 'hora'], 'date', 2);
        $this->views[$viewName]->addOrderBy(['total'], 'total');
        $this->views[$viewName]->addSearchFields(['codigo', 'direccion', 'nombrecliente', 'observaciones']);
        $this->disableButtons($viewName);
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewsOrders(string $viewName = 'CardPedidoCliente')
    {
        $this->addCardListView($viewName, 'PedidoCliente', 'orders', 'fas fa-file-powerpoint');
        $this->views[$viewName]->addOrderBy(['fecha', 'hora'], 'date', 2);
        $this->views[$viewName]->addOrderBy(['total'], 'total');
        $this->views[$viewName]->addSearchFields(['codigo', 'direccion', 'nombrecliente', 'observaciones']);
        $this->disableButtons($viewName);
    }

    /**
     * @param string $viewName
     */
    protected function createViewsPrivacyContact(string $viewName = 'Me')
    {
        $this->setTemplate(self::ACCOUNT_TEMPLATE);
        $this->title = $this->toolBox()->i18n()->trans('my-profile');
        $this->addHtmlView($viewName, 'WebCreator/Private/Privacy', 'Contacto', 'privacy-policy', 'fas fa-check-double');
    }

    /**
     * 
     * @param string $viewName
     */
    protected function disableButtons(string $viewName)
    {
        $this->setSettings($viewName, 'btnDelete', false);
        $this->setSettings($viewName, 'btnNew', false);
    }

    /**
     * 
     * @return bool
     */
    protected function deleteAction(): bool
    {
        return true;
    }

    /**
     * 
     * @return bool
     */
    protected function deleteProfileAction(): bool
    {
        $secutiry = $this->request->request->get('security');
        if ($this->contact->exists() && 'DELETE' === $secutiry && $this->contact->delete()) {
            $this->toolBox()->i18nLog()->warning('contact-removed', ['%email%' => $this->contact->email]);
            $this->redirect($this->getClassName());
            return true;
        }

        $this->toolBox()->i18nLog()->error('record-deleted-error');
        return true;
    }

    /**
     * 
     * @return bool
     */
    protected function editAction(): bool
    {
        return true;
    }

    /**
     * 
     * @return bool
     */
    protected function editProfileAction()
    {
        if (empty($this->contact)) {
            $this->setIPWarning();
            return true;
        } elseif (false === $this->validateFormToken()) {
            return true;
        }

        $contact = new Contacto();
        $newEmail = $this->request->request->get('email', $this->contact->email);
        $contact->loadFromCode('', [new DataBaseWhere('email', $newEmail)]);
        if (is_null($contact->idcontacto) === false && $contact->idcontacto != $this->contact->idcontacto) {
            $this->toolBox()->i18nLog()->warning('email-contact-already-used', ['%email%' => $newEmail]);
            return true;
        } else {
            $this->contact->email = $newEmail;
        }

        $fields = [
            'nombre', 'apellidos', 'tipoidfiscal', 'cifnif', 'direccion', 'apartado',
            'codpostal', 'ciudad', 'provincia', 'codpais', 'newPassword', 'newPassword2'
        ];
        foreach ($fields as $field) {
            $this->contact->{$field} = $this->request->request->get($field);
        }

        if ($this->contact->save()) {
            $this->toolBox()->i18nLog()->notice('record-updated-correctly');
            return true;
        }

        $this->toolBox()->i18nLog()->warning('record-save-error');
        return true;
    }

    /**
     * 
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'activate':
                return $this->activateAction();

            case 'delete-profile':
                return $this->deleteProfileAction();

            case 'edit-profile':
                return $this->editProfileAction();

            case 'logout':
                return $this->logoutAction();

            case 'privacy':
                return $this->privacyAction();

            case 'recover':
                return $this->recoverAction();

            case 'send-verification':
                if ($this->contact) {
                    $this->sendEmailConfirmation($this->contact);
                }
                return true;

            default:
                return parent::execPreviousAction($action);
        }
    }

    /**
     * 
     * @param Contacto $contact
     *
     * @return string
     */
    protected function getActivationCode(Contacto $contact): string
    {
        return \sha1($contact->idcontacto . $contact->password);
    }

    /**
     * 
     * @return bool
     */
    protected function insertAction(): bool
    {
        return true;
    }

    /**
     * 
     * @param string   $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        $this->hasData = true;

        switch ($viewName) {
            case 'CardPedidoCliente':
            case 'CardPresupuestoCliente':
            case 'CardFacturaCliente':
                $this->setSettings($viewName, 'active', false);
                if (isset($this->contact->codcliente)) {
                    $where = [new DataBaseWhere('codcliente', $this->contact->codcliente)];
                    $view->loadData('', $where);
                    $this->setSettings($viewName, 'active', $view->model->count($where) > 0);
                }
                break;
        }
    }

    /**
     * 
     * @return bool
     */
    protected function logoutAction(): bool
    {
        $this->response->headers->clearCookie('fsIdcontacto');
        $this->response->headers->clearCookie('fsLogkey');
        $this->contact = null;
        $this->redirect('/');
        return true;
    }

    protected function privacyAction(): bool
    {
        $this->contact->aceptaprivacidad = 1;
        $this->contact->save();
        $this->createViewsAccount();
        return true;
    }

    /**
     * 
     * @return bool
     */
    protected function recoverAction(): bool
    {
        $key = $this->request->get('key', '');
        $email = $this->request->get('email', '');
        if (empty($email) || empty($key)) {
            return true;
        }

        $contact = new Contacto();
        $where = [new DataBaseWhere('email', \rawurldecode($email))];
        if (false === $contact->loadFromCode('', $where)) {
            $this->toolBox()->i18nLog()->warning('email-not-registered', ['%email%' => $email]);
            $this->setIPWarning();
            return true;
        }

        if ($key !== $this->getActivationCode($contact)) {
            $this->toolBox()->i18nLog()->error('invalid-recovery-code');
            $this->setIPWarning();
            return true;
        }

        $contact->verificado = true;
        $this->contact = $contact;
        $this->saveCookies();
        $this->createViewsAccount();
        return true;
    }

    protected function saveCookies()
    {
        $this->contact->newLogkey($this->toolBox()->ipFilter()->getClientIp());
        $this->contact->save();

        $expire = \time() + \FS_COOKIES_EXPIRE;
        $this->response->headers->setCookie(new Cookie('fsIdcontacto', $this->contact->idcontacto, $expire));
        $this->response->headers->setCookie(new Cookie('fsLogkey', $this->contact->logkey, $expire));
    }

    /**
     * 
     * @param Contacto $contact
     *
     * @return bool
     */
    protected function sendEmailConfirmation(Contacto $contact): bool
    {
        $i18n = $this->toolBox()->i18n();
        $link = $this->toolBox()->appSettings()->get('webcreator', 'siteurl') . '/Me?action=activate'
            . '&cod=' . $this->getActivationCode($contact)
            . '&email=' . \rawurlencode($contact->email);

        $mail = new NewMail();
        $mail->fromName = $this->toolBox()->appSettings()->get('webcreator', 'title');
        $mail->addAddress($contact->email);
        $mail->title = $i18n->trans('confirm-email-name', ['%name%' => $contact->nombre]);
        $mail->text = $i18n->trans('please-click-on-confirm-email');
        $mail->addMainBlock(new ButtonBlock($i18n->trans('confirm-email'), $link));

        if ($mail->send()) {
            $this->toolBox()->i18nLog()->notice('activation-email-sent');
            return true;
        }

        $this->toolBox()->i18nLog()->error('activation-email-sent-error');
        return false;
    }

    protected function setIPWarning()
    {
        $ipFilter = $this->toolBox()->ipFilter();
        $ipFilter->setAttempt($ipFilter->getClientIp());
    }
}