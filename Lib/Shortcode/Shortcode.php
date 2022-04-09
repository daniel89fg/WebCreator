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

namespace FacturaScripts\Plugins\WebCreator\Lib\Shortcode;

use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Plugins\WebCreator\Model\WebPage;

/**
 * Description of Shortcode
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
abstract class Shortcode
{
    abstract public static function replace(?string $content, $webpage = null): ?string;

    /**
     * @var array
     */
    private static $codes = [];

    /**
     * @var array
     */
    private static $codesActive = [];

    /**
     * @param string $code
     * @param string $className
     */
    public static function addCode(string $code, string $className)
    {
        self::$codes[$code] = '\\FacturaScripts\\Dinamic\\Lib\\Shortcode\\' . $className;
    }

    /**
     * @param string $className
     * @return bool
     */
    public static function getClassName(string $className): bool
    {
        $className = explode('\\', $className);
        $className = end($className);

        if (in_array($className, self::$codesActive)) {
            return false;
        } else {
            self::$codesActive[] = $className;
            return true;
        }
    }

    /**
     * @param array $pageData
     * @return array
     */
    public static function getPageShortcodes(array $pageData): array
    {
        foreach (self::$codes as $code => $className) {
            foreach ($pageData as $key => $value) {
                switch ($key) {
                    case 'settings':
                        $value['globalmeta'] = $className::replace($value['globalmeta'], $value);
                        $value['globalcss'] = $className::replace($value['globalcss'], $value);
                        $value['globaljshead'] = $className::replace($value['globaljshead'], $value);
                        $value['globaljsfooter'] = $className::replace($value['globaljsfooter'], $value);
                        break;

                    case 'header':
                        foreach ($value->content as $Hkey => $Hvalue) {
                            $value->content[$Hkey] = Shortcode::getShortcodes($Hvalue);
                        }
                        break;

                    case 'footer':
                        foreach ($value->content as $Fkey => $Fvalue) {
                            $value->content[$Fkey] = Shortcode::getShortcodes($Fvalue, $value);
                        }
                        break;

                    case 'sidebar':
                        $value->content = $className::replace($value->content, $value, $value);
                        break;

                    case 'page':
                        $value->content = $className::replace($value->content, $value);
                        $value->pagejshead = $className::replace($value->pagejshead, $value);
                        $value->pagejsfooter = $className::replace($value->pagejsfooter, $value);
                        $value->pagemeta = $className::replace($value->pagemeta, $value);
                        $value->pagecss = $className::replace($value->pagecss, $value);
                        break;
                }
            }
        }

        return $pageData;
    }

    /**
     * Find and replace shortcodes
     *
     * @param string $content
     * @param WebPage|null $webpage
     * @return string
     */
    public static function getShortcodes(string $content, ?WebPage $webpage = null): string
    {
        foreach (self::$codes as $code => $className) {
            $content = $className::replace($content, $webpage);
        }

        return $content;
    }

    /**
     * Finds if the string with you the regular expression passed
     *
     * @param string|null $content
     * @param string $search
     * @return array|null
     */
    protected static function searchCode(?string $content, string $search): ?array
    {
        preg_match_all($search, $content, $matches);
        return (count($matches) > 0) ? $matches : null;
    }

    /**
     * Obtained the attributes of a shortcode and saves them in an array
     *
     * @param string $short
     * @return array
     */
    protected static function getAttributes(string $short): array
    {
        $short = static::toolBox()->utils()->fixHtml($short);
        preg_match_all('/[\S]*=["\'][^"\']*["\']/', $short, $matches);

        $params = [];

        if (count($matches[0]) > 0) {
            foreach ($matches[0] as $a) {
                $a = str_replace(['&#39;', "'"], "", $a);
                $a = str_replace(['&quot;', '&#34;', '"'], "", $a);
                $param = explode('=', $a);
                $params[$param[0]] = $param[1];
            }
        }

        return $params;
    }

    protected static function toolBox(): ToolBox
    {
        return new ToolBox();
    }
}