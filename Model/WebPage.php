<?php
/**
 * This file is part of WebCreator plugin for FacturaScripts.
 * Copyright (C) 2020 Carlos Garcia Gomez <carlos@facturascripts.com>
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

use FacturaScripts\Core\Model\Base;

use FacturaScripts\Dinamic\Lib\Portal\UpdateRoutes;
use FacturaScripts\Plugins\WebCreator\Lib\Portal\PermalinkTrait;

/**
 * Description of WebPage
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Athos Online <info@athosonline.com>
 */
class WebPage extends Base\ModelOnChangeClass
{
    use Base\ModelTrait;
    use PermalinkTrait;

    /**
     * Page content.
     *
     * @var string
     */
    public $content;

    /**
     * Creation date.
     *
     * @var string
     */
    public $creationdate;

    /**
     * Page description.
     *
     * @var string
     */
    public $description;

    /**
     * image default.
     *
     * @var string
     */
    public $idfile;

    /**
     * Primary key.
     *
     * @var int
     */
    public $idpage;

    /**
     * Last modification date.
     *
     * @var string
     */
    public $lastmod;

    /**
     * ID page parent.
     *
     * @var string
     */
    public $pageparent;

    /**
     * Page javascript head.
     *
     * @var string
     */
    public $pagejshead;

    /**
     * Page javascript footer.
     *
     * @var string
     */
    public $pagejsfooter;

    /**
     * Page meta.
     *
     * @var string
     */
    public $pagemeta;

    /**
     * Permanent link.
     *
     * @var string
     */
    public $permalink;

    /**
     * Page title.
     *
     * @var string
     */
    public $title;

    /**
     * Type of the page.
     *
     * @var string
     */
    public $pagetype;

    public static $fieldsTranslate = ['title', 'description', 'permalink'];

    protected function setPreviousData(array $fields = [])
    {
        $more = ['permalink', 'pageparent'];
        parent::setPreviousData(array_merge($more, $fields));
    }

    /**
     * Reset the values of all model properties.
     */
    public function clear()
    {
        parent::clear();
        $this->creationdate = \date('d-m-Y');
        $this->lastmod = $this->creationdate;
    }

    /**
     * Returns the name of the column that is the primary key of the model.
     *
     * @return string
     */
    public static function primaryColumn()
    {
        return 'idpage';
    }

    /**
     * Returns the name of the column that describes the model, such as name, description...
     *
     * @return string
     */
    public function primaryDescriptionColumn()
    {
        return 'permalink';
    }

    /**
     * Load data from array
     *
     * @param array $data
     * @param array $exclude
     */
    public function loadFromData(array $data = [], array $exclude = [])
    {
        parent::loadFromData($data, ['properties', 'action']);
        $properties = isset($data['properties']) ? json_decode($data['properties'], true) : [];
        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
        $this->setPreviousData();
    }

    /**
     * This method is called after a record is updated on the database (saveUpdate).
     */
    protected function onUpdate()
    {
        if ($this->previousData['permalink'] !== $this->permalink || $this->previousData['pageparent'] !== $this->pageparent) {
            $this->refreshPermalinkSons($this);
            $routes = new UpdateRoutes();
            $routes->setRoutes();
        }
        parent::onUpdate();
    }

    /**
     * This method is called after a new record is saved on the database (saveInsert).
     */
    protected function onInsert()
    {
        $routes = new UpdateRoutes();
        $routes->setRoutes();
        parent::onInsert();
    }

    public function delete()
    {
        if (parent::delete()) {
            $this->refreshPermalinkSons($this, true);
            $routes = new UpdateRoutes();
            $routes->setRoutes();
            return true;
        }

        return false;
    }

    public function test()
    {
        $utils = $this->toolBox()->utils();

        if (!empty($this->code)) {
            $this->idpage = $this->code;
            unset($this->code);
        }

        $this->description = \str_replace("\n", ' ', $this->description);
        $this->title = $utils->noHtml($this->title);

        if (isset($this->idbody)) {
            $this->idbody = str_replace(' ', '-', $utils->noHtml($this->idbody));
        }

        if (isset($this->idbody)) {
            $this->classbody = $utils->noHtml($this->classbody);
        }

        $fieldReserved = array('content', 'creationdate', 'description',
            'pageparent', 'idpage', 'code', 'lastmod', 'pagejshead', 'pagejsfooter',
            'pagemeta', 'permalink', 'title', 'pagecss', 'action', 'activetab',
            'multireqtoken', 'customcontroller', 'previousData', 'type', 'idfile');

        $fieldJSON = array();
        foreach ($this as $key => $value) {
            if (!in_array($key, $fieldReserved)) {
                $fieldJSON[$key] = $value;
                unset($this->$key);
            }
        }

        $this->properties = json_encode($fieldJSON);
        $this->permalink = $this->checkPermalink($this);
        
        return parent::test();
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'webpages';
    }

    /**
     * Returns url to list or edit this model.
     *
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'List')
    {
        switch ($type) {
            case 'public':
                /// don't use ===
                if ($this->idpage == $this->toolBox()->appSettings()->get('webcreator', 'homepage')) {
                    return $this->toolBox()->appSettings()->get('webcreator', 'siteurl');
                }

                if ('*' === substr($this->permalink, -1)) {
                    return $this->toolBox()->appSettings()->get('webcreator', 'siteurl') . substr($this->permalink, 0, -1);
                }

                return $this->toolBox()->appSettings()->get('webcreator', 'siteurl') . $this->permalink;

            default:
                return parent::url($type, $list);
        }
    }
}