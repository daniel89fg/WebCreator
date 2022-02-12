<?php
/**
 * This file is part of WebCreator plugin for FacturaScripts.
 * Copyright (C) 2020-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
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

use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Base\ExtensionsTrait;
use FacturaScripts\Dinamic\Model\EmailSent;

/**
 * Description of PortalAction class
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class PortalAction
{
    use ExtensionsTrait;

    public $uri;
    public $contact;
    public $request;

    public function __construct($uri, $contact, $request)
    {
        $this->uri = $uri;
        $this->contact = $contact;
        $this->request = $request;
        $this->execAction();
        $this->pipe('constructAfter', $this->uri, $this->contact, $this->request);
    }

    protected function execAction()
    {
        $this->pipe('execActionBefore', $this->uri, $this->contact, $this->request);

        /// email verification
        $verificode = $this->request->get('verificode', '');
        if ($verificode) {
            $email = empty($this->contact) ? '' : $this->contact->email;
            EmailSent::verify($verificode, $email);
        }

        $this->pipe('execActionAfter', $this->uri, $this->contact, $this->request);
    }

    private function toolBox(): ToolBox
    {
        return new ToolBox();
    }
}