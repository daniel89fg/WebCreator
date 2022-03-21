<?php
/**
 * This file is part of WebCreator plugin for FacturaScripts.
 * Copyright (C) 2022 Carlos Garcia Gomez  <carlos@facturascripts.com>
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

use FacturaScripts\Dinamic\Model\WebPage;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Lib\WebCreator\GetRoutes;
use FacturaScripts\Dinamic\Model\Settings;

/**
 * Description of Permalink
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
trait PermalinkTrait
{
    private $pageOrig;
    private $permalinkFinal;

    public function checkPermalink(WebPage $webpage): string
    {
        $utils = $this->toolBox()->utils();
        $permalink = $utils->noHtml($webpage->permalink);

        $changes = ['/à/' => 'a', '/á/' => 'a', '/â/' => 'a', '/ã/' => 'a', '/ä/' => 'a',
            '/å/' => 'a', '/æ/' => 'ae', '/ç/' => 'c', '/è/' => 'e', '/é/' => 'e', '/ê/' => 'e',
            '/ë/' => 'e', '/ì/' => 'i', '/í/' => 'i', '/î/' => 'i', '/ï/' => 'i', '/ð/' => 'd',
            '/ñ/' => 'n', '/ò/' => 'o', '/ó/' => 'o', '/ô/' => 'o', '/õ/' => 'o', '/ö/' => 'o',
            '/ő/' => 'o', '/ø/' => 'o', '/ù/' => 'u', '/ú/' => 'u', '/û/' => 'u', '/ü/' => 'u',
            '/ű/' => 'u', '/ý/' => 'y', '/þ/' => 'th', '/ÿ/' => 'y', '/&quot;/' => '-'
        ];

        $permalink = \preg_replace(\array_keys($changes), $changes, \strtolower($permalink));
        $permalink = \preg_replace('/[^a-z0-9.]/i', '-', $permalink);
        $permalink = \preg_replace('/-+/', '-', $permalink);

        if (\substr($permalink, 0, 1) == '-' || substr($permalink, 0, 1) == '/') {
            $permalink = \substr($permalink, 1);
        }

        if (\substr($permalink, -1) == '-' || substr($permalink, -1) == '/') {
            $permalink = \substr($permalink, 0, -1);
        }

        /*if ($webpage->type !== 'WebPage') {
            $webSettings = new Settings();
            $propertie = 'permalink_' . strtolower($webpage->type);
            $permalink = $webSettings->get('webcreator')->{$propertie} . '/' . $permalink;
        }*/

        $this->pageOrig = $webpage;
        return $this->parentpermalink($permalink, $webpage);
    }

    public function refreshPermalinkSons(WebPage $webpage, bool $deleteParent = false)
    {
        $pageModel = new WebPage();
        $where = [new DataBaseWhere('pageparent', $webpage->idpage)];

        foreach ($pageModel->all($where, [], 0, 0) as $page) {
            $url = explode('/', $page->permalink);
            $page->permalink = end($url);
            $page->pageparent = ($deleteParent) ? null : $page->pageparent;
            $page->save();
        }
    }

    private function findPermalink(string $permalink): string
    {
        foreach (GetRoutes::getRoutes() as $key => $value) {
            if ($key == $permalink && $value['optionalId'] != $this->pageOrig->idpage) {
                $permalink .= '-2';
            }
        }

        return $permalink;
    }

    private function parentpermalink(string $permalink, WebPage $webpage): string
    {
        if (!empty($webpage->pageparent)) {
            $parent = new WebPage();
            $parent->loadFromCode($webpage->pageparent);
            $permalink = \substr($parent->permalink, 1) . '/' . $permalink;
            $this->parentpermalink($permalink, $parent);
        }

        $this->permalinkFinal = '/' . $permalink;
        return $this->findPermalink($this->permalinkFinal);
    }
}