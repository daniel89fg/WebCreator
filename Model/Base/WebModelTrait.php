<?php

namespace FacturaScripts\Plugins\WebCreator\Model\Base;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

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