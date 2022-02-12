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

use FacturaScripts\Dinamic\Lib\ExtendedController\PanelController;
use FacturaScripts\Dinamic\Lib\AssetManager;
use FacturaScripts\Dinamic\Model\WebPage;
use FacturaScripts\Dinamic\Model\WebHeader;
use FacturaScripts\Dinamic\Model\WebSidebar;
use FacturaScripts\Dinamic\Model\WebFooter;
use FacturaScripts\Dinamic\Model\Page;
use FacturaScripts\Plugins\WebCreator\Lib\WebCreator\PermalinkTrait;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of EditWebPage.
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditWebPage extends PanelController
{
    use PermalinkTrait;

    public function getControllers(): array
    {
        $page = new Page();
        return $page->all([], [], 0, 0);
    }

    public function getFooters(): array
    {
        $footer = new WebFooter();
        return $footer->all([], [], 0, 0);
    }

    public function getHeaders(): array
    {
        $header = new WebHeader();
        return $header->all([], [], 0, 0);
    }

    public function getImages(): array
    {
        return $this->codeModel->all('attached_files', 'idfile', 'filename', true, [
            new DataBaseWhere('mimetype', 'image/gif,image/jpeg,image/png', 'IN')
        ]);
    }

    /**
     * Returns basic page attributes.
     *
     * @return array
     */
    public function getPageData()
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'web';
        $pageData['title'] = 'page';
        $pageData['icon'] = 'fas fa-globe-americas';
        $pageData['showonmenu'] = false;
        return $pageData;
    }

    public function getPages($idpage): array
    {
        $webpage = new WebPage();
        $result = array();
        foreach ($webpage->all([], [], 0, 0) as $page) {
            if ($idpage != $page->idpage) {
                $result[] = $page;
            }
        }
        return $result;
    }

    public function getSidebars(): array
    {
        $sidebar = new WebSidebar();
        return $sidebar->all([], [], 0, 0);
    }

    public function getSiteUrl(): string
    {
        return $this->toolBox()->appSettings()->get('webcreator', 'siteurl');
    }

    /**
     * Load views.
     */
    protected function createViews()
    {
        $this->addHtmlView('EditWebPage', 'WebCreator/Admin/EditWebPage', 'WebPage', 'page', 'fas fa-globe-americas');
    }

    /**
     * Run the actions that alter data before reading it.
     *
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        $activetab = $this->request->request->get('activetab');
        switch ($activetab) {
            case 'EditWebPage':
                switch ($action) {
                    case 'edit':
                        $page = new WebPage();
                        $page->loadFromData($this->request->request->all());

                        if ($page->save()) {
                            $this->toolBox()->i18nLog()->notice('record-updated-correctly');
                            return true;
                        } else {
                            $this->toolBox()->i18nLog()->error('record-save-error');
                            return false;
                        }
                        break;

                    case 'insert':
                        $page = new WebPage();
                        $page->loadFromData($this->request->request->all());

                        if ($page->save()) {
                            $this->redirect($activetab . '?code=' . $page->idpage . '&action=save-ok');
                        }
                        break;

                    case 'delete':
                        $page = new WebPage();
                        $page->loadFromCode($this->request->request->get('code'));
                        if ($page->delete()) {
                            $this->toolBox()->i18nLog()->notice('record-deleted-correctly');
                            return true;
                        } else {
                            $this->toolBox()->i18nLog()->warning('record-deleted-error');
                            return false;
                        }
                        break;
                }
                break;
        }

        return parent::execPreviousAction($action);
    }

    protected function loadData($viewName, $view) {
        switch ($viewName) {
            default:
                AssetManager::add('css', FS_ROUTE . '/Dinamic/Assets/CSS/codemirror.css');
                AssetManager::add('js', FS_ROUTE . '/Dinamic/Assets/JS/codemirrorBundle.js');
                $code = $this->request->get('code');
                $view->loadData($code);
                break;
        }
    }
}