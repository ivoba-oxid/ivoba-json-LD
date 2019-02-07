<?php

namespace IvobaOxid\JsonLd\Core;

use OxidEsales\Eshop\Application\Model\Article;

class JsonProductFactory
{
    private $currency;
    private $product;
    private $worstRating;
    private $bestRating;

    public function __construct(Article $product, string $currency, $worstRating = 1, $bestRating = 5)
    {
        $this->currency    = $currency;
        $this->worstRating = $worstRating;
        $this->bestRating  = $bestRating;
        $this->product     = $this->create($product);
    }

    public function getProduct(): array
    {
        return $this->product;
    }

    protected function create(Article $product)
    {
        $json = [];

        if ($product->oxarticles__oxrating->value > 0 && $product->oxarticles__oxratingcnt->value > 0) {
            $json['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'worstRating' => $this->worstRating,
                'bestRating'  => $this->bestRating,
                'ratingValue' => $product->oxarticles__oxrating->value,
                'reviewCount' => $product->oxarticles__oxratingcnt->value,
            ];
        }

        $json = $this->makeProduct($product, $json);

        if ($product->getVariantsCount() < 1) {
            $offer = $this->makeOffer($product);
        } else {
            $offer              = [
                '@type'      => 'AggregateOffer',
                'offerCount' => $product->getVariantsCount(),
            ];
            $offer['lowPrice']  = number_format($product->oxarticles__oxvarminprice->value, 2, '.', '');
            $offer['highPrice'] = number_format($product->oxarticles__oxvarmaxprice->value, 2, '.', '');
            $offer['priceCurrency'] = $this->currency;
            $offer['offers']    = [];
            //todo make $blRemoveNotOrderables configurable, adjust also getVariantsCount
            //todo make showVariants configurable
            foreach ($product->getFullVariants($blRemoveNotOrderables = true) as $variant) {
                $variantOffer                = $this->makeOffer($variant);
                $variantOffer['itemOffered'] = $this->makeProduct($variant, ['@type' => 'Product']);
                $offer['offers'][]           = $variantOffer;
            }
        }

        if ($offer) {
            $json['offers'] = $offer;
        }

        return $json;
    }

    /**
     * can be Article & SimpleVariant
     * @param $product
     * @return array
     */
    protected function makeOffer(Article $product): array
    {
        $offer                 = [
            '@type' => 'Offer',
        ];
        $offer['availability'] = 'http://schema.org/OutOfStock';
        if ($product->isBuyable()) {
            $offer['availability'] = 'http://schema.org/InStock';
        }
        $offer['itemCondition'] = 'http://schema.org/NewCondition';
        $offer['price']         = number_format($product->oxarticles__oxprice->value, 2, '.', '');
        $offer['priceCurrency'] = $this->currency;
        $offer['url'] = $product->getLink();

        return $offer;
    }

    protected function makeProduct(Article $product, array $json): array
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
