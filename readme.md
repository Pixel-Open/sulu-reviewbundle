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

## Features
* Display reviews on the website
* Settings management
* Possibility to retrieve reviews from Google My Business

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

## Use
### Add/Edit a review
Go to the "Reviews" section in the administration interface. Then, click on "Add".
Fill the fields that are needed for your use.

Here is the list of the fields:
* Customer name (mandatory)
* Review date (mandatory)
* Rating (mandatory)
* Message (Mandatory)
* Retrieve from Google My Business? (Read only. Allows you to know if the review was automatically picked up from Google)
* Customer image

Once you finished, click on "Save"

To edit a review, simply click on the pencil at the left of the review you wish to edit.

## Remove/Restore a gallery
There are two ways to remove a review:
* Check every review you want to remove and then click on "Delete"
* Go to the detail of a review (see the above section) and click on "Delete".

In both cases, the review will be put in the trash.

To access the trash, go to the "Settings" and click on "Trash".
To restore a review, click on the clock at the left. Confirm the restore. You will be redirected to the detail of the review you restored.

To remove permanently a review, check all the reviews you want to remove and click on "Delete".

## Settings
This bundle comes with settings. Here the list of the different settings available:
* Total reviews (mandatory)
* Average rating (mandatory)
* Use Google rating?
* Retrieve Google review?
* Place ID
* API key

The total reviews and the average rating must be filled manually unless you use the Google rating.

The option "Use Google rating?" allows you to pick up the total rating and the average rating from Google.
If you want to retrieve the reviews as well, check the "Retrieve Google review?" checkbox.

If you decide to use the reviews information from Google, you will need to fill the place ID and the API key.
Once that is done, run the following command:
```bash
bin/console sync:google:rating
```

## Twig function
There are several twig function in order to help you use the reviews and the settings on your website:

**get_latest_reviews(limit)**: returns the latest reviews. It takes one parameter: 

* limit: represents the number of the latest reviews to display. If no limit is provided, the default value is 3.

Example of use:
```twig
<div class="w-full flex flex-row gap-6 justify-between">
    {% set reviews = get_latest_reviews(4) %}
    {% for review in reviews %}
        <div class="containerAvis bg-white rounded-xl p-6 w-1/4">
            {{ review.message|raw }}
            <h3 class="block text-center text-base font-bold">{{ review.name }} - {{ review.rating }}/5 </h3>
        </div>
    {% endfor %}
</div>
```

**get_latest_reviews_html(limit)**: do the same thing as get_latest_reviews() but it renders a view instead. It takes one parameter:

* limit: represents the number of the latest reviews to display. If no limit is provided, the default value is 3.

Example of use:
```twig
<div>
    {{ get_latest_reviews_html(4) }}
</div>
```

**reviews_settings()**: returns the settings of the bundle. No parameters are required.

Example of use:
```twig
{% set reviewsSettings = reviews_settings() %}
{% if reviewsSettings is not null %}
    <div class="noteGoogle">
        <p>Note : {{ reviewsSettings.averageRating }}</p>
        <ul class="star">
            <li><img src="{{ asset('/assets/images/noteGoogle/star.svg') }}" alt=""></li>
            <li><img src="{{ asset('/assets/images/noteGoogle/star.svg') }}" alt=""></li>
            <li><img src="{{ asset('/assets/images/noteGoogle/star.svg') }}" alt=""></li>
            <li><img src="{{ asset('/assets/images/noteGoogle/star.svg') }}" alt=""></li>
            <li><img src="{{ asset('/assets/images/noteGoogle/star.svg') }}" alt=""></li>
            <li>
                More than <strong>{{ reviewsSettings.totalRating }}</strong> reviews
            </li>
        </ul>
    </div>
{% endif %}
```

## Contributing
You can contribute to this bundle. The only thing you must do is respect the coding standard we implements.
You can find them in the `ecs.php` file.
