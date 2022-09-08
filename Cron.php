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

namespace FacturaScripts\Plugins\WebCreator;

use FacturaScripts\Core\Base\CronClass;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\WebFont;
use FacturaScripts\Dinamic\Model\WebFontWeight;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class Cron extends CronClass
{

    public function run()
    {
        if ($this->isTimeForJob('updateGoogleFonts', '1 months')) {
            $this->updateGoogleFonts();
            $this->jobDone('updateGoogleFonts');
        }
    }

    private function setFont($googleFont): WebFont
    {
        $font = new WebFont();
        $where = [new DataBaseWhere('name', $googleFont->family)];
        if (false === $font->loadFromCode('', $where)) {
            $font->name = $googleFont->family;
            $font->save();
        }
        return $font;
    }

    private function setFontWeight($variants, $font)
    {
        $fontWeight = new WebFontWeight();

        foreach ($variants as $variant) {
            $where = [
                new DataBaseWhere('id', $font->primaryColumnValue()),
                new DataBaseWhere('weight', $variant)
            ];

            if ($fontWeight->loadFromCode('', $where)) {
                $fontWeight->idfont = $font->primaryColumnValue();
                $fontWeight->weight = $variant;
                $fontWeight->save();
            }
        }
    }

    private function updateGoogleFonts()
    {
        $appSettings = $this->toolBox()->appSettings();
        $googleApi = $appSettings->get('webcreator', 'google-api', '');
        if (empty($googleApi)) {
            return;
        }

        $googleFonts = json_decode(file_get_contents('https://www.googleapis.com/webfonts/v1/webfonts?key=' . $googleApi));
        if ($googleFonts) {
            foreach ($googleFonts->items as $googleFont) {
                $font = $this->setFont($googleFont);
                $this->setFontWeight($googleFont->variants, $font);
            }
        }
    }
}