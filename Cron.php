<?php

namespace FacturaScripts\Plugins\WebCreator;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Plugins\WebCreator\Model\WebFont;
use FacturaScripts\Plugins\WebCreator\Model\WebFontWeight;

class Cron extends \FacturaScripts\Core\Base\CronClass {
    public function run() {
        if ($this->isTimeForJob('updateGoogleFonts', '30 days')) {
            $this->updateGoogleFonts();
            $this->jobDone('updateGoogleFonts');
        }
    }
    
    private function updateGoogleFonts()
    {
        $appSettings = $this->toolBox()->appSettings();
        $googleApi = $appSettings->get('webcreator', 'google-api');

        if (!empty($googleApi)) {
            $googleFonts = json_decode(file_get_contents('https://www.googleapis.com/webfonts/v1/webfonts?key='.$googleApi));
            if ($googleFonts) {
                foreach ($googleFonts->items as $googleFont) {
                    $font = $this->setFont($googleFont);
                    $this->setFontWeight($googleFont->variants, $font);
                }
            }
        }
    }

    private function setFont($googleFont)
    {
        $font = new WebFont();
        if (!$font->loadFromCode('', [new DataBaseWhere('name', $googleFont->family)])) {
            $font->name = $googleFont->family;
            $font->save();
        }
        return $font;
    }

    private function setFontWeight($variants, $font)
    {
        $fontWeight = new WebFontWeight();
        
        foreach ($variants as $variant) {
            $fontWeight->loadFromCode('', [
                new DataBaseWhere('idfont', $font->primaryColumnValue()),
                new DataBaseWhere('weight', $variant)
            ]);

            if ($fontWeight != false) {
                $fontWeight->idfont = $font->primaryColumnValue();
                $fontWeight->weight = $variant;
                $fontWeight->save();
            }
        }
    }
}