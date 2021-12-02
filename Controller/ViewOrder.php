<?php
/**
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @copyright 2020, Carlos García Gómez. All Rights Reserved.
 */
namespace FacturaScripts\Plugins\WebCreator\Controller;

use FacturaScripts\Dinamic\Lib\ExtendedController\BaseView;
use FacturaScripts\Dinamic\Lib\ExportManager;
use FacturaScripts\Dinamic\Lib\Portal\PortalViewController;

/**
 * Description of ViewOrder
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Athos Online <info@athosonline.com>
 */
class ViewOrder extends PortalViewController
{
    /**
     * 
     * @return string
     */
    public function getModelClassName(): string
    {
        return 'PedidoCliente';
    }

    /**
     * 
     * @return bool
     */
    private function cancelAction()
    {
        if (false === $this->permissions->allowAccess) {
            $this->toolBox()->i18nLog()->warning('access-denied');
            return true;
        }

        $order = $this->preloadModel();
        foreach ($order->getAvaliableStatus() as $status) {
            if (false === $status->editable && empty($status->generadoc)) {
                $order->idestado = $status->idestado;
                break;
            }
        }

        if ($order->save()) {
            $this->toolBox()->i18nLog()->notice('record-updated-correctly');
            $this->redirect('/plugins');
            return true;
        }

        $this->toolBox()->i18nLog()->error('record-save-error');
        return true;
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
        $this->addHtmlView('info', 'Web/Private/OrderInfo', 'PedidoCliente', 'detail', 'fas fa-info-circle');
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
            case 'cancel':
                return $this->cancelAction();

            case 'print':
                return $this->printAction();

            default:
                return parent::execPreviousAction($action);
        }
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
                parent::loadData($viewName, $view);
                $this->title = $this->toolBox()->i18n()->trans('order') . ' ' . $view->model->codigo;
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    private function printAction()
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
