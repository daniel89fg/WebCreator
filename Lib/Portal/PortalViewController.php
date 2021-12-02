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
namespace FacturaScripts\Plugins\WebCreator\Lib\Portal;

use FacturaScripts\Dinamic\Lib\Portal\PortalPanelController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\Base\ModelClass;
use FacturaScripts\Core\Lib\ExtendedController\BaseView;

/**
 * Description of PortalViewController
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
abstract class PortalViewController extends PortalPanelController
{

    const DEFAULT_TEMPLATE = 'Web/Private/PortalViewTemplate';
    const MAIN_VIEW_NAME = 'main';

    /**
     * Returns the class name of the model to use in the editView.
     */
    abstract public function getModelClassName(): string;

    protected function createEditView()
    {
        $viewName = 'Edit' . $this->getModelClassName();
        $this->addEditView($viewName, $this->getModelClassName(), 'edit');
        $this->setSettings($viewName, 'btnDelete', $this->permissions->allowDelete);
    }

    protected function createViews()
    {
        $this->addHtmlView(static::MAIN_VIEW_NAME, 'Web/Private/'.$this->getClassName(), $this->getModelClassName(), static::MAIN_VIEW_NAME);
    }

    /**
     * 
     * @return string
     */
    protected function getComposeUrlColumn(): string
    {
        return '';
    }

    /**
     * 
     * @param string   $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case self::MAIN_VIEW_NAME:
                /// do not merge end with explode
                $parts = \explode('/', $this->uri);
                $code = \end($parts);
                if (!empty($code)) {
                    $colName = empty($this->getComposeUrlColumn()) ? $view->model->primaryColumn() : $this->getComposeUrlColumn();
                    $where = [new DataBaseWhere($colName, $code)];
                    $view->loadData('', $where);
                    if ($view->count > 0) {
                        break;
                    }
                }
                $altCode = $this->request->query->get('code', '');
                $view->loadData($altCode);
                $this->description = $view->model->primaryDescription();
                $this->title .= ' ' . $view->model->primaryColumnValue();
                break;

            case 'Edit' . $this->getModelClassName():
                $code = $this->views[self::MAIN_VIEW_NAME]->model->primaryColumnValue();
                $view->loadData($code);
                break;
        }
    }

    /**
     * 
     * @return ModelClass
     */
    protected function preloadModel()
    {
        $modelClass = self::MODEL_NAMESPACE . $this->getModelClassName();
        $model = new $modelClass();

        /// do not merge end with explode
        $parts = \explode('/', $this->uri);
        $code = \end($parts);
        if (!empty($code)) {
            $colName = empty($this->getComposeUrlColumn()) ? $model->primaryColumn() : $this->getComposeUrlColumn();
            $where = [new DataBaseWhere($colName, $code)];
            if ($model->loadFromCode('', $where)) {
                return $model;
            }
        }

        $altCode = $this->request->query->get('code', '');
        $model->loadFromCode($altCode);
        return $model;
    }
}
