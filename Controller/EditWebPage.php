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
use FacturaScripts\Dinamic\Lib\AssetManager;
use FacturaScripts\Dinamic\Model\WebPage;
use FacturaScripts\Dinamic\Model\WebHeader;
use FacturaScripts\Dinamic\Model\WebSidebar;
use FacturaScripts\Dinamic\Model\WebFooter;
use FacturaScripts\Dinamic\Model\Page;
use FacturaScripts\Plugins\WebCreator\Lib\WebCreator\PermalinkTrait;
use FacturaScripts\Plugins\WebCreator\Lib\WebCreator\IncludeViewTrait;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditWebPage extends PanelController
{

    use PermalinkTrait;
    use IncludeViewTrait;

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

    protected function createViews()
    {
        $this->addHtmlView('EditWebPage', 'WebCreator/Admin/EditWebPage', 'WebPage', 'page', 'fas fa-globe-americas');

        if (WEBMULTILANGUAGE) {
            $this->setTabsPosition('top');
            $this->addEditListView('EditWebPageTranslate', 'WebTranslate', 'translations', 'fa fa-language');
        }
    }

    /**
     * @param string $action
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        $activetab = $this->request->request->get('activetab');
        switch ($activetab) {
            case 'EditWebPage':
                switch ($action) {
                    case 'edit':
                    case 'insert':
                        $page = new WebPage();
                        $page->loadFromCode($this->request->request->get('code', ''));
                        $page->loadFromData($this->request->request->all(), ['code']);

                        if (false === $page->save()) {
                            $this->toolBox()->i18nLog()->error('record-save-error');
                            return true;
                        }

                        if (empty($this->request->get('code', ''))) {
                            $controllerName = 'Edit' . $this->request->request->get('type');
                            $this->redirect($controllerName . '?code=' . $page->idpage . '&action=save-ok');
                        }

                        $this->toolBox()->i18nLog()->notice('record-updated-correctly');
                        return true;
                }
                break;
        }

        return parent::execPreviousAction($action);
    }

    /**
     * @param string $viewName
     * @param BaseView $view
     * @return void
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'EditWebPage':
                AssetManager::add('css', FS_ROUTE . '/Dinamic/Assets/CSS/codemirror.css');
                AssetManager::add('js', FS_ROUTE . '/Dinamic/Assets/JS/codemirrorBundle.js');
                $code = $this->request->get('code');
                $view->loadData($code);
                break;

            case 'EditWebPageTranslate':
                // obtenemos los idiomas para el select de idiomas
                $modelLanguage = '\\FacturaScripts\\Dinamic\\Model\\WebLanguage';
                $columnLanguages = $this->views['EditWebPageTranslate']->columnForName('language');
                if ($columnLanguages && $columnLanguages->widget->getType() === 'select') {
                    $customValues = [];
                    foreach ($modelLanguage::getWebLanguages() as $lang) {
                        $customValues[] = [
                            'value' => $lang->codicu,
                            'title' => $lang->name
                        ];
                    }
                    $columnLanguages->widget->setValuesFromArray($customValues);
                }

                // obtenemos los campos traducibles del modelo para rellenar el select de las keys
                $columnKeys = $this->views['EditWebPageTranslate']->columnForName('key');
                if ($columnKeys && $columnKeys->widget->getType() === 'select') {
                    $customValues = [];
                    $modelViewFirstName = $this->views['EditWebPage']->model->modelClassName();
                    $modelClassName = '\\FacturaScripts\\Dinamic\\Model\\' . $modelViewFirstName;
                    foreach ($modelClassName::$fieldsTranslate as $field) {
                        $customValues[] = [
                            'value' => $field,
                            'title' => $field
                        ];
                    }
                    $columnKeys->widget->setValuesFromArray($customValues);
                }

                $where = [
                    new DataBaseWhere('modelid', $this->request->get('code')),
                    new DataBaseWhere('modelname', $modelViewFirstName)
                ];
                $view->loadData('', $where);
                break;
        }
    }
}