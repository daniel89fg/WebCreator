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

namespace FacturaScripts\Plugins\WebCreator\Lib\Widget;

use FacturaScripts\Core\Lib\AssetManager;
use FacturaScripts\Core\Lib\Widget\BaseWidget;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WidgetCode extends BaseWidget
{

    /**
     * @var string
     */
    protected $lang;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->lang = $data['lang'] ?? '';
    }

    /**
     * Adds needed assets to the asset manager.
     */
    protected function assets()
    {
        AssetManager::add('css', FS_ROUTE . '/Dinamic/Assets/CSS/codemirror.css');
        AssetManager::add('js', FS_ROUTE . '/Dinamic/Assets/JS/codemirrorBundle.js');
    }

    /**
     * @param string $type
     * @param string $extraClass
     * @return string
     */
    protected function inputHtml($type = '', $extraClass = '')
    {
        $html = '<div id="' . $this->fieldname . '" class="codemirror ' . $this->lang . 'Editor">';
        $html .= '<textarea class="d-none" name="' . $this->fieldname . '">' . htmlentities($this->value, ENT_QUOTES) . '</textarea>';
        $html .= '<code class="d-none">' . htmlentities($this->value, ENT_QUOTES) . '</code>';
        $html .= '</div>';
        return $html;
    }
}