<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athostrader.com>
 */
namespace FacturaScripts\Plugins\WebCreator\Lib\Shortcode;

use FacturaScripts\Dinamic\Lib\Shortcode\Shortcode;

/**
 * Shortcode of webAsset
 * Create the url to upload files
 *
 * @author Athos Online <info@athosonline.com>
 */
class webAsset extends Shortcode
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
        
        $shorts = static::searchCode($content, "/\[webAsset(.*?)\]/");
        
        if (count($shorts[0]) <= 0) {
            return $content;
        }
        
        $appSettings = static::toolBox()->appSettings();
        for ($x = 0; $x < count($shorts[1]); $x++) {
            $params = static::getAttributes($shorts[1][$x]);
            
            $file = isset($params['file']) ? $params['file'] : null;

            if (!is_null($file)) {
                $url = $appSettings->get('webcreator', 'siteurl').$file;    
                $content = str_replace($shorts[0][$x], $url, $content);
            }
        }

        return $content;
    }
}