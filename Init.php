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
        $name = 'webcreator';
        $appSettings = $this->toolBox()->appSettings();

        $empresa = new Empresa();
        $empresa->loadFromCode($appSettings->get('default', 'idempresa'));

        $appSettings->get($name, 'title', $empresa->nombrecorto);
        $appSettings->get($name, 'siteurl', $this->getSiteUrl());
        $appSettings->get($name, 'langcode', FS_LANG);
        $appSettings->get($name, 'headerdefault', 1);
        $appSettings->get($name, 'titledefault', 1);
        $appSettings->get($name, 'footerdefault', 1);
        $appSettings->get($name, 'sidebardefault', 1);
        $appSettings->get($name, 'homepage', 1);
        $appSettings->get('default', 'homepage', 'PortalHome');
        $appSettings->get($name, 'cookiespage', 2);
        $appSettings->get($name, 'privacypage', 3);
        $appSettings->get($name, 'sitewidth', 'container');
        $appSettings->get($name, 'sidebarposition', 1);
        $appSettings->get($name, 'registeravailable', 1);
        $appSettings->get($name, 'accountpage', 6);
        $appSettings->get($name, 'loginpage', 7);
        $appSettings->get($name, 'registerpage', 8);
        $appSettings->get($name, 'forgotpage', 9);
        $appSettings->get($name, 'loginavailable', 1);
        $appSettings->get($name, 'fontdefault', 864);
        $appSettings->get($name, 'fontlink', 864);
        $appSettings->get($name, 'fontp', 864);
        $appSettings->get($name, 'fonth1', 864);
        $appSettings->get($name, 'fonth2', 864);
        $appSettings->get($name, 'fonth3', 864);
        $appSettings->get($name, 'fonth4', 864);
        $appSettings->get($name, 'fonth5', 864);
        $appSettings->get($name, 'fonth6', 864);
        $appSettings->get($name, 'fontdefaultweight', 3034);
        $appSettings->get($name, 'fontlinkweight', 3034);
        $appSettings->get($name, 'fontpweight', 3034);
        $appSettings->get($name, 'fonth1weight', 3034);
        $appSettings->get($name, 'fonth2weight', 3034);
        $appSettings->get($name, 'fonth3weight', 3034);
        $appSettings->get($name, 'fonth4weight', 3034);
        $appSettings->get($name, 'fonth5weight', 3034);
        $appSettings->get($name, 'fonth6weight', 3034);
        $appSettings->get($name, 'fontlinkcolor', '#212529');
        $appSettings->get($name, 'fontlinkcolorhover', '#007bff');
        $appSettings->get($name, 'fontpcolor', '#212529');
        $appSettings->get($name, 'fonth1color', '#212529');
        $appSettings->get($name, 'fonth2color', '#212529');
        $appSettings->get($name, 'fonth3color', '#212529');
        $appSettings->get($name, 'fonth4color', '#212529');
        $appSettings->get($name, 'fonth5color', '#212529');
        $appSettings->get($name, 'fonth6color', '#212529');
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