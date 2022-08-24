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
class WebSidebar extends Base\ModelClass
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
    public $idsidebar;

    /**
     * @var string
     */
    public $lastmod;

    /**
     * @var string
     */
    public $name;

    public function clear()
    {
        parent::clear();
        $this->content = 'Hello world!';
    }

    public static function primaryColumn(): string
    {
        return 'idsidebar';
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
        return 'webcreator_sidebars';
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

        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'List'): string
    {
        return parent::url($type, 'ListWebPage?activetab=List');
    }
}