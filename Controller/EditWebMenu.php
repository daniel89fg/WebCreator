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

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Lib\AssetManager;
use FacturaScripts\Dinamic\Model\WebMenuLink;
use FacturaScripts\Dinamic\Model\WebPage;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditWebMenu extends EditController
{

    /**
     * @return string
     */
    public function getModelClassName()
    {
        return 'WebMenu';
    }

    /**
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'web';
        $data["title"] = "WebMenu";
        $data["icon"] = "fas fa-bars";
        $data['showonmenu'] = false;
        return $data;
    }

    public function loadParentLinks(): string
    {
        $customValues = '<option value="">------</option>';
        $modelLinks = new WebMenuLink();
        $where = [
            new DataBaseWhere('idmenu', $this->getViewModelValue($this->getMainViewName(), 'idmenu')),
            new DataBaseWhere('idpage', NULL, '!=')
        ];
        foreach ($modelLinks->all($where, [], 0, 0) as $link) {
            $customValues .= '<option value="' . $link->idlink . '">' . $link->getPage()->title . '</option>';
        }
        return $customValues;
    }

    protected function autocompletePageAction(): bool
    {
        $this->setTemplate(false);

        $list = [];
        $pageModel = new WebPage();
        $query = (string)$this->request->get('term');
        foreach ($pageModel->codeModelSearch($query, 'idpage') as $value) {
            $list[] = [
                'key' => $this->toolBox()->utils()->fixHtml($value->code),
                'value' => $this->toolBox()->utils()->fixHtml($value->description)
            ];
        }

        if (empty($list)) {
            $list[] = ['key' => null, 'value' => $this->toolBox()->i18n()->trans('no-data')];
        }

        $this->response->setContent(json_encode($list));
        return false;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->setTabsPosition('bottom');
        $this->createViewsLinks();
    }

    protected function createViewsLinks(string $viewName = 'EditWebMenuLink')
    {
        AssetManager::add('css', FS_ROUTE . '/node_modules/jquery-ui-dist/jquery-ui.min.css', 2);
        AssetManager::add('js', FS_ROUTE . '/node_modules/jquery-ui-dist/jquery-ui.min.js', 2);
        $this->addHtmlView($viewName, 'WebCreator/Admin/' . $viewName, 'WebMenuLink', 'links', 'fas fa-link');
    }

    /**
     * @param string $action
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        $activeTab = $this->request->request->get('activetab');
        switch ($action) {
            case 'autocomplete-page':
                return $this->autocompletePageAction();
        }

        if ($activeTab === 'EditWebMenuLink') {
            switch ($action) {
                case 'insert':
                    return $this->saveLinkAction();
            }
        }

        return parent::execPreviousAction($action);
    }

    protected function saveLinkAction(): bool
    {
        $link = new WebMenuLink();
        $link->loadFromData($this->request->request->all());
        if (empty($link->sort)) {
            $link->sort = 10;
        }
        if (false === $link->save()) {
            $this->toolBox()->i18nLog()->error('record-save-error');
            return false;
        }
        $this->toolBox()->i18nLog()->notice('record-updated-correctly');
        return true;
    }
}
