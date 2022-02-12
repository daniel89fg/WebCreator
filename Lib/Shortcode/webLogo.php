<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athostrader.com>
 */
namespace FacturaScripts\Plugins\WebCreator\Lib\Shortcode;

use FacturaScripts\Dinamic\Model\AttachedFile;
use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;

/**
 * Shortcode of webLogo
 * Displays the default logo or the logo set in the general settings.
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class webLogo extends Shortcode
{
    /**
     * Replace the block shortcode with the content of the block if found
     *
     * @param string $content
     *
     * @return string
     */
    public static function replace($content)
    {
        $shorts = static::searchCode($content, "/\[webLogo(.*?)\]/");
        
        if (count($shorts[0]) <= 0) {
            return $content;
        }
        
        $appSettings = static::toolBox()->appSettings();
        for ($x = 0; $x < count($shorts[1]); $x++) {
            $params = static::getAttributes($shorts[1][$x]);
            
            $class = $params['class'] ?? '';
            $id = $params['id'] ?? '';
            $width = $params['width'] ?? '';
            $height = $params['height'] ?? '';

            $logo = $appSettings->get('webcreator', 'siteurl').'/Dinamic/Assets/Images/webcreator.svg';
            
            if ($appSettings->get('webcreator', 'idlogo')) {
                $file = new AttachedFile();
                $file->loadFromCode($appSettings->get('webcreator', 'idlogo'));
                $logo = $file->url('download-permanent');
            }

            $img = '<img src="'.$logo.'" class="'.$class.'" id="'.$id.'" width="'.$width.'" height="'.$height.'">';

            $content = str_replace($shorts[0][$x], $img, $content);
        }

        return $content;
    }
}