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
use FacturaScripts\Dinamic\Model\WebFooter;
use FacturaScripts\Dinamic\Model\WebSidebar;
use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;
use FacturaScripts\Dinamic\Model\WebFont;
use FacturaScripts\Dinamic\Model\WebFontWeight;
use FacturaScripts\Core\Base\ExtensionsTrait;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of PageComposer
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class PageComposer
{
    use ExtensionsTrait;
    use IncludeViewTrait;

    public function getAttachFile(int $idfile): string
    {
        $file = new AttachedFile();
        $file->loadFromCode($idfile);
        return $file->url('download-permanent');
    }

    public function getBreadcrumbs(WebPage $page): string
    {
        $this->pipe('getBreadcrumbsBefore');

        $breadcrumb = $page->title;
        $breadcrumb = $this->getPageParent($breadcrumb, $page);

        $this->pipe('getBreadcrumbsAfter');

        return $breadcrumb;
    }

    public function getCookie(string $name): string
    {
        return ($_COOKIE[$name]) ?? '';
    }

    public function getDataModel(string $modelName, array $arrayKeys, array $arrayValues, array $arrayOperators, string $return = '', bool $translate = true): ?mixed
    {
        $this->pipe('getDataModelBefore', $modelName, $arrayKeys, $arrayValues, $arrayOperators, $return, $translate);

        if (!$translate) {
            $data = $this->getDataModelOrigin($modelName, $arrayKeys, $arrayValues, $arrayOperators, $return);
        } else {
            $data = $this->getDataModelTrans($arrayKeys, $arrayValues, $arrayOperators);
        }

        if ($translate && is_null($data)) {
            $data = $this->getDataModelOrigin($modelName, $arrayKeys, $arrayValues, $arrayOperators, $return);
        }

        $this->pipe('getDataModelAfter', $modelName, $arrayKeys, $arrayValues, $arrayOperators, $return, $translate);

        return $data;
    }

    public function getFont(int $idfont): string
    {
        $font = new WebFont();
        $font->loadFromCode($idfont);
        return $font->name;
    }

    public function getFooter(?int $idfooter): WebFooter
    {
        $footer = new WebFooter();

        $this->pipe('getFooterBefore');

        if (is_null($idfooter) || $idfooter == -1) {
            $footer->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'footerdefault'));
        } else if ($idfooter > 0) {
            $footer->loadFromCode($idfooter);
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

    public function getHeader(?int $idheader): WebHeader
    {
        $header = new WebHeader();

        $this->pipe('getHeaderBefore');

        if (is_null($idheader) || $idheader == -1) {
            $header->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'headerdefault'));
        } else if ($idheader > 0) {
            $header->loadFromCode($idheader);
        }

        $this->pipe('getHeaderAfter');

        return $header;
    }

    public function getPageData(WebPage $webPage): array
    {
        $pageData = array(
            'settings' => $this->getWebSettings(),
            'header' => $this->getHeader($webPage->idheader ?? null),
            'sidebar' => $this->getSidebar($webPage->idsidebar ?? null),
            'page' => $webPage,
            'footer' => $this->getFooter($webPage->idfooter ?? null)
        );

        return Shortcode::getPageShortcodes($pageData);
    }

    public function getPagesDefault(): array
    {
        $this->pipe('getPagesDefaultBefore');

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

        $pages = array(
            'homepage' => $homepageUrl,
            'cookiespage' => $cookiespageUrl,
            'privacypage' => $privacypageUrl,
            'termspage' => $termspageUrl
        );

        //$pages = $this->pipe('getPagesDefaultAfter', $pages);
        return $pages;
    }

    public function getShortcodes(string $content)
    {
        return Shortcode::getShortcodes($content);
    }

    public function getSidebar(?int $idsidebar): WebSidebar
    {
        $sidebar = new WebSidebar();

        $this->pipe('getSidebarBefore');

        if (is_null($idsidebar) || $idsidebar == -1) {
            $sidebar->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'sidebardefault'));
        } else if ($idsidebar > 0) {
            $sidebar->loadFromCode($idsidebar);
        }

        $this->pipe('getSidebarAfter');

        return $sidebar;
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

    private function getDataModelOrigin(string $modelName, array $arrayKeys, array $arrayValues, array $arrayOperators, string $return): ?mixed
    {
        $this->pipe('getDataModelOriginBefore', $modelName, $arrayKeys, $arrayValues, $arrayOperators, $return);

        $result = null;
        $modelClass = '\\FacturaScripts\\Dinamic\\Model\\' . $modelName;

        if (\class_exists($modelClass)) {
            $model = new $modelClass();

            if ($modelName === 'Settings') {
                if (empty($return)) {
                    $result = $model->get($arrayValues[0]);
                } else {
                    $result = $model->get($arrayValues[0])->$return;
                }
            }

            if (is_null($result)) {
                $where = [];
                $index = 0;

                foreach ($arrayKeys as $key) {
                    $where[] = new DataBaseWhere($key, $arrayValues[$index], $arrayOperators[$index]);
                    $index++;
                }

                $model->loadFromCode('', $where);
                if (empty($return)) {
                    $result = $model;
                } else {
                    $result = $model->$return;
                }
            }
        }

        $this->pipe('getDataModelOriginAfter', $modelName, $arrayKeys, $arrayValues, $arrayOperators, $return);

        return $result;
    }

    private function getDataModelTrans(array $arrayKeys, array $arrayValues, array $arrayOperators): ?string
    {
        $this->pipe('getDataModelTransBefore', $arrayKeys, $arrayValues, $arrayOperators);

        $result = null;
        $modelClass = '\\FacturaScripts\\Dinamic\\Model\\WebTranslate';

        if (class_exists($modelClass)) {
            $model = new $modelClass();
            $where = [];
            $index = 0;

            foreach ($arrayKeys as $key) {
                $where[] = new DataBaseWhere($key, $arrayValues[$index], $arrayOperators[$index]);
                $index++;
            }

            $model->loadFromCode('', $where);
            $result = $model->valuetrans;
        }

        $this->pipe('getDataModelTransBefore', $arrayKeys, $arrayValues, $arrayOperators);

        return $result;
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

    private function getPageParent(string $breadcrumb, WebPage $page): string
    {
        $this->pipe('getPageParentBefore');

        if ($page->idpage != $page->pageparent) {
            $siteurl = $this->toolBox()->appSettings()->get('webcreator', 'siteurl');

            if ($page->pageparent) {
                $webPage = new WebPage();
                $webPage->loadFromCode($page->pageparent);
                $breadcrumb = '<a href="' . $siteurl . $webPage->permalink . '">' . $webPage->title . '</a>' . $this->setSeparatorBreadcrumb() . $breadcrumb;
                $breadcrumb = $this->getPageParent($breadcrumb, $webPage);
            }
        }

        $this->pipe('getPageParentAfter');

        return $breadcrumb;
    }

    private function setSeparatorBreadcrumb(): string
    {
        $this->pipe('setSeparatorBreadcrumbBefore');
        $separator = $this->toolBox()->appSettings()->get('webcreator', 'titlebreadcrumbsseparate');
        $this->pipe('setSeparatorBreadcrumbAfter');
        return '<span class="mx-1">' . $separator . '</span>';
    }

    private function toolBox(): ToolBox
    {
        return new ToolBox();
    }
}