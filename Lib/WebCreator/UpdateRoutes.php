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

namespace FacturaScripts\Plugins\WebCreator\Lib\WebCreator;

use FacturaScripts\Core\App\AppRouter;
use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Plugins\WebCreator\Model\WebPage;
use FacturaScripts\Core\Base\ExtensionsTrait;

/**
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class UpdateRoutes
{

    use ExtensionsTrait;

    public function setRoutes()
    {
        $resultBefore = $this->pipe('setRoutesBefore');
        if (false === $resultBefore) {
            return;
        }

        /// we must clear FacturaScripts custom routes in order to set the new ones.
        $appRouter = new AppRouter();
        $appRouter->clear();

        /// we need the homepage
        $homePage = new WebPage();
        $homePage->loadFromCode(AppSettings::get('webcreator', 'homepage'));
        $customController = empty($homePage->customcontroller) ? 'PortalHome' : $homePage->customcontroller;
        $appRouter->setRoute('/', $customController, $homePage->id, false);

        foreach ($homePage->all([], [], 0, 0) as $webpage) {
            $customController = empty($webpage->customcontroller) ? 'PortalHome' : $webpage->customcontroller;
            $appRouter->setRoute($webpage->permalink, $customController, $webpage->id, false);
        }

        $this->pipe('setRoutesAfter');
    }
}
