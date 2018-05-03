# JSON-LD Structured Data module for Oxid eShop v6

Add JSON-LD data of your shop to your website.
https://developers.google.com/search/docs/guides/intro-structured-data

Add these information to your page:

- Marketing details like your official name, logo, and social profile info
- Sitelinks searchbox
- Breadcrumbs
- Contact details

Use https://search.google.com/structured-data/testing-tool
to verify the outputs

## Installation

    composer require ivoba-oxid/jsonld

If you have OXID eshop < 6 use v1, check v1 branch of this module.

## Usage
In "Erweiterungen -> Module -> Ivo Bathke: JsonLD" enter your settings in the "Settings" tab

### Note
  - using RDFa with JsonLD is in most cases double and not needed
  - Flow theme has schema.org data baked in, which should be removed in custom theme,
    to not have unneeded markup.

## Requirements
- UTF-8
- PHP >= 7
- Oxid eShop >= CE 6

## Todo
- add fields for shopName, Organization name to override Stammdaten
- add option for beautify json

## License MIT

© [Ivo Bathke](https://oxid.ivo-bathke.name)
