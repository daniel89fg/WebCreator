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

use FacturaScripts\Dinamic\Model\WebBlock as modelWebBlock;
use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;

/**
 * Shortcode of Block
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class webBlock extends Shortcode
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
        
        $shorts = static::searchCode($content, "/\[webBlock(.*?)\]/");
        
        if (count($shorts[0]) <= 0) {
            return $content;
        }

        $blocks = new modelWebBlock();
        for ($x = 0; $x < count($shorts[1]); $x++) {
            $params = static::getAttributes($shorts[1][$x]);
            
            if (isset($params['idblock'])) {
                foreach ($blocks->all([], [], 0, 0) as $block) {
                    if ($block->idblock == $params['idblock']) {
                        $content = str_replace($shorts[0][$x], $block->content, $content);
                    }
                }
            }
        }

        return static::replace($content);
    }
}