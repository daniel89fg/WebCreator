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

use FacturaScripts\Core\Base\FileManager;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Dinamic\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Model\AttachedFileWeb;
use ZipArchive;

/**
 * Description of EditAttachedFileWeb
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditAttachedFileWeb extends EditController
{
    /**
     * Returns the model name
     *
     * @return string
     */
    public function getModelClassName()
    {
        return 'AttachedFileWeb';
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'admin';
        $data['title'] = 'attached-files-web';
        $data['icon'] = 'fas fa-paperclip';
        return $data;
    }

    /**
     * Load views.
     */
    protected function createViews()
    {
        parent::createViews();
        if (empty($this->request->get('code', ''))) {
            $this->views[$this->getMainViewName()]->disableColumn('folder');
        } else {
            $this->views[$this->getMainViewName()]->setReadOnly(true);
            $this->views[$this->getMainViewName()]->disableColumn('file');
        }
    }

    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'insert':
                $uploadFile = $this->request->files->get('filezip', '');

                if (false === $uploadFile->isValid()) {
                    $this->toolBox()->log()->error($uploadFile->getErrorMessage());
                    return false;
                }

                if ($uploadFile->getMimeType() !== 'application/zip') {
                    $this->toolBox()->i18nLog()->error('file-not-supported');
                    return false;
                }

                $zipFile = new ZipArchive();
                $zipPath = $uploadFile->getPathname();
                $zipName = $uploadFile->getClientOriginalName();
                $result = $zipFile->open($zipPath, ZipArchive::CHECKCONS);

                if (true !== $result) {
                    ToolBox::log()->error('ZIP error: ' . $result);
                    return false;
                }

                // get folders inside the zip file
                $folders = [];
                for ($index = 0; $index < $zipFile->numFiles; $index++) {
                    $data = $zipFile->statIndex($index);
                    $path = explode('/', $data['name']);
                    if (empty($path[1])) {
                        $folders[] = $path[0];
                    }
                }

                if (empty($folders)) {
                    ToolBox::i18nLog()->error('zip-error-wrong-structure');
                    return false;
                }

                $path = FS_FOLDER . DIRECTORY_SEPARATOR . 'MyFiles/Public';
                if (false === $zipFile->extractTo($path)) {
                    ToolBox::log()->error('ZIP EXTRACT ERROR: ' . $zipName);
                    $zipFile->close();
                    return false;
                }

                $zipFile->close();
                $attachFile = new AttachedFileWeb();
                $attachFile->name = $this->request->request->get('name');
                $attachFile->folder = implode(',', $folders);

                if (false === $attachFile->save()) {
                    $this->toolBox()->i18nLog()->warning('record-save-error');
                    foreach ($folders as $folder) {
                        FileManager::delTree($path . $folder);
                    }
                    return false;
                }

                $this->toolBox()->i18nLog()->notice('record-updated-correctly');
                return true;
        }

        return parent::execPreviousAction($action);
    }
}