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

use FacturaScripts\Dinamic\Lib\ExtendedController\ListController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of ListWebPage
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Athos Online <info@athosonline.com>
 */
class ListWebPage extends ListController
{

    /**
     * Returns basic page attributes
     *
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

    /**
     * Load views
     */
    protected function createViews()
    {
        $this->createViewWebPages();
        $this->createViewWebBlock();
        $this->createViewWebHeader();
        $this->createViewWebFooter();
        $this->createViewWebSidebar();
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewWebSidebar(string $viewName = 'ListWebSidebar')
    {
        $this->addView($viewName, 'WebSidebar', 'sidebars', 'fas fa-caret-square-left');
        $this->addSearchFields($viewName, ['name']);
        $this->addOrderBy($viewName, ['name'], 'name');
        $this->addOrderBy($viewName, ['lastmod'], 'last-update', 2);
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewWebFooter(string $viewName = 'ListWebFooter')
    {
        $this->addView($viewName, 'WebFooter', 'footers', 'fas fa-caret-square-down');
        $this->addSearchFields($viewName, ['name']);
        $this->addOrderBy($viewName, ['name'], 'name');
        $this->addOrderBy($viewName, ['lastmod'], 'last-update', 2);
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewWebHeader(string $viewName = 'ListWebHeader')
    {
        $this->addView($viewName, 'WebHeader', 'headers', 'fas fa-caret-square-up');
        $this->addSearchFields($viewName, ['name']);
        $this->addOrderBy($viewName, ['name'], 'name');
        $this->addOrderBy($viewName, ['lastmod'], 'last-update', 2);
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewWebBlock(string $viewName = 'ListWebBlock')
    {
        $this->addView($viewName, 'WebBlock', 'blocks', 'fas fa-code');
        $this->addSearchFields($viewName, ['content']);
        $this->addOrderBy($viewName, ['idblock'], 'code');
        $this->addOrderBy($viewName, ['name'], 'name');
        $this->addOrderBy($viewName, ['lastmod'], 'last-update', 2);
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewWebPages(string $viewName = 'ListWebPage')
    {
        $this->addView($viewName, 'WebPage', 'pages', 'fas fa-globe-americas');
        $this->addSearchFields($viewName, ['title', 'description']);
        $this->addOrderBy($viewName, ['permalink']);
        $this->addOrderBy($viewName, ['title']);
        $this->addOrderBy($viewName, ['lastmod'], 'last-update');

        /// filters
        $this->addFilterCheckbox($viewName, 'noindex', 'no-index', 'noindex');
    }

    protected function loadData($viewName, $view) {
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