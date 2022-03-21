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

namespace FacturaScripts\Plugins\WebCreator\Lib\WebCreator;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Model\User;
use FacturaScripts\Dinamic\Model\WebPage;
use FacturaScripts\Dinamic\Lib\WebCreator\WebPageData;
use FacturaScripts\Dinamic\Lib\WebCreator\PortalAction;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of PortalController class
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class PortalController extends Controller
{
    const PUBLIC_UPDATE_ACTIVITY_PERIOD = 3600;
    const DEFAULT_TEMPLATE = 'WebCreator/Public/PortalTemplate';

    /**
     *
     * @var string
     */
    public $canonicalUrl;

    /**
     * The associated contact.
     *
     * @var Contacto
     */
    public $contact;

    /**
     * Page description.
     *
     * @var string
     */
    public $description;

    /**
     * The page composer.
     *
     * @var PageComposer
     */
    public $pageComposer;

    /**
     * The web page object.
     *
     * @var WebPage
     */
    public $webPage;

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'web';
        $pageData['showonmenu'] = false;
        return $pageData;
    }

    /**
     *
     * @param Response $response
     * @param User $user
     * @param ControllerPermissions $permissions
     */
    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        /// loads contact
        $contact = new Contacto();
        if (!empty($this->user->email)) {
            if (false === $contact->loadFromCode('', [new DataBaseWhere('email', $this->user->email)])) {
                $contact->email = $this->user->email;
                $contact->nombre = $this->user->nick;
                $contact->save();
            }

            $this->contact = $contact;
            if (\time() - \strtotime($this->contact->lastactivity) > self::PUBLIC_UPDATE_ACTIVITY_PERIOD) {
                $this->contact->lastactivity = \date(Contacto::DATETIME_STYLE);
                $this->contact->save();
            }
        }

        $this->commonCore();
    }

    /**
     *
     * @param Response $response
     */
    public function publicCore(&$response)
    {
        parent::publicCore($response);

        /// contact login
        $contact = new Contacto();
        $idcontacto = $this->request->cookies->get('fsIdcontacto', '');
        if (!empty($idcontacto) &&
            $contact->loadFromCode($idcontacto) &&
            $contact->habilitado &&
            $contact->verifyLogkey($this->request->cookies->get('fsLogkey'))) {
            $this->contact = $contact;
            if (\time() - \strtotime($this->contact->lastactivity) > self::PUBLIC_UPDATE_ACTIVITY_PERIOD) {
                $this->contact->lastactivity = \date(Contacto::DATETIME_STYLE);
                $this->contact->save();
            }
        }

        $this->commonCore();
    }

    protected function commonCore()
    {
        $pageData = new WebPageData($this->uri, $this->contact, $this->request);
        $this->pageComposer = new PageComposer();
        $this->webPage = $pageData->getWebPage();

        $this->setTemplate(static::DEFAULT_TEMPLATE);

        if ($this->webPage->permalink) {
            $this->canonicalUrl = $this->webPage->url('public');
        } else {
            $this->canonicalUrl = $this->url();
        }

        $this->description = $this->webPage->description;
        $this->title = $this->webPage->title;

        new PortalAction($this->uri, $this->contact, $this->request);
    }

    protected function error403()
    {
        $this->setTemplate('WebCreator/Error/PortalAccessDenied');
        $this->response->setStatusCode(Response::HTTP_FORBIDDEN);
        $this->webPage->noindex = true;
    }

    protected function error404()
    {
        $this->setTemplate('WebCreator/Error/Portal404');
        $this->response->setStatusCode(Response::HTTP_NOT_FOUND);

        $this->description = $this->toolBox()->i18n()->trans('page-not-found-p');
        $this->title = $this->toolBox()->i18n()->trans('page-not-found');
        $this->webPage->noindex = true;
    }

    /**
     * @return bool
     */
    protected function validateFormToken(): bool
    {
        // valid request?
        $token = $this->request->request->get('multireqtoken', '');
        if (empty($token) || false === $this->multiRequestProtection->validate($token)) {
            $this->toolBox()->i18nLog()->warning('invalid-request');
            return false;
        }

        // duplicated request?
        if ($this->multiRequestProtection->tokenExist($token)) {
            $this->toolBox()->i18nLog()->warning('duplicated-request');
            return false;
        }

        return true;
    }
}