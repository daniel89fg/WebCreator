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
 * Description of WebBlock
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Athos Online <info@athosonline.com>
 */
class WebBlock extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     * Block content.
     *
     * @var string
     */
    public $content;

    /**
     * Primary key.
     *
     * @var int
     */
    public $idblock;

    /**
     *
     * @var string
     */
    public $lastmod;

    /**
     * Block name.
     *
     * @var string
     */
    public $name;

    /**
     * Position number.
     *
     * @var int
     */
    public $ordernum;

    /**
     * Block type: body, meta, css, javascript, footer.
     *
     * @var string
     */
    public $type;

    /**
     * Reset the values of all model properties.
     */
    public function clear()
    {
        parent::clear();
        $this->content = 'Hello world!';
        $this->lastmod = \date('d-m-Y');
    }

    /**
     * Returns the name of the column that is the primary key of the model.
     *
     * @return string
     */
    public static function primaryColumn()
    {
        return 'idblock';
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
        return 'webblocks';
    }

    /**
     * Returns True if there is no errors on properties values.
     *
     * @return bool
     */
    public function test()
    {
        return parent::test();
    }

    /**
     * Returns the url where to see / modify the data.
     *
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'List')
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
}