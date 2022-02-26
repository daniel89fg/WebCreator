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

use FacturaScripts\Core\Base\FileManager;
use FacturaScripts\Core\Model\Base;

/**
 * Description of AttachedFileWeb
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */

class AttachedFileWeb extends Base\ModelClass
{
    use Base\ModelTrait;

    /**
     * @var date
     */
    public $date;

    /**
     * @var string
     */
    public $folder;

    /**
     * @var int
     */
    public $idattached;

    /**
     * @var string
     */
    public $name;

    public function clear()
    {
        parent::clear();
        $this->date = date('d-m-Y H:i:s');
    }

    public function delete()
    {
        $result = parent::delete();

        if (false === $result) {
            return $result;
        }

        $folders = explode(',', $this->folder);
        foreach ($folders as $folder) {
            FileManager::delTree(FS_FOLDER . DIRECTORY_SEPARATOR . 'MyFiles/Public/' . $folder);
        }

        return $result;
    }

    public static function primaryColumn()
    {
        return 'idattached';
    }

    public static function tableName()
    {
        return 'webcreator_attached_files';
    }

    public function url(string $type = 'auto', string $list = 'List')
    {
        return parent::url($type, 'ListAttachedFile?activetab=List');
    }
}