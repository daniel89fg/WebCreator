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

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WebCookie
{

    public static function deleteCookie(string $name, string $path = FS_ROUTE)
    {
        setcookie($name, '', time() - 3600, $path);
    }

    public static function getCookie(string $name)
    {
        return filter_input(INPUT_COOKIE, $name);
    }

    public static function setCookie(string $name, string $value, int $expire = 0, string $path = FS_ROUTE)
    {
        setcookie($name, $value, $expire, $path);
    }

    public static function saveCookie(string $name, string $value, int $expire = null, string $path = FS_ROUTE)
    {
        if (is_null($expire)) {
            $expire = time() + FS_COOKIES_EXPIRE;
        }
        setcookie($name, $value, $expire, $path);
    }
}