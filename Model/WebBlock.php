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
use FacturaScripts\Dinamic\Lib\WebCreator\WebCookie;
use FacturaScripts\Dinamic\Model\Base\ModelCore;

/**
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WebBlock extends Base\ModelClass
{

    use Base\ModelTrait;

    /** @var string */
    public $creationdate;

    /** @var string */
    public $content;

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

    /** @var int */
    public $ordernum;

    /** @var string */
    public $type;

    public function clear()
    {
        parent::clear();
        $this->content = 'Hello world!';
        $this->creationdate = date(ModelCore::DATETIME_STYLE);
        $this->lastupdate = $this->creationdate;
        $this->nick = WebCookie::getCookie('fsNick');
    }

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'webcreator_blocks';
    }

    public function url(string $type = 'auto', string $list = 'List'): string
    {
        if ($type === 'public') {
            $webPage = new WebPage();
            if (!empty($this->idpage) && $webPage->loadFromCode($this->idpage)) {
                return $webPage->url($type);
            }

            return '';
        }

        return parent::url($type, 'ListWebPage?activetab=List');
    }

    protected function saveUpdate(array $values = [])
    {
        $this->lastnick = WebCookie::getCookie('fsNick');
        $this->lastupdate = date(ModelCore::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}