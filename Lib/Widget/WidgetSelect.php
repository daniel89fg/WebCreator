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

use FacturaScripts\Core\Lib\Widget\WidgetSelect as parentWidget;
use FacturaScripts\Core\Lib\AssetManager;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WidgetSelect extends parentWidget
{

    /**
     * @var string
     */
    protected $fieldfilter;

    /**
     * @var string
     */
    protected $parent;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->parent = $data['parent'] ?? '';
    }

    /**
     * Obtains the configuration of the datasource used in obtaining data
     *
     * @return array
     */
    public function getDataSource(): array
    {
        return [
            'source' => $this->source,
            'fieldcode' => $this->fieldcode,
            'fieldtitle' => $this->fieldtitle,
            'fieldfilter' => $this->fieldfilter
        ];
    }

    /**
     * Adds assets to the asset manager.
     */
    protected function assets()
    {
        AssetManager::add('js', \FS_ROUTE . '/Dinamic/Assets/JS/WidgetSelect.js');
    }

    /**
     * @param string $type
     * @param string $extraClass
     * @return string
     */
    protected function inputHtml($type = 'text', $extraClass = '')
    {
        $class = $this->combineClasses($this->css('form-control'), $this->class, $extraClass);

        if ($this->parent != '') {
            $class = $class . ' parentSelect';
        }

        if ($this->readonly()) {
            return '<input type="hidden" name="' . $this->fieldname . '" value="' . $this->value . '"/>'
                . '<input type="text" value="' . $this->show() . '" class="' . $class . '" readonly=""/>';
        }

        $found = false;
        $html = '<select'
            . ' name="' . $this->fieldname . '"'
            . ' class="' . $class . '"'
            . $this->inputHtmlExtraParams()
            . ' parent="' . $this->parent . '"'
            . ' value="' . $this->value . '"'
            . ' data-field="' . $this->fieldname . '"'
            . ' data-source="' . $this->source . '"'
            . ' data-fieldcode="' . $this->fieldcode . '"'
            . ' data-fieldtitle="' . $this->fieldtitle . '"'
            . ' data-fieldfilter="' . $this->fieldfilter . '"'
            . '>';

        foreach ($this->values as $option) {
            $title = empty($option['title']) ? $option['value'] : $option['title'];

            /// don't use strict comparation (===)
            if ($option['value'] == $this->value) {
                $found = true;
                $html .= '<option value="' . $option['value'] . '" selected="">' . $title . '</option>';
                continue;
            }
            $html .= '<option value="' . $option['value'] . '">' . $title . '</option>';
        }
        /// value not found?
        if (!$found && !empty($this->value)) {
            $html .= '<option value="' . $this->value . '" selected="">'
                . static::$codeModel->getDescription($this->source, $this->fieldcode, $this->value, $this->fieldtitle)
                . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Set datasource data and Load data from Model into values array.
     *
     * @param array $child
     * @param bool $loadData
     */
    protected function setSourceData(array $child, bool $loadData = true)
    {
        $this->source = $child['source'];
        $this->fieldcode = $child['fieldcode'] ?? 'id';
        $this->fieldtitle = $child['fieldtitle'] ?? $this->fieldcode;
        $this->fieldfilter = $child['fieldfilter'] ?? $this->fieldfilter;
        if ($loadData) {
            $values = static::$codeModel->all($this->source, $this->fieldcode, $this->fieldtitle, !$this->required);
            $this->setValuesFromCodeModel($values, $this->translate);
        }
    }
}