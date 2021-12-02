<?php
/**
 * This file is part of WebCreator plugin for FacturaScripts.
 * Copyright (C) 2020 Carlos Garcia Gomez <carlos@facturascripts.com>
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
 * Description of WebFontWeight
 *
 * @author Athos Online <info@athosonline.com>
 */
class WebFontWeight extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     *
     * @var string
     */
    public $lastmod;

    /**
     * ID font parent
     *
     * @var string
     */
    public $idfont;

    /**
     * Primary key
     *
     * @var string
     */
    public $idfontweight;

    /**
     * Weight font
     *
     * @var string
     */
    public $weight;

    /**
     * Reset the values of all model properties.
     */
    public function clear()
    {
        parent::clear();
        $this->lastmod = \date('d-m-Y');
    }

    /**
     * Returns the name of the column that is the primary key of the model.
     *
     * @return string
     */
    public static function primaryColumn()
    {
        return 'idfontweight';
    }

    /**
     * 
     * @return bool
     */
    public function save()
    {
        /// update last modification date
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
        return 'webfontsweight';
    }
}