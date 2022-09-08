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

use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Dinamic\Model\Settings;
use FacturaScripts\Dinamic\Model\AttachedFile;
use FacturaScripts\Dinamic\Model\WebPage;
use FacturaScripts\Dinamic\Model\WebHeader;
use FacturaScripts\Dinamic\Model\WebMenu;
use FacturaScripts\Dinamic\Model\WebFooter;
use FacturaScripts\Dinamic\Model\WebSidebar;
use FacturaScripts\Dinamic\Model\WebTitle;
use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;
use FacturaScripts\Dinamic\Model\WebFont;
use FacturaScripts\Dinamic\Model\WebFontWeight;
use FacturaScripts\Core\Base\ExtensionsTrait;

/**
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class PageComposer
{

    use ExtensionsTrait;
    use IncludeViewTrait;

    public function getAttachFile(int $idfile): string
    {
        $siteurl = $this->toolBox()->appSettings()->get('webcreator', 'siteurl');
        $file = new AttachedFile();
        $file->loadFromCode($idfile);
        return $siteurl . '/' . $file->url('download-permanent');
    }

    public function getBreadcrumbs(WebPage $page, string $separator): string
    {
        $this->pipe('getBreadcrumbsBefore');

        $breadcrumb = $page->title;
        $breadcrumb = $this->getPageParent($breadcrumb, $page, $separator);

        $this->pipe('getBreadcrumbsAfter');

        return $breadcrumb;
    }

    public function getCookie(string $name): string
    {
        return ($_COOKIE[$name]) ?? '';
    }

    public function getFont(int $id): string
    {
        $font = new WebFont();
        $font->loadFromCode($id);
        return $font->name;
    }

    public function getFooter(WebPage $webPage): WebFooter
    {
        $footer = new WebFooter();

        $this->pipe('getFooterBefore');

        if (is_null($webPage->idfooter) || $webPage->idfooter == -1) {
            $footer->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'footerdefault'));
        } else if ($webPage->idfooter > 0) {
            $footer->loadFromCode($webPage->idfooter);
        } else if ($webPage->idfooter == -2 && $webPage->pageparent) {
            $page = new WebPage();
            $page->loadFromCode($webPage->pageparent);
            return $this->getFooter($page);
        }

        $this->pipe('getFooterAfter');

        return $footer;
    }

    public function getGoogleFonts(): string
    {
        $link = 'https://fonts.googleapis.com/css?family=';
        $link .= $this->getFontDefault($link);
        $link .= $this->getFontLink($link);
        $link .= $this->getFontP($link);
        $link .= $this->getFontH1($link);
        $link .= $this->getFontH2($link);
        $link .= $this->getFontH3($link);
        $link .= $this->getFontH4($link);
        $link .= $this->getFontH5($link);
        $link .= $this->getFontH6($link);

        $link = substr($link, 0, -1);
        return '<link href="' . $link . '" rel="stylesheet">';
    }

    public function getHeader(WebPage $webPage): WebHeader
    {
        $header = new WebHeader();

        $this->pipe('getHeaderBefore');

        if (is_null($webPage->idheader) || $webPage->idheader == -1) {
            $header->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'headerdefault'));
        } else if ($webPage->idheader > 0) {
            $header->loadFromCode($webPage->idheader);
        } else if ($webPage->idheader == -2 && $webPage->pageparent) {
            $page = new WebPage();
            $page->loadFromCode($webPage->pageparent);
            return $this->getHeader($page);
        }

        $header->menu = $this->getMenu($header->id);
        $this->pipe('getHeaderAfter');

        return $header;
    }

    public function getMenu(?int $id): WebMenu
    {
        $menu = new WebMenu();
        $this->pipe('getMenuBefore');

        if ($id > 0) {
            $menu->loadFromCode($id);
            $menu->links = $menu->getLinks();
        }

        $this->pipe('getMenuAfter');

        return $menu;
    }

    public function getPageData(WebPage $webPage): array
    {
        $pageData = array(
            'settings' => $this->getWebSettings(),
            'header' => $this->getHeader($webPage),
            'sidebar' => $this->getSidebar($webPage),
            'page' => $webPage,
            'footer' => $this->getFooter($webPage),
            'title' => $this->getTitle($webPage),
        );

        return Shortcode::getPageShortcodes($pageData);
    }

    public function getPagesDefault(): array
    {
        $resultBefore = $this->pipe('getPagesDefaultBefore');
        if (false === empty($resultBefore)) {
            return $resultBefore;
        }

        $siteurl = $this->toolBox()->appSettings()->get('webcreator', 'siteurl');

        $homepage = new WebPage();
        $homepage->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'homepage'));
        $homepageUrl = $homepage->url('public');

        $cookiespage = new WebPage();
        $cookiespage->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'cookiespage'));
        $cookiespageUrl = $cookiespage->url('public');

        $privacypage = new WebPage();
        $privacypage->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'privacypage'));
        $privacypageUrl = $privacypage->url('public');

        $termspage = new WebPage();
        $termspage->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'termspage'));
        $termspageUrl = $termspage->url('public');

        $accountpage = new WebPage();
        if ($accountpage->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'accountpage'))) {
            $accountpageUrl = $accountpage->url('public');
        } else {
            $accountpageUrl = $siteurl . '/Me';
        }

        $loginpage = new WebPage();
        if ($loginpage->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'loginpage'))) {
            $loginpageUrl = $loginpage->url('public');
        } else {
            $loginpageUrl = $siteurl . '/MeLogin';
        }

        $registerpage = new WebPage();
        if ($registerpage->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'registerpage'))) {
            $registerpageUrl = $registerpage->url('public');
        } else {
            $registerpageUrl = $siteurl . '/MeRegister';
        }

        $forgotpage = new WebPage();
        if ($forgotpage->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'forgotpage'))) {
            $forgotpageUrl = $forgotpage->url('public');
        } else {
            $forgotpageUrl = $siteurl . '/MeForgot';
        }

        $pages = array(
            'homepage' => $homepageUrl,
            'cookiespage' => $cookiespageUrl,
            'privacypage' => $privacypageUrl,
            'termspage' => $termspageUrl,
            'accountpage' => $accountpageUrl,
            'loginpage' => $loginpageUrl,
            'registerpage' => $registerpageUrl,
            'forgotpage' => $forgotpageUrl
        );

        $resultAfter = $this->pipe('getPagesDefaultAfter');
        if (false === empty($resultAfter)) {
            return $resultAfter;
        }

        return $pages;
    }

    /*public function getShortcodes(string $content, WebPage $webpage)
    {
        return Shortcode::getShortcodes($content, $webpage);
    }*/

    public function getSidebar(WebPage $webPage): WebSidebar
    {
        $sidebar = new WebSidebar();

        $this->pipe('getSidebarBefore');

        if (is_null($webPage->id) || $webPage->id == -1) {
            $sidebar->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'sidebardefault'));
        } else if ($webPage->idsidebar > 0) {
            $sidebar->loadFromCode($webPage->idsidebar);
        } else if ($webPage->idsidebar == -2 && $webPage->pageparent) {
            $page = new WebPage();
            $page->loadFromCode($webPage->pageparent);
            return $this->getSidebar($page);
        }
        $sidebar->position = $webPage->sidebarposition;

        $this->pipe('getSidebarAfter');

        return $sidebar;
    }

    public function getTitle(WebPage $webPage): WebTitle
    {
        $title = new WebTitle();

        $this->pipe('getTitleBefore');

        if (is_null($webPage->idtitle) || $webPage->idtitle == -1) {
            $title->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'titledefault'));
        } else if ($webPage->idtitle > 0) {
            $title->loadFromCode($webPage->idtitle);
        } else if ($webPage->idtitle == -2 && $webPage->pageparent) {
            $page = new WebPage();
            $page->loadFromCode($webPage->pageparent);
            return $this->getTitle($page);
        }

        $this->pipe('getTitleAfter');

        return $title;
    }

    public function getWebSettings(): array
    {
        $webSettings = new Settings();
        return $webSettings->get('webcreator')->properties;
    }

    public function regexCSS(?string $content): ?string
    {
        $this->pipe('regexCSSBefore');

        if (!is_null($content)) {
            preg_match_all("/\<link(.*?)\>/", $content, $matches);
            $shorts = (count($matches) > 0) ? $matches : null;

            $styles = '';
            for ($x = 0; $x < count($shorts[1]); $x++) {
                $styles .= $shorts[0][$x];
                $content = str_replace($shorts[0][$x], '', $content);
            }

            $content = '<style>' . $content . '</style>';
            $content = $styles . $content;
        }

        $this->pipe('regexCSSAfter');

        return $content;
    }

    public function regexJS(?string $content): ?string
    {
        $this->pipe('regexJSBefore');

        if (!is_null($content)) {
            preg_match_all("/\<script(.*?)\>\<\/script\>/", $content, $matches);
            $shorts = (count($matches) > 0) ? $matches : null;

            $scripts = '';
            for ($x = 0; $x < count($shorts[1]); $x++) {
                $scripts .= $shorts[0][$x];
                $content = str_replace($shorts[0][$x], '', $content);
            }

            $content = '<script>' . $content . '</script>';
            $content = $scripts . $content;
        }

        $this->pipe('regexJSAfter');

        return $content;
    }

    private function getFontDefault(): string
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontdefault'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontdefaultweight'));
        return $font->name . ':' . $fontWeight->weight . '|';
    }

    private function getFontH1(): string
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth1'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth1weight'));
        return $font->name . ':' . $fontWeight->weight . '|';
    }

    private function getFontH2(): string
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth2'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth2weight'));
        return $font->name . ':' . $fontWeight->weight . '|';
    }

    private function getFontH3(): string
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth3'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth3weight'));
        return $font->name . ':' . $fontWeight->weight . '|';
    }

    private function getFontH4(): string
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth4'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth4weight'));
        return $font->name . ':' . $fontWeight->weight . '|';
    }

    private function getFontH5(): string
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth5'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth5weight'));
        return $font->name . ':' . $fontWeight->weight . '|';
    }

    private function getFontH6(): string
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth6'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth6weight'));
        return $font->name . ':' . $fontWeight->weight . '|';
    }

    private function getFontLink(): string
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontlink'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontlinkweight'));
        return $font->name . ':' . $fontWeight->weight . '|';
    }

    private function getFontP(): string
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontp'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontpweight'));
        return $font->name . ':' . $fontWeight->weight . '|';
    }

    private function getPageParent(string $breadcrumb, WebPage $page, string $separator): string
    {
        $this->pipe('getPageParentBefore');

        if ($page->id != $page->pageparent) {
            if ($page->pageparent) {
                $webPage = new WebPage();
                $webPage->loadFromCode($page->pageparent);
                $breadcrumb = '<a href="' . $webPage->url('public') . '">' . $webPage->title . '</a>' . $this->setSeparatorBreadcrumb($separator) . $breadcrumb;
                $breadcrumb = $this->getPageParent($breadcrumb, $webPage);
            }
        }

        $this->pipe('getPageParentAfter');

        return $breadcrumb;
    }

    private function setSeparatorBreadcrumb(string $separator): string
    {
        $this->pipe('setSeparatorBreadcrumbBefore');
        $html = '<span class="mx-1">' . $separator . '</span>';
        $this->pipe('setSeparatorBreadcrumbAfter');
        return $html;
    }

    private function toolBox(): ToolBox
    {
        return new ToolBox();
    }
}