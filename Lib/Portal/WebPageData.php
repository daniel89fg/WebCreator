<?php
/**
 * This file is part of WebCreator plugin for FacturaScripts.
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
namespace FacturaScripts\Plugins\WebCreator\Lib\Portal;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\WebPage;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;
use FacturaScripts\Core\Base\ExtensionsTrait;

/**
 * Description of PortalController class
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Athos Online <info@athosonline.com>
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

    private function toolBox()
    {
        return new ToolBox();
    }

    public function getWebPage()
    {
        $this->pipe('getWebPageBefore');

        $webPage = new WebPage();
        $result = null;
        
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

    protected function getDefaultPage($webPage)
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

    protected function getPerfectPage($webPage)
    {
        $this->pipe('getPerfectPageBefore');

        if ($webPage->loadFromCode('', [new DataBaseWhere('permalink', $this->uri)])) {
            $this->pipe('getPerfectPageAfter');
            return $webPage;
        }

        $this->pipe('getPerfectPageAfter');

        return null;
    }

    protected function getOtherPage($webPage)
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

    protected function setLang($webPage)
    {
        $this->pipe('setLangBefore');

        $webPage->filelang = $this->toolbox()->appSettings()->get('webcreator', 'langcode');
        $webPage->weblang = str_replace('_', '-', $webPage->filelang);
        
        if (!isset($_COOKIE['weblang'])) {
            $expire = \time() + \FS_COOKIES_EXPIRE;
            setcookie('weblang', $webPage->weblang, $expire, FS_ROUTE);
        }
        
        $webPage->canonicalUrl = $webPage->url('public');

        $this->pipe('setLangAfter');

        return $webPage;
    }
}