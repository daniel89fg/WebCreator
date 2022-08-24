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

use FacturaScripts\Core\Base\Utils;
use FacturaScripts\Core\Model\Base;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WebHeader extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     * @var string
     */
    public $cssclass;

    /**
     * @var string
     */
    public $cssid;

    /**
     * @var string
     */
    public $content;

    /**
     * @var int
     */
    public $idheader;

    /**
     * @var int
     */
    public $idmenu;

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

    public function clear()
    {
        parent::clear();
        $this->properties = [];
        $this->content = [];
    }

    public function loadFromData(array $data = [], array $exclude = [])
    {
        parent::loadFromData($data, ['properties', 'content', 'action']);
        $this->properties = isset($data['properties']) ? json_decode($data['properties'], true) : [];
        $this->content = isset($data['content']) ? json_decode($data['content'], true) : [];
    }

    public static function primaryColumn(): string
    {
        return 'idheader';
    }

    /**
     * @return bool
     */
    public function save()
    {
        $this->lastmod = date('d-m-Y');
        return parent::save();
    }

    public static function tableName(): string
    {
        return 'webcreator_headers';
    }

    /**
     * @return bool
     */
    public function test()
    {
        if (isset($this->cssid)) {
            $this->cssid = str_replace(' ', '-', Utils::noHtml($this->cssid));
        }

        if (isset($this->cssclass)) {
            $this->cssclass = Utils::noHtml($this->cssclass);
        }

        $content = json_decode($this->content);
        foreach ($content as $key => $value) {
            $content->$key = $value;
        }
        $this->content = json_encode($content);

        $this->idmenu = empty($this->idmenu) ? null : $this->idmenu;
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'List'): string
    {
        return parent::url($type, 'ListWebPage?activetab=List');
    }
}