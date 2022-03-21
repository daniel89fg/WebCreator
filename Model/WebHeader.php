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

namespace FacturaScripts\Plugins\WebCreator\Model;

use FacturaScripts\Core\Model\Base;

/**
 * Description of WebHeader
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WebHeader extends Base\ModelClass
{
    use Base\ModelTrait;

    /**
     * @var string
     */
    public $content;

    /**
     * @var int
     */
    public $idheader;

    /**
     * @var string
     */
    public $lastmod;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $properties;

    /**
     * Reset the values of all model properties.
     */
    public function clear()
    {
        parent::clear();
        $this->properties = [];
        $this->content = [];
    }

    /**
     * Load data from array
     *
     * @param array $data
     * @param array $exclude
     */
    public function loadFromData(array $data = [], array $exclude = [])
    {
        parent::loadFromData($data, ['properties', 'content', 'action']);
        $this->properties = isset($data['properties']) ? json_decode($data['properties'], true) : [];
        $this->content = isset($data['content']) ? json_decode($data['content'], true) : [];
    }

    /**
     * Returns the name of the column that is the primary key of the model.
     *
     * @return string
     */
    public static function primaryColumn()
    {
        return 'idheader';
    }

    /**
     *
     * @return bool
     */
    public function save()
    {
        $this->lastmod = date('d-m-Y');
        return parent::save();
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'webcreator_headers';
    }

    /**
     * Returns True if there is no errors on properties values.
     *
     * @return bool
     */
    public function test()
    {
        $content = json_decode($this->content);
        foreach ($content as $key => $value) {
            $content->$key = $value;
        }
        $this->content = json_encode($content);

        return parent::test();
    }

    /**
     * Returns the url where to see / modify the data.
     *
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'List')
    {
        return parent::url($type, 'ListWebPage?activetab=List');
    }
}