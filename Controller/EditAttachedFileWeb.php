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

namespace FacturaScripts\Plugins\WebCreator\Controller;

use FacturaScripts\Core\Base\FileManager;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Dinamic\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Model\AttachedFileWeb;
use ZipArchive;

/**
 * Description of EditAttachedFileWeb
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditAttachedFileWeb extends EditController
{
    /**
     * Returns the model name
     *
     * @return string
     */
    public function getModelClassName()
    {
        return 'AttachedFileWeb';
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'admin';
        $data['title'] = 'attached-files-web';
        $data['icon'] = 'fas fa-paperclip';
        return $data;
    }

    /**
     * Load views.
     */
    protected function createViews()
    {
        parent::createViews();
        if (empty($this->request->get('code', ''))) {
            $this->views[$this->getMainViewName()]->disableColumn('folder');
        } else {
            $this->views[$this->getMainViewName()]->setReadOnly(true);
            $this->views[$this->getMainViewName()]->disableColumn('file');
        }
    }
}