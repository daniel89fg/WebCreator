<?php
/**
 * This file is part of WebCreator plugin for FacturaScripts.
 * Copyright (C) 2022 Carlos Garcia Gomez  <carlos@facturascripts.com>
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

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Description of IncludeViewTrait
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
trait IncludeViewTrait
{
    public static function includeView($fileParent): array
    {
        $files = [];
        $fileParentTemp = explode('/', $fileParent);
        $fileParent = str_replace('.html.twig', '', end($fileParentTemp));
        $path = FS_FOLDER . '/Dinamic/View/WebCreator/Include/';

        if (!is_dir($path)) {
            return $files;
        }

        $ficheros = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($ficheros as $f) {
            if ($f->isDir()) {
                continue;
            }

            // buscamos que el archivo empiece por el nombre del fichero padre o sea el nombre del fichero que se está incluyendo
            $file = explode('_', $f->getFilename());
            if ($file[0] != $fileParent && $file[0] != 'PortalTemplate') {
                continue;
            }

            $pathName = str_replace('\\', '/', $f->getPathname());
            $directories = explode('/', $pathName);

            $dirPlugin = '';
            if ($directories[count($directories) - 2] != 'Include') {
                $dirPlugin = $directories[count($directories) - 2] . '/';
            }

            $arrayName = explode('_', str_replace('.html.twig', '', $f->getFilename()));
            $arrayFile = [
                'path' => $dirPlugin . $f->getFilename(),
                'file' => $arrayName[0],
                'position' => $arrayName[1]
            ];

            if (false === isset($arrayName[2])) {
                $arrayName[2] = '10';
            }
            $arrayFile['order'] = str_pad($arrayName[2], 5, "0", STR_PAD_LEFT);;
            $files[] = $arrayFile;
        }

        usort($files,function($a,$b) {
            return strcmp($a['file'], $b['file']) // status ascending
                ?: strcmp($a['position'], $b['position']) // start ascending
                    ?: strcmp($a['order'], $b['order']) // mh ascending
                ;
        });

        return $files;
    }
}