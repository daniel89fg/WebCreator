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
namespace FacturaScripts\Plugins\WebCreator\Controller;

use FacturaScripts\Dinamic\Lib\ExtendedController\EditController;

/**
 * Description of EditWebBlock
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Athos Online <info@athosonline.com>
 */
class EditWebBlock extends EditController
{

    /**
     * Returns the model name
     *
     * @return string
     */
    public function getModelClassName()
    {
        return 'WebBlock';
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['menu'] = 'web';
        $pagedata['title'] = 'web-block';
        $pagedata['icon'] = 'fas fa-code';
        return $pagedata;
    }

    /**
     * Load views.
     */
    protected function createViews()
    {
        parent::createViews();
    }
}