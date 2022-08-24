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

use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;
use FacturaScripts\Dinamic\Model\WebPage;

/**
 * Create the url to upload files
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class webAsset extends Shortcode
{

    /**
     * Replace the block shortcode with the content of the block if found
     */
    public static function replace(?string $content, WebPage $webpage): ?string
    {
        $shorts = static::searchCode($content, "/\[webAsset(.*?)\]/");

        if (count($shorts[0]) <= 0) {
            return $content;
        }

        $appSettings = static::toolBox()->appSettings();
        for ($x = 0; $x < count($shorts[1]); $x++) {
            $params = static::getAttributes($shorts[1][$x]);

            $file = $params['file'] ?? null;

            if (!is_null($file)) {
                $url = $appSettings->get('webcreator', 'siteurl') . $file;
                $content = str_replace($shorts[0][$x], $url, $content);
            }
        }

        return $content;
    }
}