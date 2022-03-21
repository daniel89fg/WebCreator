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

use FacturaScripts\Dinamic\Model\AttachedFile;
use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;

/**
 * Shortcode of webLogo
 * Displays the default logo or the logo set in the general settings.
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class webLogo extends Shortcode
{
    /**
     * Replace the block shortcode with the content of the block if found
     *
     * @param string|null $content
     *
     * @return string|null
     */
    public static function replace(?string $content): ?string
    {
        $shorts = static::searchCode($content, "/\[webLogo(.*?)\]/");

        if (count($shorts[0]) <= 0) {
            return $content;
        }

        $appSettings = static::toolBox()->appSettings();
        for ($x = 0; $x < count($shorts[1]); $x++) {
            $params = static::getAttributes($shorts[1][$x]);

            $class = $params['class'] ?? '';
            $id = $params['id'] ?? '';
            $width = $params['width'] ?? '';
            $height = $params['height'] ?? '';

            $logo = $appSettings->get('webcreator', 'siteurl') . '/Dinamic/Assets/Images/webcreator.svg';

            if ($appSettings->get('webcreator', 'idlogo')) {
                $file = new AttachedFile();
                $file->loadFromCode($appSettings->get('webcreator', 'idlogo'));
                $logo = $file->url('download-permanent');
            }

            $img = '<img src="' . $logo . '" class="' . $class . '" id="' . $id . '" width="' . $width . '" height="' . $height . '">';

            $content = str_replace($shorts[0][$x], $img, $content);
        }

        return $content;
    }
}