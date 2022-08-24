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

namespace FacturaScripts\Plugins\WebCreator\Lib\WebCreator;

use FacturaScripts\Dinamic\Lib\WebCreator\PortalController;
use FacturaScripts\Dinamic\Lib\ExtendedController\EditListView;
use FacturaScripts\Dinamic\Lib\ExtendedController\EditView;
use FacturaScripts\Dinamic\Lib\ExtendedController\HtmlView;
use FacturaScripts\Dinamic\Lib\ExtendedController\ListView;
use FacturaScripts\Dinamic\Model\CodeModel;

/**
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
abstract class PortalPanelController extends PortalController
{

    use ExtendedControllerTrait;

    const DEFAULT_TEMPLATE = 'WebCreator/Private/PortalPanelTemplate';
    const MODEL_NAMESPACE = '\\FacturaScripts\\Dinamic\\Model\\';

    /**
     * Indicates if the main view has data or is empty.
     *
     * @var bool
     */
    public $hasData = false;

    public function __construct(string $className, string $uri = '')
    {
        parent::__construct($className, $uri);
        $activeTabGet = $this->request->query->get('activetab', '');
        $this->active = $this->request->request->get('activetab', $activeTabGet);
        $this->codeModel = new CodeModel();
    }

    public function commonCore()
    {
        parent::commonCore();
        $this->setTemplate(static::DEFAULT_TEMPLATE);

        // Create the views to display
        $this->createViews();
        $this->pipe('createViews');

        /// Get any operations that have to be performed
        $action = $this->request->request->get('action', $this->request->query->get('action', ''));

        /// Runs operations before reading data
        if ($this->execPreviousAction($action) === false || $this->pipe('execPreviousAction', $action) === false) {
            return;
        }

        /// Load the data for each view
        $mainViewName = $this->getMainViewName();
        foreach ($this->views as $viewName => $view) {
            /// disable views if main view has no data
            if ($viewName != $mainViewName && false === $this->hasData) {
                $this->setSettings($viewName, 'active', false);
            }

            if (false === $view->settings['active']) {
                /// exclude inactive views
                continue;
            } elseif ($this->active == $viewName) {
                $view->processFormData($this->request, 'load');
            } else {
                $view->processFormData($this->request, 'preload');
            }

            $this->loadData($viewName, $view);
            $this->pipe('loadData', $viewName, $view);

            if ($viewName === $mainViewName && $view->model->exists()) {
                $this->hasData = true;
            }
        }

        /// General operations with the loaded data
        $this->execAfterAction($action);
        $this->pipe('execAfterAction', $action);
    }

    /**
     * Adds a card list type view to the controller.
     */
    protected function addCardListView(string $viewName, string $modelName, string $viewTitle, string $viewIcon = 'fas fa-list')
    {
        $view = new ListView($viewName, $viewTitle, self::MODEL_NAMESPACE . $modelName, $viewIcon);
        $view->template = 'WebCreator/Private/CardListView.html.twig';
        $this->addCustomView($viewName, $view);
    }

    /**
     * Adds a EditList type view to the controller.
     */
    protected function addEditListView(string $viewName, string $modelName, string $viewTitle, string $viewIcon = 'fas fa-bars')
    {
        $view = new EditListView($viewName, $viewTitle, self::MODEL_NAMESPACE . $modelName, $viewIcon);
        $this->addCustomView($viewName, $view);
    }

    /**
     * Adds Edit type view to the controller.
     */
    protected function addEditView(string $viewName, string $modelName, string $viewTitle, string $viewIcon = 'fas fa-edit')
    {
        $view = new EditView($viewName, $viewTitle, self::MODEL_NAMESPACE . $modelName, $viewIcon);
        $view->settings['card'] = false;
        $this->addCustomView($viewName, $view);
    }

    /**
     * Adds HTML type view to the controller.
     */
    protected function addHtmlView(string $viewName, string $fileName, string $modelName, string $viewTitle, string $viewIcon = 'fab fa-html5')
    {
        $view = new HtmlView($viewName, $viewTitle, self::MODEL_NAMESPACE . $modelName, $fileName, $viewIcon);
        $this->addCustomView($viewName, $view);
    }

    /**
     * Adds a List type view to the controller.
     */
    protected function addListView(string $viewName, string $modelName, string $viewTitle, string $viewIcon = 'fas fa-list')
    {
        $view = new ListView($viewName, $viewTitle, self::MODEL_NAMESPACE . $modelName, $viewIcon);
        $view->settings['card'] = true;
        $this->addCustomView($viewName, $view);
    }

    /**
     * Run the controller after actions.
     *
     * @param string $action
     */
    protected function execAfterAction($action)
    {
        ;
    }

    /**
     * Run the actions that alter data before reading it.
     *
     * @param string $action
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'autocomplete':
                $this->setTemplate(false);
                $results = $this->autocompleteAction();
                $this->response->setContent(\json_encode($results));
                return false;

            case 'delete':
                $this->deleteAction();
                break;

            case 'edit':
                if ($this->editAction()) {
                    $this->views[$this->active]->model->clear();
                }
                break;

            case 'insert':
                if ($this->insertAction() || !empty($this->views[$this->active]->model->primaryColumnValue())) {
                    /// wee need to clear model in these scenarios
                    $this->views[$this->active]->model->clear();
                }
                break;
        }

        return true;
    }

    /**
     * Runs the data edit action.
     *
     * @return bool
     */
    protected function editAction(): bool
    {
        if (false === $this->permissions->allowUpdate || false === $this->views[$this->active]->settings['btnSave']) {
            $this->toolBox()->i18nLog()->warning('not-allowed-modify');
            return false;
        }

        // duplicated request?
        if ($this->multiRequestProtection->tokenExist($this->request->request->get('multireqtoken', ''))) {
            $this->toolBox()->i18nLog()->warning('duplicated-request');
            return false;
        }

        // loads model data
        $code = $this->request->request->get('code', '');
        if (false === $this->views[$this->active]->model->loadFromCode($code)) {
            $this->toolBox()->i18nLog()->error('record-not-found');
            return false;
        }

        // loads form data
        $this->views[$this->active]->processFormData($this->request, 'edit');

        // checks security
        if (false === $this->checkModelSecurity($this->views[$this->active]->model)) {
            $this->toolBox()->i18nLog()->warning('not-allowed-modify');
            return false;
        }

        // has PK value been changed?
        $this->views[$this->active]->newCode = $this->views[$this->active]->model->primaryColumnValue();
        if ($code != $this->views[$this->active]->newCode && $this->views[$this->active]->model->test()) {
            $pkColumn = $this->views[$this->active]->model->primaryColumn();
            $this->views[$this->active]->model->{$pkColumn} = $code;
            // change in database
            if (!$this->views[$this->active]->model->changePrimaryColumnValue($this->views[$this->active]->newCode)) {
                $this->toolBox()->i18nLog()->error('record-save-error');
                return false;
            }
        }

        // save in database
        if ($this->views[$this->active]->model->save()) {
            $this->toolBox()->i18nLog()->notice('record-updated-correctly');
            return true;
        }

        $this->toolBox()->i18nLog()->error('record-save-error');
        return false;
    }

    /**
     * Runs data insert action.
     *
     * @return bool
     */
    protected function insertAction(): bool
    {
        if (false === $this->permissions->allowUpdate || false === $this->views[$this->active]->settings['btnNew']) {
            $this->toolBox()->i18nLog()->warning('not-allowed-modify');
            return false;
        }

        // duplicated request?
        if ($this->multiRequestProtection->tokenExist($this->request->request->get('multireqtoken', ''))) {
            $this->toolBox()->i18nLog()->warning('duplicated-request');
            return false;
        }

        // loads form data
        $this->views[$this->active]->processFormData($this->request, 'edit');
        if ($this->views[$this->active]->model->exists()) {
            $this->toolBox()->i18nLog()->error('duplicate-record');
            return false;
        }

        // checks security
        if (false === $this->checkModelSecurity($this->views[$this->active]->model)) {
            $this->toolBox()->i18nLog()->warning('not-allowed-modify');
            return false;
        }

        // save in database
        if ($this->views[$this->active]->model->save()) {
            /// redir to new model url only if this is the first view
            if ($this->active === $this->getMainViewName()) {
                $this->redirect($this->views[$this->active]->model->url() . '&action=save-ok');
            }

            $this->views[$this->active]->newCode = $this->views[$this->active]->model->primaryColumnValue();
            $this->toolBox()->i18nLog()->notice('record-updated-correctly');
            return true;
        }

        $this->toolBox()->i18nLog()->error('record-save-error');
        return false;
    }
}