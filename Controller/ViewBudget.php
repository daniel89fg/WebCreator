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
use FacturaScripts\Dinamic\Lib\ExportManager;
use FacturaScripts\Dinamic\Lib\WebCreator\PortalViewController;

/**
 * Description of ViewBudget
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class ViewBudget extends PortalViewController
{
    /**
     *
     * @return string
     */
    public function getModelClassName(): string
    {
        return 'PresupuestoCliente';
    }

    protected function createViews()
    {
        if (false === $this->preloadModel()->exists()) {
            return $this->error404();
        }

        $this->setContactPermissions();
        if (false === $this->permissions->allowAccess) {
            return $this->error403();
        }

        parent::createViews();
        $this->addHtmlView('info', 'WebCreator/Private/BudgetInfo', 'PresupuestoCliente', 'detail', 'fas fa-info-circle');
    }

    /**
     *
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'print':
                return $this->printAction();

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
        switch ($viewName) {
            case self::MAIN_VIEW_NAME:
                parent::loadData($viewName, $view);
                $this->title = $this->toolBox()->i18n()->trans('estimation') . ' ' . $view->model->codigo;
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function printAction(): bool
    {
        if (false === $this->permissions->allowAccess) {
            $this->toolBox()->i18nLog()->warning('access-denied');
            return true;
        }

        $this->setTemplate(false);
        $exportManager = new ExportManager();
        $exportManager->newDoc($exportManager->defaultOption());
        $exportManager->addBusinessDocPage($this->preloadModel());
        $exportManager->show($this->response);
        return false;
    }

    private function setContactPermissions()
    {
        if (empty($this->contact)) {
            /// anonymous
            $this->permissions->set(false, 0, false, false, false);
        } elseif (!empty($this->user) && $this->user->admin) {
            /// admin user
            $this->permissions->set(true, 99, true, true, false);
        } elseif ($this->preloadModel()->idcontactofact == $this->contact->idcontacto) {
            /// owner
            $this->permissions->set(true, 1, false, false, false);
        } else {
            /// unauthorized
            $this->permissions->set(false, 0, false, false, false);
        }
    }
}