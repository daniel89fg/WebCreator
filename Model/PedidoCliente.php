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
use FacturaScripts\Core\Model\PedidoCliente as ParentModel;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class PedidoCliente extends ParentModel
{

    public function url(string $type = 'auto', string $list = 'List'): string
    {
        if ($type === 'public') {
            $page = new WebPage();
            $where = [new DataBaseWhere('customcontroller', 'ViewOrder')];
            if ($page->loadFromCode('', $where)) {
                return $page->url('public') . '?code=' . $this->primaryColumnValue();
            }
            return $this->toolBox()->appSettings()->get('webcreator', 'siteurl') . '/ViewOrder?code=' . $this->primaryColumnValue();
        }

        return parent::url($type, $list);
    }
}
