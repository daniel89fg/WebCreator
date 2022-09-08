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

use FacturaScripts\Dinamic\Lib\ExtendedController\ListController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class ListWebPage extends ListController
{

    /**
     * @return array
     */
    public function getPageData()
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'web';
        $pageData['title'] = 'pages';
        $pageData['icon'] = 'fas fa-globe-americas';
        return $pageData;
    }

    protected function createViews()
    {
        $this->createViewWebPages();
        $this->createViewsWebMenu();
        $this->createViewWebBlock();
        $this->createViewWebTitle();
        $this->createViewWebHeader();
        $this->createViewWebFooter();
        $this->createViewWebSidebar();
    }

    protected function createViewWebBlock(string $viewName = 'ListWebBlock')
    {
        $this->addView($viewName, 'WebBlock', 'blocks', 'fas fa-code');
        $this->addSearchFields($viewName, ['name']);
        $this->addOrderBy($viewName, ['id'], 'code');
        $this->addOrderBy($viewName, ['name'], 'name');
        $this->addOrderBy($viewName, ['lastupdate'], 'last-update');
    }

    protected function createViewWebFooter(string $viewName = 'ListWebFooter')
    {
        $this->addView($viewName, 'WebFooter', 'footers', 'fas fa-caret-square-down');
        $this->addSearchFields($viewName, ['name']);
        $this->addOrderBy($viewName, ['name'], 'name');
        $this->addOrderBy($viewName, ['lastupdate'], 'last-update');
    }

    protected function createViewWebHeader(string $viewName = 'ListWebHeader')
    {
        $this->addView($viewName, 'WebHeader', 'headers', 'fas fa-caret-square-up');
        $this->addSearchFields($viewName, ['name']);
        $this->addOrderBy($viewName, ['name'], 'name');
        $this->addOrderBy($viewName, ['lastupdate'], 'last-update');
    }

    protected function createViewsWebMenu(string $viewName = "ListWebMenu")
    {
        $this->addView($viewName, "WebMenu", "menus", "fas fa-bars");
        $this->addSearchFields($viewName, ['name']);
        $this->addOrderBy($viewName, ['name'], 'name');
        $this->addOrderBy($viewName, ['lastupdate'], 'last-update');
    }

    protected function createViewWebPages(string $viewName = 'ListWebPage')
    {
        $this->addView($viewName, 'WebPage', 'pages', 'fas fa-globe-americas');
        $this->addSearchFields($viewName, ['title']);
        $this->addOrderBy($viewName, ['CONCAT(title, pageparent)'], 'title-parent');
        $this->addOrderBy($viewName, ['title'], 'title');
        $this->addOrderBy($viewName, ['permalink'], 'permalink');
        $this->addOrderBy($viewName, ['lastupdate'], 'last-update');

        /// filters
        $this->addFilterCheckbox($viewName, 'noindex', 'no-index', 'noindex');
    }

    protected function createViewWebSidebar(string $viewName = 'ListWebSidebar')
    {
        $this->addView($viewName, 'WebSidebar', 'sidebars', 'fas fa-caret-square-left');
        $this->addSearchFields($viewName, ['name']);
        $this->addOrderBy($viewName, ['name'], 'name');
        $this->addOrderBy($viewName, ['lastupdate'], 'last-update');
    }

    protected function createViewWebTitle(string $viewName = 'ListWebTitle')
    {
        $this->addView($viewName, 'WebTitle', 'page-titles', 'fas fa-heading');
        $this->addSearchFields($viewName, ['name']);
        $this->addOrderBy($viewName, ['name'], 'name');
        $this->addOrderBy($viewName, ['lastupdate'], 'last-update');
    }

    /**
     * @param string $viewName
     * @param BaseView $view
     * @return void
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'ListWebPage':
                $where = [new DataBaseWhere('type', 'WebPage')];
                $view->loadData('', $where);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}