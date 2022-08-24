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
use FacturaScripts\Dinamic\Lib\AssetManager;
use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;
use FacturaScripts\Dinamic\Model\WebMenu as WebMenuModel;
use FacturaScripts\Dinamic\Model\WebPage;

/**
 * Create menu
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class webMenu extends Shortcode
{
    /**
     * Replace the block shortcode with the content of the block if found
     */
    public static function replace(?string $content, WebPage $webpage): ?string
    {
        $shorts = static::searchCode($content, "/\[webMenu(.*?)\]/");

        if (count($shorts[0]) <= 0) {
            return $content;
        }

        $addCode = false;

        for ($x = 0; $x < count($shorts[1]); $x++) {
            $params = static::getAttributes($shorts[1][$x]);

            $idmenu = $params['idmenu'] ?? '';
            $inline = isset($params['inline']) && $params['inline'] == 'yes' ? '' : 'flex-column ';
            $unstyle = !isset($params['unstyle']) || $params['unstyle'] == 'yes' ? '' : 'list-unstyled ';

            switch ($params['align']) {
                case 'center':
                    $align = 'justify-content-center text-center ';
                    break;

                case 'right':
                    $align = 'justify-content-end text-right ';
                    break;

                case 'justify':
                    $align = 'nav-fill ';
                    break;

                case 'left':
                default:
                    $align = '';
                    break;
            }

            $menu = new WebMenuModel();
            if (false === $menu->loadFromCode($idmenu)) {
                continue;
            }

            $html = '<ul id="' . $menu->cssid . '" class="webMenu ' . (empty($inline) ? 'nav' : '') . ' ' . $unstyle . ' ' . $inline . $align . $menu->cssclass . '">';
            if (empty($inline)) {
                $html .= self::setMenuInline($menu->getLinks(), $webpage);
            } else {
                $html .= self::setMenuColumn($menu->getLinks(), $webpage, $unstyle);
            }
            $html .= '</ul>';

            $addCode = true;
            $content = str_replace($shorts[0][$x], $html, $content);
        }

        if ($addCode) {
            self::addAsset();
        }

        return $content;
    }

    protected static function addAsset()
    {
        if (static::getClassName(get_class())) {
            AssetManager::add('css', FS_ROUTE . '/Dinamic/Assets/CSS/webMenu.css');
        }
    }

    protected static function setMenuInline($links, $webpage): string
    {
        $html = '';
        foreach ($links as $link) {
            if (count($link->childrens) > 0) {
                $html .= '<li id="' . $link->cssid . '" class="nav-item dropdown ' . $link->cssclass . '">';
                $active = '';
                foreach ($link->childrens as $children) {
                    $active = $children->idpage == $webpage->idpage ? 'active' : '';
                }
                $html .= '<a class="nav-link dropdown-toggle ' . $active . '" data-toggle="dropdown" href="#" role="button" aria-expanded="false">' . $link->name . '</a>';
                $html .= '<div class="dropdown-menu">';
                foreach ($link->childrens as $children) {
                    if (count($children->childrens) > 0) {
                        $html .= '<ul class="nav">';
                        $html .= self::setMenuInline($children->childrens, $webpage);
                        $html .= '</ul>';
                    }
                    $active = $children->idpage == $webpage->idpage ? 'active' : '';
                    $href = $children->idpage ? $children->getPage()->permalink : $children->redirect;
                    $blank = $children->target ? ' target="_blank"' : '';
                    $html .= '<a class="dropdown-item ' . $active . '" href="' . $href . '" ' . $blank . '>' . $children->name . '</a>';
                }
                $html .= '</div>';
            } else {
                $href = $link->idpage ? $link->getPage()->permalink : $link->redirect;
                $blank = $link->target ? ' target="_blank"' : '';
                $active = $link->idpage == $webpage->idpage ? 'active' : '';
                $html .= '<li id="' . $link->cssid . '" class="nav-item ' . $link->cssclass . '">';
                $html .= '<a class="nav-link ' . $active . '" href="' . $href . '" ' . $blank . '>' . $link->name . '</a>';
            }

            $html .= '</li>';
        }
        return $html;
    }

    protected static function setMenuColumn($links, $webpage, $unstyle): string
    {
        $html = '';
        foreach ($links as $link) {
            if (count($link->childrens) > 0) {
                $random = ToolBox::utils()->randomString(5);
                $activeBool = false;
                foreach ($link->childrens as $children) {
                    if ($children->idpage == $webpage->idpage) {
                        $activeBool = true;
                    }
                }

                $activeStr = $activeBool ? 'active' : '';
                $html .= '<li id="' . $link->cssid . '" class="' . $link->cssclass . ' ' . $activeStr . ' ' . $unstyle . '">';
                $html .= '<a class="' . $activeStr . '">' . $link->name . '</a>';
                $html .= '<span class="pl-1 ' . ($activeBool ? '' : 'collapsed') . '" data-toggle="collapse" role="button" data-target="#acc' . $random . '" aria-expanded="' . ($activeBool ? 'true' : 'false') . '"></span>';
                $html .= '<div class="collapse ' . ($activeBool ? 'show' : '') . ' pl-4" id="acc' . $random . '" aria-expanded="' . ($activeBool ? 'true' : 'false') . '" role="button">';
                $html .= '<ul class="nav flex-column ' . $unstyle . '">';
                $html .= self::setMenuColumn($link->childrens, $webpage, $unstyle);
                $html .= '</ul>';
                $html .= '</div>';
            } else {
                $href = $link->idpage ? $link->getPage()->permalink : $link->redirect;
                $blank = $link->target ? ' target="_blank"' : '';
                $active = $link->idpage == $webpage->idpage ? 'active' : '';
                $html .= '<li id="' . $link->cssid . '" class="' . $link->cssclass . ' ' . $unstyle . '">';
                $html .= '<a class="' . $active . '" href="' . $href . '" ' . $blank . '>' . $link->name . '</a>';
            }

            $html .= '</li>';
        }
        return $html;
    }
}