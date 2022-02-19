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

        if (is_dir($path)) {
            $ficheros = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            foreach ($ficheros as $f) {
                if (!$f->isDir()) {
                    $file = explode('_', $f->getFilename());
                    if ($file[0] == $fileParent || $file[0] == 'PortalTemplate') {
                        $pathName = str_replace('\\', '/', $f->getPathname());
                        $directories = explode('/', $pathName);

                        $dirPlugin = '';
                        if ($directories[count($directories) - 2] != 'Include') {
                            $dirPlugin = $directories[count($directories) - 2] . '/';
                        }

                        $files[] = $dirPlugin . $f->getFilename();
                    }
                }
            }
            sort($files);
        }

        return $files;
    }
}