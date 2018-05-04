<?php

/* Please retain this copyright header in all versions of the software
 *
 * Copyright (C) 2017 Ivo Bathke
 *
 * It is published under the MIT Open Source License.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace IvobaOxid\JsonLd\Core;

use OxidEsales\Eshop\Core\Registry;
use IvobaOxid\JsonLd\Core\JsonProductFactory;

class ViewConfig extends ViewConfig_parent
{
    /**
     * @return mixed|null|string|void
     */
    public function getJsonLd()
    {
        $cfg          = Registry::getConfig();
        $jsonLd       = [];
        $organization = [];
        $webSite      = [];

        if ($cfg->getConfigParam('ivoba_json_ld_EnableMarketingDetails') && $this->getActionClassName() === 'start') {
            $organization = array_merge($organization, $this->getMarketingDetails());
        }
        if ($cfg->getConfigParam('ivoba_json_ld_EnableSearch') && $this->getActionClassName() === 'start') {
            $webSite = array_merge($webSite, $this->getSearch());
        }
        if ($cfg->getConfigParam('ivoba_json_ld_EnableContactDetails') && $this->getActionClassName() === 'start') {
            $organization = array_merge($organization, $this->getContactDetails());
        }
        if ($cfg->getConfigParam('ivoba_json_ld_EnableBreadCrumbs')) {
            $breadCrumbs = $this->getBreadCrumbs();
            if ($breadCrumbs) {
                $jsonLd[] = $breadCrumbs;
            }
        }

        if ($cfg->getConfigParam('ivoba_json_ld_EnableLists') &&
            ($this->getActionClassName() === 'alist' || $this->getActionClassName() === 'search' )) {
            $lists = $this->getLists();
            if ($lists) {
                $jsonLd[] = $lists;
            }
        }

        if ($cfg->getConfigParam('ivoba_json_ld_EnableProduct') && $this->getActionClassName() === 'details') {
            $product = $this->getProduct();
            if ($product) {
                $jsonLd[] = $product;
            }
        }

        if ($organization) {
            $organizationBase = [
                '@context' => 'http://schema.org',
                '@type'    => 'Organization',
                '@id'      => '#organization',
                'url'      => $this->getBaseDir(),
            ];
            $jsonLd[]         = array_merge($organizationBase, $organization);
        }

        if ($webSite) {
            $webSiteBase = [
                '@context' => 'http://schema.org',
                '@type'    => 'WebSite',
                '@id'      => '#website',
                'url'      => $this->getBaseDir(),
                'name'     => $cfg->getActiveShop()->oxshops__oxname->value,
            ];
            $jsonLd[]    = array_merge($webSiteBase, $webSite);
        }

        if ($jsonLd) {

            return json_encode($jsonLd);
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getMarketingDetails()
    {
        $cfg   = Registry::getConfig();
        $array = ['name' => $cfg->getActiveShop()->oxshops__oxcompany->value];
        if ($cfg->getConfigParam('ivoba_json_ld_SocialLinks')) {
            $array['sameAs'] = explode(',', $cfg->getConfigParam('ivoba_json_ld_SocialLinks'));
        }

        $array['logo'] = $this->getImageUrl($this->getShopLogo());
        if ($cfg->getConfigParam('ivoba_json_ld_Logo')) {
            $array['logo'] = $cfg->getConfigParam('ivoba_json_ld_Logo');
        }

        return $array;
    }

    /**
     * @return array
     */
    protected function getSearch()
    {
        return [
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => $this->getBaseDir().'?cl=search&searchparam={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getBreadCrumbs()
    {
        $json        = [];
        $breadCrumbs = Registry::getConfig()->getActiveView()->getBreadCrumb();
        if ($breadCrumbs) {
            $items = [];
            foreach ($breadCrumbs as $key => $breadCrumb) {
                $items[] = [
                    '@type'    => 'ListItem',
                    '@id'      => $breadCrumb['link'],
                    'position' => $key + 1,
                    'name'     => $breadCrumb['title'],
                ];
            }
            $json = [
                '@context'        => 'http://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $items,
            ];
        }

        return $json;
    }

    /**
     * @return array
     */
    protected function getContactDetails()
    {
        $cfg = Registry::getConfig();
        $tel = $cfg->getActiveShop()->oxshops__oxtelefon->value;
        //expects format +1-401-555-1212
        if (substr($tel, 0, 2) === '00') {
            $tel = substr_replace($tel, '+', 0, 2);
        }

        return [
            'contactPoint' => [
                '@type'       => 'ContactPoint',
                'telephone'   => $tel,
                'contactType' => 'customer service',
            ],
        ];
    }

    /**
     * @return array
     * @throws \oxSystemComponentException
     */
    protected function getLists()
    {
        $json = [];
        $list = Registry::getConfig()->getActiveView()->getArticleList();
        if ($list) {
            $items = [];
            $i     = 1;
            foreach ($list as $item) {
                $jsonProductFactory = new JsonProductFactory(
                    $item,
                    $this->getConfig()->getActShopCurrencyObject()->name
                );
                $jsonProduct        = $jsonProductFactory->getProduct();
                // clear elements that are not supported by ListItem
                unset($jsonProduct['sku']);
                unset($jsonProduct['gtin']);
                unset($jsonProduct['gtin13']);
                unset($jsonProduct['aggregateRating']);
                unset($jsonProduct['offers']);
                unset($jsonProduct['weight']);
                if ($jsonProduct) {
                    $items[] = array_merge([
                        '@type'    => 'ListItem',
                        'position' => $i, // offset by pagination ?
                    ], $jsonProduct);
                    $i++;
                }
            }
            $json = [
                '@context'        => 'http://schema.org',
                '@type'           => 'ItemList',
                'itemListElement' => $items,
                'numberOfItems'   => (int)Registry::getConfig()->getActiveView()->getArticleCount(),
                //todo itemListOrder
            ];
        }

        return $json;
    }

    protected function getProduct(): array
    {
        $json    = [];
        $product = Registry::getConfig()->getActiveView()->getProduct();
        if ($product) {
            $jsonProductFactory = new JsonProductFactory(
                $product,
                $this->getConfig()->getActShopCurrencyObject()->name
            );

            $jsonProduct = $jsonProductFactory->getProduct();
            if ($jsonProduct) {
                $json = array_merge([
                    '@context' => 'http://schema.org',
                    '@type'    => 'Product',
                ], $jsonProduct);
            }
        }

        return $json;
    }

    /**
     * @param $product
     * @return array
     */
    protected function makeOffer($product): array
    {
        $offer                 = [
            '@type' => 'Offer',
        ];
        $offer['availability'] = 'http://schema.org/OutStock';
        if ($product->isBuyable()) {
            $offer['availability'] = 'http://schema.org/InStock';
        }
        $offer['itemCondition'] = 'http://schema.org/NewCondition';
        $offer['price']         = number_format($product->oxarticles__oxprice->value, 2, '.', '');
        $offer['priceCurrency'] = $this->getConfig()->getActShopCurrencyObject()->name;

        return $offer;
    }

    /**
     * @param $product
     * @param $json
     * @return mixed
     */
    protected function makeProduct($product, $json)
    {
        if ($product->oxarticles__oxshortdesc->value) {
            $json['description'] = $product->oxarticles__oxshortdesc->value;
        }
        if ($product->oxarticles__oxartnum->value) {
            $json['sku'] = $product->oxarticles__oxartnum->value;
        }
        if ($product->oxarticles__oxean->value) {
            $json['gtin13'] = $product->oxarticles__oxean->value;
        }
        if ($product->oxarticles__oxmpn->value) {
            $json['mpn'] = $product->oxarticles__oxmpn->value;
        }
        if ($product->oxarticles__oxweight->value) {
            $json['weight'] = (float)number_format($product->oxarticles__oxweight->value, 3, '.', '');
        }
        if ($product->oxarticles__oxheight->value) {
            $json['height'] = (float)number_format($product->oxarticles__oxheight->value, 3, '.', '');
        }
        if ($product->oxarticles__oxwidth->value) {
            $json['width'] = (float)number_format($product->oxarticles__oxwidth->value, 3, '.', '');
        }
        if ($product->oxarticles__oxartlength->value) {
            $json['depth'] = (float)number_format($product->oxarticles__oxlength->value, 3, '.', '');
        }
        $name = $product->oxarticles__oxvarselect->value ? $product->oxarticles__oxtitle->value.' '.$product->oxarticles__oxvarselect->value : $product->oxarticles__oxtitle->value;
        if ($name) {
            $json['name'] = $name;
        }
        if ($product->oxmanufacturers__oxtitle->value) {
            $json['brand'] = $product->oxmanufacturers__oxtitle->value;
        }
        if ($product->oxcategories__oxtitle->value) {
            $json['category'] = $product->oxcategories__oxtitle->value;
        }
        if ($product->getPictureUrl()) {
            $json['image'] = $product->getPictureUrl();
        }
        if ($product->getLink()) {
            $json['url'] = $product->getLink();
        }

        return $json;
    }
}
