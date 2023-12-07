<?php

namespace Pixel\ReviewBundle\DependencyInjection;

use Pixel\ReviewBundle\Admin\ReviewAdmin;
use Pixel\ReviewBundle\Entity\Review;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

class ReviewExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension("sulu_admin")) {
            $container->prependExtensionConfig(
                "sulu_admin",
                [
                    "forms" => [
                        "directories" => [
                            __DIR__ . "/../Resources/config/forms",
                        ],
                    ],
                    'lists' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/lists',
                        ],
                    ],
                    "resources" => [
                        "reviews" => [
                            "routes" => [
                                "list" => "review.get_reviews",
                                "detail" => "review.get_review",
                            ],
                        ],
                        "review_settings" => [
                            "routes" => [
                                "detail" => "review.get_review-settings",
                            ],
                        ],
                    ],
                    "field_type_options" => [
                        "selection" => [
                            "review_selection" => [
                                "default_type" => "list_overlay",
                                "resource_key" => Review::RESOURCE_KEY,
                                "view" => [
                                    "name" => ReviewAdmin::REVIEW_EDIT_FORM_VIEW,
                                    "result_to_view" => [
                                        "id" => "id",
                                    ],
                                ],
                                "types" => [
                                    "list_overlay" => [
                                        "adapter" => "table",
                                        "list_key" => Review::LIST_KEY,
                                        "display_properties" => ["name"],
                                        "icon" => "fa-star",
                                        "label" => "review",
                                        "overlay_title" => "review.reviewList",
                                    ],
                                ],
                            ],
                        ],
                        "single_selection" => [
                            "single_review_selection" => [
                                "default_type" => "list_overlay",
                                "resource_key" => Review::RESOURCE_KEY,
                                "view" => [
                                    "name" => ReviewAdmin::REVIEW_EDIT_FORM_VIEW,
                                    "result_to_view" => [
                                        "id" => "id",
                                    ],
                                ],
                                "types" => [
                                    "list_overlay" => [
                                        "adapter" => "table",
                                        "list_key" => Review::LIST_KEY,
                                        "display_properties" => ["name"],
                                        "icon" => "fa-star",
                                        "empty_text" => "review.emptyReview",
                                        "overlay_title" => "review.reviewList",
                                    ],
                                    "auto_complete" => [
                                        "display_property" => "name",
                                        "search_properties" => ["name"],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $loaderYaml = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $loader->load("services.xml");
        $loaderYaml->load("services.yaml");
    }
}
