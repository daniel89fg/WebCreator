<?php
/**
 * This file is part of webportal plugin for FacturaScripts.
 * Copyright (C) 2018-2019 Carlos Garcia Gomez <carlos@facturascripts.com>
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

use FacturaScripts\Core\Lib\Widget\GroupItem as parentGroupItem;

/**
 * Description of GroupItem
 *
 * @author Athos Online <info@athosonline.com>
 */
class GroupItem extends parentGroupItem
{
    /**
     * Description
     *
     * @var string
     */
    protected $description;

    /**
     * 
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->description = $data['description'] ?? '';
    }

    /**
     *
     * @return string
     */
    protected function legend()
    {
        $icon = empty($this->icon) ? '' : '<i class="' . $this->icon . ' fa-fw"></i> ';
        return '<legend class="text-info mt-3 mb-0">' . $icon . static::$i18n->trans($this->title) . '</legend><small class="form-text text-muted w-100 mb-3">' . static::$i18n->trans($this->description) . '</small>';
    }
}