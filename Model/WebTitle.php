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
use FacturaScripts\Dinamic\Model\Base\ModelCore;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WebTitle extends Base\ModelClass
{

    use Base\ModelTrait;

    /** @var string */
    public $align;

    /** @var string */
    public $bgcolor;

    /** @var bool */
    public $breadcrumbs;

    /** @var string */
    public $breadcrumbsseparator;

    /** @var string */
    public $creationdate;

    /** @var string */
    public $cssclass;

    /** @var string */
    public $cssid;

    /** @var int */
    public $idimage;

    /** @var int */
    public $id;

    /** @var string */
    public $imagefixed;

    /** @var float */
    public $imageopacity;

    /** @var string */
    public $imageposition;

    /** @var string */
    public $imagerepeat;

    /** @var string */
    public $imagesize;

    /** @var string */
    public $lastnick;

    /** @var string */
    public $lastupdate;

    /** @var string */
    public $name;

    /** @var string */
    public $nick;

    /** @var string */
    public $tag;

    /** @var string */
    public $width;

    public function clear()
    {
        parent::clear();
        $this->align = 'left';
        $this->bgcolor = '#376FB7';
        $this->breadcrumbs = true;
        $this->breadcrumbsseparator = '>';
        $this->creationdate = date(ModelCore::DATETIME_STYLE);
        $this->lastupdate = $this->creationdate;
        $this->imagefixed = 'fixed';
        $this->imageopacity = 0.1;
        $this->imageposition = 'center center';
        $this->imagerepeat = 'repeat';
        $this->imagesize = 'cover';
        $this->nick = $_COOKIE['fsNick'];
        $this->tag = 'h1';
        $this->width = 'container';
    }

    public static function primaryColumn(): string
    {
        return 'id';
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
        return 'webcreator_titles';
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

    protected function saveUpdate(array $values = [])
    {
        $this->lastnick = $_COOKIE['fsNick'];
        $this->lastupdate = date(ModelCore::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}