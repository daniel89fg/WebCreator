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

namespace FacturaScripts\Plugins\WebCreator;

require_once __DIR__ . '/vendor/autoload.php';

use FacturaScripts\Core\App\AppRouter;
use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;
use FacturaScripts\Dinamic\Lib\WebCreator\UpdateRoutes;
use FacturaScripts\Dinamic\Model\Empresa;
use FacturaScripts\Dinamic\Model\WebPage;

/**
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class Init extends InitClass
{

    public function init()
    {
        Shortcode::addCode('webBlock');
        Shortcode::addCode('webLogo');
        Shortcode::addCode('webAsset');
        Shortcode::addCode('webMenu');

        $translateClass = '\\FacturaScripts\\Dinamic\\Model\\WebTranslate';
        if (class_exists($translateClass)) {
            define('WEBMULTILANGUAGE', TRUE);
        } else {
            define('WEBMULTILANGUAGE', FALSE);
        }

        $this->loadExtension(new Extension\Controller\ListAttachedFile());
    }

    public function update()
    {
        new WebPage();
        $this->configure();
    }

    public function uninstall()
    {
        $router = new AppRouter();
        $router->setRoute('/', $this->toolBox()->appSettings()->get('default', 'homepage'));
    }

    protected function configure()
    {
        $appSettings = $this->toolBox()->appSettings();
        if (empty($appSettings->get('webcreator', 'title'))) {
            $empresa = new Empresa();
            $empresa->loadFromCode($appSettings->get('default', 'idempresa'));
            $appSettings->set('webcreator', 'title', $empresa->nombrecorto);
        }

        if (empty($appSettings->get('webcreator', 'siteurl'))) {
            $appSettings->set('webcreator', 'siteurl', $this->getSiteUrl());
        }

        if (empty($appSettings->get('webcreator', 'langcode'))) {
            $appSettings->set('webcreator', 'langcode', FS_LANG);
        }

        if (empty($appSettings->get('webcreator', 'headerdefault'))) {
            $appSettings->set('webcreator', 'headerdefault', 1);
        }

        if (empty($appSettings->get('webcreator', 'footerdefault'))) {
            $appSettings->set('webcreator', 'footerdefault', 1);
        }

        if (empty($appSettings->get('webcreator', 'sidebardefault'))) {
            $appSettings->set('webcreator', 'sidebardefault', 1);
        }

        if (empty($appSettings->get('webcreator', 'homepage'))) {
            $appSettings->set('webcreator', 'homepage', 1);
            $appSettings->set('default', 'homepage', 'PortalHome');
        }

        if (empty($appSettings->get('webcreator', 'cookiespage'))) {
            $appSettings->set('webcreator', 'cookiespage', 2);
        }

        if (empty($appSettings->get('webcreator', 'privacypage'))) {
            $appSettings->set('webcreator', 'privacypage', 3);
        }

        if (empty($appSettings->get('webcreator', 'sitewidth'))) {
            $appSettings->set('webcreator', 'sitewidth', 'container');
        }

        if (empty($appSettings->get('webcreator', 'sidebarposition'))) {
            $appSettings->set('webcreator', 'sidebarposition', 1);
        }

        if (empty($appSettings->get('webcreator', 'registeravailable'))) {
            $appSettings->set('webcreator', 'registeravailable', 1);
        }

        if (empty($appSettings->get('webcreator', 'loginavailable'))) {
            $appSettings->set('webcreator', 'loginavailable', 1);
        }

        if (empty($appSettings->get('webcreator', 'pagetitle'))) {
            $appSettings->set('webcreator', 'pagetitle', 1);
        }

        if (empty($appSettings->get('webcreator', 'titlestyle'))) {
            $appSettings->set('webcreator', 'titlestyle', 'left');
        }

        if (empty($appSettings->get('webcreator', 'titletag'))) {
            $appSettings->set('webcreator', 'titletag', 'h1');
        }

        if (empty($appSettings->get('webcreator', 'titlebackgroundcolor'))) {
            $appSettings->set('webcreator', 'titlebackgroundcolor', '#376FB7');
        }

        if (empty($appSettings->get('webcreator', 'titlebackgroundopacity'))) {
            $appSettings->set('webcreator', 'titlebackgroundopacity', 0.1);
        }

        if (empty($appSettings->get('webcreator', 'titlewidth'))) {
            $appSettings->set('webcreator', 'titlewidth', 'container');
        }

        if (empty($appSettings->get('webcreator', 'titlebreadcrumbs'))) {
            $appSettings->set('webcreator', 'titlebreadcrumbs', 1);
        }

        if (empty($appSettings->get('webcreator', 'titlebreadcrumbsseparate'))) {
            $appSettings->set('webcreator', 'titlebreadcrumbsseparate', '>');
        }

        if (empty($appSettings->get('webcreator', 'fontdefault'))) {
            $appSettings->set('webcreator', 'fontdefault', 864);
            $appSettings->set('webcreator', 'fontlink', 864);
            $appSettings->set('webcreator', 'fontp', 864);
            $appSettings->set('webcreator', 'fonth1', 864);
            $appSettings->set('webcreator', 'fonth2', 864);
            $appSettings->set('webcreator', 'fonth3', 864);
            $appSettings->set('webcreator', 'fonth4', 864);
            $appSettings->set('webcreator', 'fonth5', 864);
            $appSettings->set('webcreator', 'fonth6', 864);
        }

        if (empty($appSettings->get('webcreator', 'fontdefaultweight'))) {
            $appSettings->set('webcreator', 'fontdefaultweight', 3034);
            $appSettings->set('webcreator', 'fontlinkweight', 3034);
            $appSettings->set('webcreator', 'fontpweight', 3034);
            $appSettings->set('webcreator', 'fonth1weight', 3034);
            $appSettings->set('webcreator', 'fonth2weight', 3034);
            $appSettings->set('webcreator', 'fonth3weight', 3034);
            $appSettings->set('webcreator', 'fonth4weight', 3034);
            $appSettings->set('webcreator', 'fonth5weight', 3034);
            $appSettings->set('webcreator', 'fonth6weight', 3034);
        }

        if (empty($appSettings->get('webcreator', 'fontlinkcolor'))) {
            $appSettings->set('webcreator', 'fontlinkcolor', '#212529');
            $appSettings->set('webcreator', 'fontlinkcolorhover', '#007bff');
            $appSettings->set('webcreator', 'fontpcolor', '#212529');
            $appSettings->set('webcreator', 'fonth1color', '#212529');
            $appSettings->set('webcreator', 'fonth2color', '#212529');
            $appSettings->set('webcreator', 'fonth3color', '#212529');
            $appSettings->set('webcreator', 'fonth4color', '#212529');
            $appSettings->set('webcreator', 'fonth5color', '#212529');
            $appSettings->set('webcreator', 'fonth6color', '#212529');
        }

        $appSettings->save();

        $routes = new UpdateRoutes();
        $routes->setRoutes();
    }

    /**
     *
     * @return string
     */
    protected function getSiteUrl(): string
    {
        $url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $url .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return \substr($url, 0, \strrpos($url, '/'));
    }
}