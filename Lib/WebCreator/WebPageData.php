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

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\WebPage;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;
use FacturaScripts\Core\Base\ExtensionsTrait;
use FacturaScripts\Dinamic\Model\Redirect;

/**
 * Description of PortalController class
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WebPageData
{
    use ExtensionsTrait;

    public $uri;
    public $contact;
    public $request;

    public function __construct($uri, $contact, $request)
    {
        $this->uri = $uri;
        $this->contact = $contact;
        $this->request = $request;
        $this->pipe('constructAfter', $this->uri, $this->contact, $this->request);
    }

    protected function getDefaultPage(WebPage $webPage): ?WebPage
    {
        $this->pipe('getDefaultPageBefore');

        if ($this->uri === '/' || $this->uri === '/index.php') {
            $code = $this->toolBox()->appSettings()->get('webcreator', 'homepage');
            if ($webPage->loadFromCode($code)) {
                $this->pipe('getDefaultPageAfter');
                return $webPage;
            }
        }

        $this->pipe('getDefaultPageAfter');

        return null;
    }

    protected function getOtherPage(WebPage $webPage): ?WebPage
    {
        $this->pipe('getOtherPageBefore');

        foreach ($webPage->all([new DataBaseWhere('permalink', '*', 'LIKE')], [], 0, 0) as $wpage) {
            if (0 === \strncmp($this->uri, $wpage->permalink, \strlen($wpage->permalink) - 1)) {
                $this->pipe('getOtherPageAfter');
                return $wpage;
            }
        }

        $this->pipe('getOtherPageAfter');

        return null;
    }

    protected function getPerfectPage(WebPage $webPage): ?WebPage
    {
        $resultBefore = $this->pipe('getPerfectPageBefore');
        if (false === is_null($resultBefore)) {
            return $resultBefore;
        }

        if ($webPage->loadFromCode('', [new DataBaseWhere('permalink', $this->uri)])) {
            $this->pipe('getPerfectPageAfter');
            return $webPage;
        }

        $resultAfter = $this->pipe('getPerfectPageAfter');
        if (false === is_null($resultAfter)) {
            return $resultAfter;
        }

        return null;
    }

    public function getWebPage(): WebPage
    {
        $this->pipe('getWebPageBefore');

        $webPage = new WebPage();

        /// show default page?
        $result = $this->getDefaultPage($webPage);

        /// perfect match?
        if (is_null($result)) {
            $result = $this->getPerfectPage($webPage);
        }

        /// match with pages with * in permalink?
        if (is_null($result)) {
            $result = $this->getOtherPage($webPage);
        }

        /// if no page found, then we use this page with noindex activated.
        if (is_null($result)) {
            $webPage->noindex = true;
            $result = $webPage;
        }

        $result = $this->setLang($result);

        $this->pipe('getWebPageAfter', $result);

        return $result;
    }

    protected function setLang(WebPage $webPage): WebPage
    {
        $resultBefore = $this->pipe('setLangBefore', $webPage);
        if (false === is_null($resultBefore)) {
            return $resultBefore;
        }

        $webPage->filelang = $this->toolbox()->appSettings()->get('webcreator', 'langcode');
        $webPage->weblang = str_replace('_', '-', $webPage->filelang);

        if (false === isset($_COOKIE['weblang'])) {
            $expire = \time() + \FS_COOKIES_EXPIRE;
            setcookie('weblang', $webPage->weblang, $expire, FS_ROUTE);
        }

        //$webPage->canonicalUrl = $webPage->url('public');

        $this->pipe('setLangAfter');

        return $webPage;
    }

    private function toolBox(): ToolBox
    {
        return new ToolBox();
    }
}