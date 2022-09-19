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

use FacturaScripts\Core\Base\FileManager;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Dinamic\Lib\WebCreator\WebCookie;
use FacturaScripts\Dinamic\Model\Base\ModelCore;
use ZipArchive;
use finfo;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class AttachedFileWeb extends Base\ModelClass
{

    use Base\ModelTrait;

    /** @var string */
    public $creationdate;

    /** @var string */
    public $folder;

    /** @var int */
    public $id;

    /** @var string */
    public $lastnick;

    /** @var string */
    public $lastupdate;

    /** @var string */
    public $name;

    /** @var string */
    public $nick;

    /** @var string */
    private $pathMyfiles;

    /** @var string */
    private $pathPublic;

    public function clear()
    {
        parent::clear();
        $this->creationdate = date(ModelCore::DATETIME_STYLE);
        $this->lastupdate = $this->creationdate;
        $this->nick = WebCookie::getCookie('fsNick');
        $this->lastnick = $this->nick;
    }

    public function delete()
    {
        if (false === parent::delete()) {
            return false;
        }

        $folders = explode(',', $this->folder);
        $this->pathPublic = FS_FOLDER . '/MyFiles/Public/';
        foreach ($folders as $folder) {
            FileManager::delTree($this->pathPublic . $folder);
        }

        return true;
    }

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'webcreator_attached_files';
    }

    public function test()
    {
        $this->pathMyfiles = FS_FOLDER . '/MyFiles/' . $this->filezip;
        if (false === file_exists($this->pathMyfiles)) {
            $this->toolBox()->i18nLog()->error('file-not-exists');
            return false;
        }

        $info = new finfo();
        $mimetype = $info->file($this->pathMyfiles, FILEINFO_MIME_TYPE);
        if ($mimetype !== 'application/zip') {
            unlink($this->pathMyfiles);
            $this->toolBox()->i18nLog()->error('file-not-supported');
            return false;
        }

        $zipFile = new ZipArchive();
        $result = $zipFile->open($this->pathMyfiles, ZipArchive::CHECKCONS);

        if (true !== $result) {
            unlink($this->pathMyfiles);
            ToolBox::log()->error('ZIP error: ' . $result);
            $zipFile->close();
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
            unlink($this->pathMyfiles);
            ToolBox::i18nLog()->error('zip-error-wrong-structure');
            $zipFile->close();
            return false;
        }

        $this->pathPublic = FS_FOLDER . '/MyFiles/Public';
        if (false === $zipFile->extractTo($this->pathPublic)) {
            unlink($this->pathMyfiles);
            ToolBox::log()->error('ZIP EXTRACT ERROR: ' . $this->filezip);
            $zipFile->close();
            return false;
        }

        foreach (FileManager::scanFolder($this->pathPublic, true) as $file) {
            if (substr($file, -4) === '.php') {
                unlink($this->pathPublic . '/' . $file);
            }
        }

        unlink($this->pathMyfiles);
        $this->folder = implode(',', $folders);
        return parent::test();
    }

    public function url(string $type = 'auto', string $list = 'List'): string
    {
        return parent::url($type, 'ListAttachedFile?activetab=List');
    }

    protected function saveInsert(array $values = [])
    {
        if (false === parent::saveInsert()) {
            $folders = explode(',', $this->folder);
            foreach ($folders as $folder) {
                FileManager::delTree($this->pathPublic . '/' . $folder);
            }
            return false;
        }

        return true;
    }

    protected function saveUpdate(array $values = [])
    {
        $this->lastnick = WebCookie::getCookie('fsNick');
        $this->lastupdate = date(ModelCore::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }
}