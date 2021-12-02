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
namespace FacturaScripts\Plugins\WebCreator\Lib\Portal;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\WebPage;

/**
 * Description of PortalModelTrait
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
trait PortalModelTrait
{

    /**
     *
     * @var array
     */
    private static $controllerUrls = [];

    /**
     * 
     * @param string $controllerName
     * @param string $code
     * @param string $altCode
     *
     * @return string
     */
    private function composeUrl($controllerName, $code, $altCode = ''): string
    {
        if (isset(self::$controllerUrls[$controllerName])) {
            return self::$controllerUrls[$controllerName] . $code;
        }

        /// find a webpage with this controller
        $webPage = new WebPage();
        $where = [new DataBaseWhere('customcontroller', $controllerName)];
        foreach ($webPage->all($where, [], 0, 0) as $wpage) {
            self::$controllerUrls[$controllerName] = $wpage->url('public');
            return self::$controllerUrls[$controllerName] . $code;
        }

        /// webpage not found
        return empty($altCode) ? $controllerName . '?code=' . $code : $controllerName . '?code=' . $altCode;
    }

    /**
     * 
     * @param string $title
     * @param int    $length
     *
     * @return string
     */
    private static function getPermalink(string $title, int $length = 200): string
    {
        $changes = ['/à/' => 'a', '/á/' => 'a', '/â/' => 'a', '/ã/' => 'a', '/ä/' => 'a',
            '/å/' => 'a', '/æ/' => 'ae', '/ç/' => 'c', '/è/' => 'e', '/é/' => 'e', '/ê/' => 'e',
            '/ë/' => 'e', '/ì/' => 'i', '/í/' => 'i', '/î/' => 'i', '/ï/' => 'i', '/ð/' => 'd',
            '/ñ/' => 'n', '/ò/' => 'o', '/ó/' => 'o', '/ô/' => 'o', '/õ/' => 'o', '/ö/' => 'o',
            '/ő/' => 'o', '/ø/' => 'o', '/ù/' => 'u', '/ú/' => 'u', '/û/' => 'u', '/ü/' => 'u',
            '/ű/' => 'u', '/ý/' => 'y', '/þ/' => 'th', '/ÿ/' => 'y',
            '/&quot;/' => '-'
        ];
        $permalink = \preg_replace(\array_keys($changes), $changes, \strtolower($title));
        $permalink = \preg_replace('/[^a-z0-9]/i', '-', $permalink);
        $permalink = \preg_replace('/-+/', '-', $permalink);

        if (\substr($permalink, 0, 1) == '-') {
            $permalink = \substr($permalink, 1);
        }

        if (\substr($permalink, -1) == '-') {
            $permalink = \substr($permalink, 0, -1);
        }

        return \strlen($permalink) > $length ? \substr($permalink, 0, $length - 1) : $permalink;
    }
}