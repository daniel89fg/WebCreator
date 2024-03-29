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

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Dinamic\Model\User;
use FacturaScripts\Dinamic\Model\WebPage;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class Sitemap extends Controller
{

    /**
     * @return array
     */
    public function getPageData()
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'web';
        $pageData['title'] = 'sitemap';
        $pageData['showonmenu'] = false;
        return $pageData;
    }

    /**
     * @param Response $response
     */
    public function publicCore(&$response)
    {
        parent::publicCore($response);
        $this->generateSitemap();
    }

    /**
     * @param Response $response
     * @param User $user
     * @param ControllerPermissions $permissions
     */
    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);
        $this->generateSitemap();
    }

    protected function createItem(string $loc, int $lastmod, string $changefreq = 'weekly', float $priority = 0.5): array
    {
        return [
            'loc' => $loc,
            'lastmod' => \date('Y-m-d', $lastmod),
            'changefreq' => $changefreq,
            'priority' => $priority
        ];
    }

    private function generateSitemap()
    {
        $this->setTemplate(false);
        $this->response->headers->set('Content-type', 'text/xml');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($this->getSitemapItems() as $item) {
            $xml .= '<url><loc>' . $item['loc'] . '</loc>'
                . '<lastmod>' . $item['lastmod'] . '</lastmod>'
                . '<changefreq>' . $item['changefreq'] . '</changefreq>'
                . '<priority>' . $item['priority'] . '</priority>'
                . '</url>' . "\n";
        }
        $xml .= '</urlset>';

        $this->response->setContent($xml);
    }

    protected function getSitemapItems(): array
    {
        $items = [];
        $webpageModel = new WebPage();
        foreach ($webpageModel->all([], [], 0, 0) as $wpage) {
            if ($wpage->noindex) {
                continue;
            }

            $items[] = $this->createItem($wpage->url('public'), \strtotime($wpage->lastmod), 'weekly', 0.8);
        }

        return $items;
    }
}
