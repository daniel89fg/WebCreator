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

namespace FacturaScripts\Plugins\WebCreator\Model\Base;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
trait WebModelTrait
{

    public function getColumnValue(string $column, string $modelName = '', string $modelId = '', string $codicu = ''): string
    {
        // obtenemos el nombre del modelo
        if (empty($modelName)) {
            $modelName = $this->modelClassName();
        }

        // obtenemos el ID del modelo
        if (empty($modelId)) {
            $modelId = $this->primaryColumnValue();
        }

        // si el plugin multi-idioma no está instalado, devolvemos el valor directamente
        if (false === WEBMULTILANGUAGE) {
            return $this->getModelValue($modelName, $modelId, $column);
        }

        $translateClass = '\\FacturaScripts\\Dinamic\\Model\\WebTranslate';
        $languageClass = '\\FacturaScripts\\Dinamic\\Model\\WebLanguage';

        // obtenemos el idioma en el que se está viendo la web si no se pasó un idioma
        if (empty($codicu)) {
            $codicu = $languageClass::getWebLangFile();
        }

        // buscamos la traducción
        $translate = new $translateClass();
        $where = [
            new DataBaseWhere('modelname', $modelName),
            new DataBaseWhere('modelid', $modelId),
            new DataBaseWhere('keytrans', $column),
            new DataBaseWhere('codicu', $codicu)
        ];

        // si encuentra la traducción devolvemos el valor
        if ($translate->loadFromCode('', $where)) {
            return $translate->valuetrans;
        }

        return $this->getModelValue($modelName, $modelId, $column);
    }

    private function getModelValue(string $modelName, string $modelId, string $column): string
    {
        // si el modelo es Settings, entonces es un modelo especial
        if ($modelName === 'Settings' || $modelName === 'WebSettings') {
            return $this->get($modelId)->{$column};
        }

        // devolvemos el valor directamente
        return $this->{$column};
    }
}