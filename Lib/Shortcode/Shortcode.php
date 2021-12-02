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
namespace FacturaScripts\Plugins\WebCreator\Lib\Shortcode;

use FacturaScripts\Core\Base\ToolBox;

/**
 * Description of Shortcode
 *
 * @author Athos Online <info@athosonline.com>
 */
abstract class Shortcode
{

    abstract public static function replace($content);

    /**
     *
     * @var array
     */
    private static $codes = [];
    private static $codesActive = [];

    public static function getClassName($className)
    {
        $className = explode('\\', $className);
        $className = end($className);

        if (in_array($className, self::$codesActive)) {
            return false;
        } else {
            array_push(self::$codesActive, $className);
            return true;
        }
    }

    /**
     * 
     * @param string $code
     * @param string  $className
     */
    public static function addCode(string $code, $className)
    {
        self::$codes[$code] = '\\FacturaScripts\\Dinamic\\Lib\\Shortcode\\'.$className;
    }

    public static function getShortcodes($content)
    {
        foreach (self::$codes as $code => $className) {
            $content = $className::replace($content);
        }

        return $content;
    }

    public static function getPageShortcodes($pageData)
    {
        foreach (self::$codes as $code => $className) {
            foreach ($pageData as $key => $value) {
                switch ($key) {
                    case 'settings':
                        $value['globalmeta'] = $className::replace($value['globalmeta']);
                        $value['globalcss'] = $className::replace($value['globalcss']);
                        $value['globaljshead'] = $className::replace($value['globaljshead']);
                        $value['globaljsfooter'] = $className::replace($value['globaljsfooter']);
                        break;

                    case 'header':
                        foreach ($value->content as $Hkey => $Hvalue) {
                            $value->content[$Hkey] = Shortcode::getShortcodes($Hvalue);
                        }
                        break;

                    case 'footer':
                        foreach ($value->content as $Fkey => $Fvalue) {
                            $value->content[$Fkey] = Shortcode::getShortcodes($Fvalue);
                        }
                        break;

                    case 'sidebar':
                        $value->content = $className::replace($value->content);
                        break;

                    case 'page':
                        $value->content = $className::replace($value->content);
                        $value->pagejshead = $className::replace($value->pagejshead);
                        $value->pagejsfooter = $className::replace($value->pagejsfooter);
                        $value->pagemeta = $className::replace($value->pagemeta);
                        $value->pagecss = $className::replace($value->pagecss);
                        break;
                }
            }
        }

        return $pageData;
    }

    /*public static function getShortcodesCss($content)
    {
        $css = '';
        $codesActive = [];
        foreach (self::$codes as $code => $className) {
            if (method_exists($className, 'replaceCss')) {
                $result = $className::replaceCss($content, $codesActive, $className);
                if (!empty($result)) {
                    array_push($codesActive, $className);
                }
            }
        }

        return $css;
    }*/

    /**
     * Finds if the string with you the regular expression passed
     * 
     * @param string $content
     * @param preg_match $search
     *
     * @return int
     */
    protected static function searchCode($content, $search)
    {
        preg_match_all((string)$search, $content, $matches);
        return (count($matches) > 0) ? $matches : null;
    }

    /**
     * Obtained the attributes of a shortcode and saves them in an array
     * 
     * @param string $matches
     *
     * @return array
     */
    protected static function getAttributes($short)
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

    protected static function toolBox()
    {
        return new ToolBox();
    }
}