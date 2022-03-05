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

use FacturaScripts\Dinamic\Lib\ExtendedController\BaseView;
use FacturaScripts\Dinamic\Lib\ExtendedController\PanelController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of WebCreator
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class WebCreator extends PanelController
{
    /**
     *
     * @return array
     */
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'admin';
        $data['title'] = 'WebCreator';
        $data['icon'] = 'fas fa-globe';
        return $data;
    }

    /**
     * Create views
     */
    protected function createViews()
    {
        $this->setTemplate('EditSettings');
        $modelName = 'Settings';

        $this->addEditView('WebSettingsDefault', $modelName, 'general');
        $this->addEditView('WebSettingsLayout', $modelName, 'general-layouts');
        $this->addEditView('WebSettingsLogin', $modelName, 'login-register');
        $this->addEditView('WebSettingsGlobal', $modelName, 'global-custom');
        $this->addEditView('WebSettingsPageTitle', $modelName, 'page-title');
        $this->addEditView('WebSettingsTypography', $modelName, 'typography-color');
        $this->addEditView('WebSettingsPermalink', $modelName, 'permalinks');

        $this->setSettings('WebSettingsDefault', 'btnDelete', false);
        $this->setSettings('WebSettingsLayout', 'btnDelete', false);
        $this->setSettings('WebSettingsLogin', 'btnDelete', false);
        $this->setSettings('WebSettingsGlobal', 'btnDelete', false);
        $this->setSettings('WebSettingsPageTitle', 'btnDelete', false);
        $this->setSettings('WebSettingsTypography', 'btnDelete', false);
        $this->setSettings('WebSettingsPermalink', 'btnDelete', false);
    }

    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'select':
                $this->setTemplate(false);
                $results = $this->selectAction();
                $this->response->setContent(json_encode($results));
                return false;
            default:
                return parent::execPreviousAction($action);
        }
    }

    /**
     *
     * @param string $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        $this->hasData = true;

        $images = $this->codeModel->all('attached_files', 'idfile', 'filename', true, [
            new DataBaseWhere('mimetype', 'image/gif,image/jpeg,image/png', 'IN')
        ]);

        switch ($viewName) {
            case 'WebSettingsDefault':
                $this->loadLanguageValues();

                $columnLogo = $view->columnForName('logo');
                if ($columnLogo) {
                    $columnLogo->widget->setValuesFromCodeModel($images);
                }

                $columnFavicon = $view->columnForName('favicon');
                if ($columnFavicon) {
                    $columnFavicon->widget->setValuesFromCodeModel($images);
                }
            case 'WebSettingsLayout':
            case 'WebSettingsLogin':
                $loginImage = $view->columnForName('login-image');
                if ($loginImage) {
                    $loginImage->widget->setValuesFromCodeModel($images);
                }
                $registerImage = $view->columnForName('register-image');
                if ($registerImage) {
                    $registerImage->widget->setValuesFromCodeModel($images);
                }
                $forgotImage = $view->columnForName('forgot-image');
                if ($forgotImage) {
                    $forgotImage->widget->setValuesFromCodeModel($images);
                }
            case 'WebSettingsGlobal':
            case 'WebSettingsPageTitle':
                $columnFavicon = $view->columnForName('titlebackgroundimage');
                if ($columnFavicon) {
                    $columnFavicon->widget->setValuesFromCodeModel($images);
                }
            case 'WebSettingsTypography':
            case 'WebSettingsPermalink':
                $view->loadData('webcreator');
                $view->model->name = 'webcreator';
                break;
        }
    }

    /**
     * Load the available language values from translator.
     */
    protected function loadLanguageValues()
    {
        $columnLangCode = $this->views['WebSettingsDefault']->columnForName('lang-code');
        if ($columnLangCode && $columnLangCode->widget->getType() === 'select') {
            $langs = [];
            foreach ($this->toolBox()->i18n()->getAvailableLanguages() as $key => $value) {
                $langs[] = ['value' => $key, 'title' => $value];
            }

            /// sorting
            \usort($langs, function ($objA, $objB) {
                return \strcmp($objA['title'], $objB['title']);
            });

            $columnLangCode->widget->setValuesFromArray($langs, false);
        }
    }

    /**
     * Run the select action.
     * Returns a JSON string for the searched values.
     *
     * @return array
     */
    protected function selectAction(): array
    {
        $data = $this->requestGet(['field', 'fieldcode', 'fieldfilter', 'fieldtitle', 'formname', 'source', 'term']);

        $where = [];
        foreach (DataBaseWhere::applyOperation($data['fieldfilter'] ?? '') as $field => $operation) {
            $where[] = new DataBaseWhere($field, $data['term'], '=', $operation);
        }

        $results = [];
        $utils = $this->toolBox()->utils();
        foreach ($this->codeModel->all($data['source'], $data['fieldcode'], $data['fieldtitle'], false, $where) as $value) {
            $results[] = ['key' => $utils->fixHtml($value->code), 'value' => $utils->fixHtml($value->description)];
        }

        if (empty($results)) {
            $results[] = ['key' => null, 'value' => $this->toolBox()->i18n()->trans('no-data')];
        }

        return $results;
    }
}