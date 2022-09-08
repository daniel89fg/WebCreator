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

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\Utils;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Dinamic\Model\Base\ModelCore;
use FacturaScripts\Dinamic\Model\WebMenuLink;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WebMenu extends ModelClass
{

    use ModelTrait;

    /** @var string */
    public $creationdate;

    /** @var string */
    public $cssclass;

    /** @var string */
    public $cssid;

    /** @var int */
    public $id;

    /** @var string */
    public $lastnick;

    /** @var string */
    public $lastupdate;

    /** @var string */
    public $name;

    /** @var string */
    public $nick;

    public function clear()
    {
        parent::clear();
        $this->creationdate = date(ModelCore::DATETIME_STYLE);
        $this->lastupdate = $this->creationdate;
        $this->nick = $_COOKIE['fsNick'];
    }

    public function getLinks(): array
    {
        $links = [];
        $modelLink = new WebMenuLink();
        $where = [
            new DataBaseWhere('idmenu', $this->id),
            new DataBaseWhere('linkparent', null)
        ];
        foreach ($modelLink->all($where, ['sort' => 'ASC'], 0, 0) as $link) {
            $link->childrens = $link->getChildrens($link->id);
            $links[] = $link;
        }
        return $links;
    }

    public static function primaryColumn(): string
    {
        return "id";
    }

    public static function tableName(): string
    {
        return "webcreator_menus";
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
