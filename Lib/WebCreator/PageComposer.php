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
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Description of PageComposer
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class PageComposer
{
    use ExtensionsTrait;

    public function getAttachFile($idfile)
    {
        $file = new AttachedFile();
        $file->loadFromCode($idfile);
        return $file->url('download-permanent');
    }

    public function getBreadcrumbs($page)
    {
        $this->pipe('getBreadcrumbsBefore');

        $breadcrumb = $page->title;
        $breadcrumb = $this->getPageParent($breadcrumb, $page);

        $this->pipe('getBreadcrumbsAfter');

        return $breadcrumb;
    }

    public function getCookie($name)
    {
        return ($_COOKIE[$name]) ?? '';
    }

    public function getDataModel($modelName, $arrayKeys, $arrayValues, $arrayOperators, $return = '', $translate = true)
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

    public function getFont($idfont)
    {
        $font = new WebFont();
        $font->loadFromCode($idfont);
        return $font->name;
    }

    public function getFooter($idfooter)
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

    public function getGoogleFonts()
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
        return '<link href="'.$link.'" rel="stylesheet">';
    }

    public function getHeader($idheader)
    {
        $header = new WebHeader();

        $this->pipe('getHeaderBefore');

        if (is_null($idheader) || $idheader == -1) {
            $header->loadFromCode($this->toolbox()->appSettings()->get('webcreator', 'headerdefault'));
        } else if ($idheader > 0) {
            $header->loadFromCode($idheader);
        }

        /*foreach ((array)$header->content as $key => $value) {
            $header->content[$key] = Shortcode::getShortcodes($value);
        }*/

        $this->pipe('getHeaderAfter');

        return $header;
    }

    public function getPageData($webPage)
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

    public function getPagesDefault()
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

        $pages = $this->pipe('getPagesDefaultAfter', $pages);
        return $pages;
    }

    public function getShortcodes($pageData)
    {
        return Shortcode::getShortcodes($pageData);
    }

    public function getSidebar($idsidebar)
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

    public function getWebSettings()
    {
        $webSettings = new Settings();
        return $webSettings->get('webcreator')->properties;
    }

    public function includeView($fileParent)
    {
        $files = [];
        $fileParentTemp = explode('/', $fileParent);
        $fileParent = str_replace('.html.twig', '', end($fileParentTemp));
        $path = FS_FOLDER . '/Dinamic/View/WebCreator/Include/';

        if (is_dir($path)) {
            $ficheros = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            foreach ($ficheros as $f) {
                if (!$f->isDir()) {
                    $file = explode('_', $f->getFilename());
                    if ($file[0] == $fileParent || $file[0] == 'PortalTemplate') {
                        $pathName = str_replace('\\', '/', $f->getPathname());
                        $directories = explode('/', $pathName);

                        $dirPlugin = '';
                        if ($directories[count($directories)-2] != 'Include') {
                            $dirPlugin = $directories[count($directories)-2] . '/';
                        }

                        $files[] = $dirPlugin . $f->getFilename();
                    }
                }
            }
            sort($files);
        }

        return $files;
    }

    public function regexCSS($content)
    {
        $this->pipe('regexCSSBefore');

        if (!empty($content)) {
            preg_match_all((string)"/\<link(.*?)\>/", $content, $matches);
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

    public function regexJS($content)
    {
        $this->pipe('regexJSBefore');

        if (!empty($content)) {
            preg_match_all((string)"/\<script(.*?)\>\<\/script\>/", $content, $matches);
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

    private function getDataModelOrigin($modelName, $arrayKeys, $arrayValues, $arrayOperators, $return)
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

    private function getDataModelTrans($arrayKeys, $arrayValues, $arrayOperators)
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

    private function getFontDefault()
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontdefault'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontdefaultweight'));
        return $font->name.':'.$fontWeight->weight.'|';
    }

    private function getFontH1()
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth1'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth1weight'));
        return $font->name.':'.$fontWeight->weight.'|';
    }

    private function getFontH2()
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth2'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth2weight'));
        return $font->name.':'.$fontWeight->weight.'|';
    }

    private function getFontH3()
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth3'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth3weight'));
        return $font->name.':'.$fontWeight->weight.'|';
    }

    private function getFontH4()
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth4'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth4weight'));
        return $font->name.':'.$fontWeight->weight.'|';
    }

    private function getFontH5()
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth5'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth5weight'));
        return $font->name.':'.$fontWeight->weight.'|';
    }

    private function getFontH6()
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth6'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fonth6weight'));
        return $font->name.':'.$fontWeight->weight.'|';
    }

    private function getFontLink()
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontlink'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontlinkweight'));
        return $font->name.':'.$fontWeight->weight.'|';
    }

    private function getFontP()
    {
        $font = new WebFont();
        $fontWeight = new WebFontWeight();
        $font->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontp'));
        $fontWeight->loadFromCode($this->toolBox()->appSettings()->get('webcreator', 'fontpweight'));
        return $font->name.':'.$fontWeight->weight.'|';
    }

    private function getPageParent($breadcrumb, $page)
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

    private function setSeparatorBreadcrumb()
    {
        $this->pipe('setSeparatorBreadcrumbBefore');
        $separator = $this->toolBox()->appSettings()->get('webcreator', 'titlebreadcrumbsseparate');
        $this->pipe('setSeparatorBreadcrumbAfter');
        return '<span class="mx-1">' . $separator . '</span>';
    }

    private function toolBox()
    {
        return new ToolBox();
    }
}