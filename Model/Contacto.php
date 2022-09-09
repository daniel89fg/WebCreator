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

namespace FacturaScripts\Plugins\WebCreator\Model;

use FacturaScripts\Core\Model\Contacto as ParentModel;
use FacturaScripts\Dinamic\Model\Cliente as DinCliente;
use FacturaScripts\Dinamic\Model\GrupoClientes;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class Contacto extends ParentModel
{

    /**
     * @param bool $create
     *
     * @return DinCliente
     */
    public function getCustomer(bool $create = true)
    {
        $cliente = new DinCliente();
        if ($this->codcliente && $cliente->loadFromCode($this->codcliente)) {
            return $cliente;
        }

        if ($create) {
            // creates a new customer
            $cliente->cifnif = $this->cifnif ?? '';
            $cliente->codagente = $this->codagente;
            $cliente->codgrupo = $this->codgrupo;
            $cliente->codproveedor = $this->codproveedor;
            $cliente->email = $this->email;
            $cliente->fax = $this->fax;
            $cliente->idcontactoenv = $this->idcontacto;
            $cliente->idcontactofact = $this->idcontacto;
            $cliente->nombre = $this->fullName();
            $cliente->observaciones = $this->observaciones;
            $cliente->personafisica = $this->personafisica;
            $cliente->razonsocial = empty($this->empresa) ? $this->fullName() : $this->empresa;
            $cliente->telefono1 = $this->telefono1;
            $cliente->telefono2 = $this->telefono2;
            if ($cliente->save()) {
                $this->codcliente = $cliente->codcliente;
                $this->save();
            }
        }

        return $cliente;
    }

    public function getGroup(): GrupoClientes
    {
        $grupo = new GrupoClientes();
        $grupo->loadFromCode($this->codgrupo);
        return $grupo;
    }
}