<?php

namespace Pixel\ReviewBundle\Admin;

use Pixel\ReviewBundle\Entity\Setting;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class SettingAdmin extends Admin
{
    public const TAB_VIEW = "review.settings";
    public const FORM_VIEW = "review.settings.form";

    private ViewBuilderFactoryInterface $viewBuilderFactory;
    private SecurityCheckerInterface $securityChecker;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker = $securityChecker;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(Setting::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $navigationItem = new NavigationItem("review.settings");
            $navigationItem->setPosition(5);
            $navigationItem->setView(static::TAB_VIEW);
            $navigationItemCollection->get(Admin::SETTINGS_NAVIGATION_ITEM)->addChild($navigationItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if ($this->securityChecker->hasPermission(Setting::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::TAB_VIEW, "/review-settings/:id")
                    ->setResourceKey(Setting::RESOURCE_KEY)
                    ->setAttributeDefault("id", "-")
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::FORM_VIEW, "/details")
                    ->setResourceKey(Setting::RESOURCE_KEY)
                    ->setFormKey(Setting::FORM_KEY)
                    ->setTabTitle("sulu_admin.details")
                    ->addToolbarActions([new ToolbarAction("sulu_admin.save")])
                    ->setParent(static::TAB_VIEW)
            );
        }
    }

    public function getSecurityContexts()
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                "Setting" => [
                    Setting::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::EDIT,
                    ],
                ],
            ],
        ];
    }
}
