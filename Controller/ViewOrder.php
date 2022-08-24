<?php
/**
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @copyright 2020, Carlos García Gómez. All Rights Reserved.
 */

namespace FacturaScripts\Plugins\WebCreator\Controller;

use FacturaScripts\Dinamic\Lib\ExtendedController\BaseView;
use FacturaScripts\Dinamic\Lib\ExportManager;
use FacturaScripts\Dinamic\Lib\WebCreator\PortalViewController;

/**
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class ViewOrder extends PortalViewController
{

    public function getModelClassName(): string
    {
        return 'PedidoCliente';
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
        $this->addHtmlView('info', 'WebCreator/Private/OrderInfo', 'PedidoCliente', 'detail', 'fas fa-info-circle');
    }

    /**
     * @param string $action
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
     * @param string $viewName
     * @param BaseView $view
     * @return void
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case self::MAIN_VIEW_NAME:
                parent::loadData($viewName, $view);
                $this->title = $this->toolBox()->i18n()->trans('order') . ' ' . $view->model->codigo;
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
