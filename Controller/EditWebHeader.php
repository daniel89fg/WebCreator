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

use FacturaScripts\Dinamic\Lib\ExtendedController\PanelController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\WebHeader;
use FacturaScripts\Dinamic\Model\WebMenu;
use FacturaScripts\Dinamic\Lib\AssetManager;
use FacturaScripts\Plugins\WebCreator\Lib\WebCreator\IncludeViewTrait;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditWebHeader extends PanelController
{

    use IncludeViewTrait;

    public function getMenus(): array
    {
        $menu = new WebMenu();
        return $menu->all([], [], 0, 0);
    }

    /**
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['menu'] = 'web';
        $pagedata['title'] = 'web-header';
        $pagedata['icon'] = 'fas fa-caret-square-up';
        $pagedata['showonmenu'] = false;
        return $pagedata;
    }

    public function imagesGallery(): array
    {
        return $this->codeModel->all('attached_files', 'idfile', 'filename', true, [
            new DataBaseWhere('mimetype', 'image/gif,image/jpeg,image/png,image/svg+xml', 'IN')
        ]);
    }

    protected function createViews()
    {
        $this->addHtmlView('EditWebHeader', 'WebCreator/Admin/EditWebHeader', 'WebHeader', 'header');
    }

    /**
     * @param string $action
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'insert':
                if ($this->saveDataAction()) {
                    $this->redirect('EditWebHeader?code=' . $this->views['EditWebHeader']->model->id . '&action=save-ok');
                }
                return true;
            case 'edit':
                $this->saveDataAction();
                return true;
            default:
                return parent::execPreviousAction($action);
        }
    }

    /**
     * @param string $viewName
     * @param BaseView $view
     * @return void
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'EditWebHeader':
                AssetManager::add('css', FS_ROUTE . '/Dinamic/Assets/CSS/codemirror.css');
                AssetManager::add('js', FS_ROUTE . '/Dinamic/Assets/JS/codemirrorBundle.js');
                $code = $this->request->get('code');
                $view->loadData($code);
                break;
        }
    }

    protected function saveDataAction(): bool
    {
        $data = $this->request->request->all();
        $content = [];

        $header = new WebHeader();
        if (!empty($data['code'])) {
            $header->id = $data['code'];
        }

        $modelFields = ['name', 'cssid', 'cssclass', 'idmenu'];
        foreach ($data as $key => $value) {
            preg_match("/header\d/", $key, $b);

            if ($b) {
                $content[$key] = $value;
                unset($data[$key]);
            } elseif (in_array($key, $modelFields)) {
                $header->$key = $value;
                unset($data[$key]);
            }
        }
        unset($data['code']);
        unset($data['action']);
        unset($data['activetab']);
        unset($data['multireqtoken']);

        $header->properties = json_encode($data);
        $header->content = json_encode($content);

        if ($header->save()) {
            $this->views['EditWebHeader']->model = $header;
            return true;
        }

        return false;
    }
}