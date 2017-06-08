# JSON-LD Structured Data module for Oxid eShop

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
1. Copy contents of "copy_this" into your shop root directory. 
2. empty /tmp directory. 
3. activate the module under "Erweiterungen->Module->Ivo Bathke: JSON-LD Structured Data".

## Usage
In "Erweiterungen -> Module -> Ivo Bathke: Manufacturer Description" enter your settings in the "Settings" tab

## Requirements
- UTF-8
- PHP >= 5.6
- Oxid eShop >= CE 4.9.0

## Todo
- add fields for shopName, Organization name to override Stammdaten
- add option for beautify json
- customize RDFa output to not have double elments like image, name when using json-ld with RDFa

## License MIT

© [Ivo Bathke](https://oxid.ivo-bathke.name)