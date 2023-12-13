# Sulu Review bundle

![GitHub release (with filter)](https://img.shields.io/github/v/release/Pixel-Open/sulu-reviewbundle?style=for-the-badge)
 [![Dependency](https://img.shields.io/badge/sulu-2.5-cca000.svg?style=for-the-badge)](https://sulu.io/)

## Presentation



A bundle to display customer reviews for the Sulu CMS.
Reviews can be retrieved automatically from Google My Business.

## Requirements

* PHP >= 8.0
* Composer
* Sulu >= 2.5.*
* Symfony >= 5.4


## Installation

### Install the bundle

Execute the following [composer](https://getcomposer.org/) command to add the bundle to the dependencies of your
project:

```bash

composer require pixelopen/sulu-reviewbundle

```

### Enable the bundle

Enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

 ```php
 return [
     /* ... */
     Pixel\ReviewBundle\ReviewBundle::class => ['all' => true],
 ];
 ```

### Update schema

Use a doctrine migration for this.

## Bundle Config

Define the Admin Api Route in `routes_admin.yaml`
```yaml
review.reviews_api:
  type: rest
  prefix: /admin/api
  resource: pixel_review.reviews_route_controller
  name_prefix: review.

review.setting_api:
 type: rest
 prefix: /admin/api
 resource: pixel_review.settings_route_controller
 name_prefix: review.
```

