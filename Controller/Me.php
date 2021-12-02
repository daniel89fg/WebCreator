<?php
/**
 * This file is part of WebCreator plugin for FacturaScripts.
 * Copyright (C) 2020 Carlos Garcia Gomez <carlos@facturascripts.com>
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
use FacturaScripts\Dinamic\Lib\Portal\PortalPanelController;
use FacturaScripts\Dinamic\Model\WebPage;
use voku\helper\EmailCheck;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Description of Me
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Athos Online <info@athosonline.com>
 */
class Me extends PortalPanelController
{

    const ACCOUNT_TEMPLATE = 'Web/Private/MeAccount';
    const LOGIN_TEMPLATE = 'Web/Public/Me';
    const REGISTER_TEMPLATE = 'Web/Public/Me';

    /**
     *
     * @var WebPage
     */
    //public $cookiesPage;

    /**
     *
     * @var WebPage
     */
    public $privacyPage;

    /**
     * 
     * @return bool
     */
    protected function activateAction()
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
            $this->setTemplate(self::REGISTER_TEMPLATE);
            $this->title = $this->toolBox()->i18n()->trans('register-me');
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
        $this->addHtmlView($viewName, 'Web/Private/Me', 'Contacto', 'detail', 'fas fa-user-circle');
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewsBudgets(string $viewName = 'CardPresupuestoCliente')
    {
        $this->addCardListView($viewName, 'PresupuestoCliente', 'estimations', 'fas fa-copy');
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
        $this->addCardListView($viewName, 'PedidoCliente', 'orders', 'fas fa-shopping-cart');
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
        $this->addCardListView($viewName, 'FacturaCliente', 'invoices', 'fas fa-file-invoice');
        $this->views[$viewName]->addOrderBy(['fecha', 'hora'], 'date', 2);
        $this->views[$viewName]->addOrderBy(['total'], 'total');
        $this->views[$viewName]->addSearchFields(['codigo', 'direccion', 'nombrecliente', 'observaciones']);
        $this->disableButtons($viewName);
    }

    /**
     * 
     * @param string $viewName
     */
    private function disableButtons(string $viewName)
    {
        $this->setSettings($viewName, 'btnDelete', false);
        $this->setSettings($viewName, 'btnNew', false);
    }

    /**
     * 
     * @return bool
     */
    protected function deleteAction()
    {
        return true;
    }

    /**
     * 
     * @return bool
     */
    protected function deleteProfileAction()
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
    protected function editAction()
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

            case 'login':
                return $this->loginAction();

            case 'logout':
                return $this->logoutAction();

            case 'recover':
                return $this->recoverAction();

            case 'register':
                return $this->registerAction();

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
    protected function getActivationCode($contact): string
    {
        return \sha1($contact->idcontacto . $contact->password);
    }

    /**
     * 
     * @return bool
     */
    protected function insertAction()
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
    protected function loginAction()
    {
        $this->setTemplate(self::LOGIN_TEMPLATE);

        $contact = new Contacto();
        $email = \strtolower(\trim($this->request->request->get('email', '')));
        $forgot = (bool) $this->request->request->get('forgot', '0');
        $passwd = $this->request->request->get('passwd', '');

        if (false === $contact->loadFromCode('', [new DataBaseWhere('email', $email)])) {
            $this->toolBox()->i18nLog()->warning('email-not-registered', ['%email%' => $email]);
            $this->setIPWarning();
            return true;
        } elseif (false === $contact->habilitado) {
            $this->toolBox()->i18nLog()->warning('email-disabled', ['%email%' => $email]);
            $this->setIPWarning();
            return true;
        } elseif (false === $contact->verificado) {
            $this->toolBox()->i18nLog()->warning('email-not-verified', ['%email%' => $email]);
            $this->sendEmailConfirmation($contact);
            return true;
        } elseif ($forgot) {
            $this->sendRecoveryMail($contact);
            return true;
        } elseif (false === $contact->verifyPassword($passwd)) {
            $this->toolBox()->i18nLog()->warning('login-password-fail');
            $this->setIPWarning();
            return true;
        }

        if (is_null($contact->codicu)) {
            $contact->codicu = $_COOKIE['weblang'];
            $contact->save();
        }

        $this->contact = $contact;
        $this->saveCookies();
        $this->createViewsAccount();
        return true;
    }

    /**
     * 
     * @return bool
     */
    protected function logoutAction()
    {
        $this->response->headers->clearCookie('fsIdcontacto');
        $this->response->headers->clearCookie('fsLogkey');
        $this->contact = null;
        $this->setTemplate(self::LOGIN_TEMPLATE);
        return true;
    }

    /**
     * 
     * @return bool
     */
    protected function recoverAction()
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

    /**
     * 
     * @return bool
     */
    protected function registerAction()
    {
        $newContact = new Contacto();
        $email = \strtolower(\trim($this->request->request->get('email', '')));
        $newPasswd = $this->request->request->get('newpasswd', '');
        $newPasswd2 = $this->request->request->get('newpasswd2', '');
        $okprivacy = (bool) $this->request->request->get('okprivacy', '0');
        
        if (empty($email)) {
            $this->toolBox()->i18nLog()->warning('email-missing');
            return true;
        } elseif (false === EmailCheck::isValid($email, true, true, true, true)) {
            $this->toolBox()->i18nLog()->warning('not-valid-email', ['%email%' => $email]);
            $this->setIPWarning();
            return true;
        } elseif ($newContact->loadFromCode('', [new DataBaseWhere('email', $email)])) {
            $this->toolBox()->i18nLog()->warning('email-contact-already-used', ['%email%' => $email]);
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
        $newContact->email = $email;
        $newContact->newPassword = $newPasswd;
        $newContact->newPassword2 = $newPasswd2;
        $newContact->nombre = $this->request->request->get('name', '');
        $newContact->weblang = $_COOKIE['weblang'];
        if (false === $newContact->save()) {
            $this->toolBox()->i18nLog()->warning('record-save-error');
            return true;
        }
        
        $newContact->newPassword = $newContact->newPassword2 = null;
        $this->sendEmailConfirmation($newContact);
        $this->toolBox()->i18nLog()->notice('record-updated-correctly');

        $this->contact = $newContact;
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
    protected function sendEmailConfirmation($contact)
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

    /**
     * 
     * @param Contacto $contact
     *
     * @return bool
     */
    protected function sendRecoveryMail($contact)
    {
        $i18n = $this->toolBox()->i18n();
        $link = $this->toolBox()->appSettings()->get('webcreator', 'siteurl') . '/Me?action=recover'
            . '&email=' . \rawurlencode($contact->email)
            . '&key=' . $this->getActivationCode($contact);

        $mail = new NewMail();
        $mail->fromName = $this->toolBox()->appSettings()->get('webcreator', 'title');
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

    protected function setIPWarning()
    {
        $ipFilter = $this->toolBox()->ipFilter();
        $ipFilter->setAttempt($ipFilter->getClientIp());
    }
}