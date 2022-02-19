<?php
/**
 * This file is part of Portal plugin for FacturaScripts.
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

use FacturaScripts\Core\Lib\Widget\VisualItem;

/**
 * Description of RowCardbody
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class RowCardbody extends VisualItem
{

    /**
     *
     * @var string
     */
    protected $fieldname;

    /**
     *
     * @var string
     */
    protected $format;

    /**
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->fieldname = $data['fieldname'];
        $this->format = $data['format'] ?? 'text';
    }

    /**
     *
     * @param object $model
     *
     * @return string
     */
    public function show($model)
    {
        switch ($this->format) {
            default:
                return $model->{$this->fieldname};
        }
    }
}
