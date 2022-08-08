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
use FacturaScripts\Dinamic\Model\WebPage;

/**
 * Description of WebMenuLink
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WebMenuLink extends ModelClass
{
    use ModelTrait;

    /**
     * @var string
     */
    public $cssclass;

    /**
     * @var string
     */
    public $cssid;

    /**
     * @var int
     */
    public $idlink;

    /**
     * @var int
     */
    public $idmenu;

    /**
     * @var int
     */
    public $idpage;

    /**
     * @var int
     */
    public $linkparent;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $redirect;

    /**
     * @var int
     */
    public $sort;

    /**
     * @var bool
     */
    public $target;

    public function checkLinkActive(array $links, int $idpage): bool
    {
        foreach ($links as $link) {
            if ($link->idpage === $idpage) {
                return true;
            }
            if (count($link->childrens) > 0) {
                return $this->checkLinkActive($link->childrens, $idpage);
            }
        }

        return false;
    }

    public function clear() {
        parent::clear();
        $this->sort = 10;
        $this->target = false;
    }

    public function getChildrens(int $idlink): array
    {
        $childrens = [];
        $modelLink = new self();
        $where = [new DataBaseWhere('linkparent', $idlink)];
        foreach ($modelLink->all($where, ['sort' => 'ASC'], 0, 0) as $link) {
            $link->childrens = $link->getChildrens($link->idlink);
            $childrens[] = $link;
        }
        return $childrens;
    }

    public function getPage(): WebPage
    {
        $page = new WebPage();
        $page->loadFromCode($this->idpage);
        return $page;
    }

    public static function primaryColumn(): string
    {
        return "idlink";
    }

    public static function tableName(): string
    {
        return "webcreator_menus_links";
    }

    public function test()
    {
        if (empty($this->idpage) && empty($this->redirect)) {
            $this->toolBox()->i18nLog()->warning('web-menu-link-no-page-or-redirect');
            return false;
        }

        if (false === empty($this->idpage) && false === empty($this->redirect)) {
            $this->redirect = '';
        }

        if ($this->linkparent === $this->idlink) {
            $this->linkparent = null;
        }

        if (isset($this->cssid)) {
            $this->cssid = str_replace(' ', '-', Utils::noHtml($this->cssid));
        }

        if (isset($this->cssclass)) {
            $this->cssclass = Utils::noHtml($this->cssclass);
        }

        return parent::test();
    }
}
