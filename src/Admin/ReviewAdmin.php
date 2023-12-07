<?php

declare(strict_types=1);

namespace Pixel\ReviewBundle\Admin;

use Pixel\ReviewBundle\Entity\Review;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\TogglerToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class ReviewAdmin extends Admin
{
    public const LIST_VIEW = "review.reviews_list";
    public const REVIEW_ADD_FORM_VIEW = "review.review_add_form";
    public const REVIEW_ADD_DETAILS_FORM = "review.review_add_detail_form";
    public const REVIEW_EDIT_FORM_VIEW = "review.review_edit_form";
    public const REVIEW_EDIT_DETAILS_FORM_VIEW = "review.review_edit_form_details";

    private ViewBuilderFactoryInterface $viewBuilderFactory;
    private SecurityCheckerInterface $securityChecker;
    private WebspaceManagerInterface $webspaceManager;
    private ActivityViewBuilderFactoryInterface $activityViewBuilderFactory;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker,
        WebspaceManagerInterface $webspaceManager,
        ActivityViewBuilderFactoryInterface $activityViewBuilderFactory
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker = $securityChecker;
        $this->webspaceManager = $webspaceManager;
        $this->activityViewBuilderFactory = $activityViewBuilderFactory;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(Review::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $reviewNavigationItem = new NavigationItem("review");
            $reviewNavigationItem->setView(static::LIST_VIEW);
            $reviewNavigationItem->setIcon("fa-star");
            $reviewNavigationItem->setPosition(15);
            $navigationItemCollection->add($reviewNavigationItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $locales = $this->webspaceManager->getAllLocales();
        $formToolbarActions = [];
        $listToolbarActions = [];
        if ($this->securityChecker->hasPermission(Review::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction("sulu_admin.add");
        }
        if ($this->securityChecker->hasPermission(Review::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction("sulu_admin.save");
            $formToolbarActions[] = new TogglerToolbarAction(
                "review.isActive",
                "isActive",
                "enable",
                "disable"
            );
        }
        if ($this->securityChecker->hasPermission(Review::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $listToolbarActions[] = new ToolbarAction("sulu_admin.delete");
            $formToolbarActions[] = new ToolbarAction("sulu_admin.delete");
        }
        if ($this->securityChecker->hasPermission(Review::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction("sulu_admin.export");
        }
        if ($this->securityChecker->hasPermission(Review::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $viewCollection->add(
                $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, "/reviews/:locale")
                    ->setResourceKey(Review::RESOURCE_KEY)
                    ->setListKey(Review::LIST_KEY)
                    ->setTitle("review")
                    ->addListAdapters(['table'])
                    ->addLocales($locales)
                    ->setDefaultLocale($locales[0])
                    ->setAddView(static::REVIEW_ADD_FORM_VIEW)
                    ->setEditView(static::REVIEW_EDIT_FORM_VIEW)
                    ->addToolbarActions($listToolbarActions)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::REVIEW_ADD_FORM_VIEW, "/reviews/:locale/add")
                    ->setResourceKey(Review::RESOURCE_KEY)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::REVIEW_ADD_DETAILS_FORM, "/details")
                    ->setResourceKey(Review::RESOURCE_KEY)
                    ->setFormKey(Review::FORM_KEY)
                    ->setTabTitle("sulu_admin.details")
                    ->setEditView(static::REVIEW_EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::REVIEW_ADD_FORM_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::REVIEW_EDIT_FORM_VIEW, "/reviews/:locale/:id")
                    ->setResourceKey(Review::RESOURCE_KEY)
                    ->addLocales($locales)
                    ->setBackView(static::LIST_VIEW)
            );

            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::REVIEW_EDIT_DETAILS_FORM_VIEW, "/details")
                    ->setResourceKey(Review::RESOURCE_KEY)
                    ->setFormKey(Review::FORM_KEY)
                    ->setTabTitle("sulu_admin.details")
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::REVIEW_EDIT_FORM_VIEW)
            );

            if ($this->activityViewBuilderFactory->hasActivityListPermission()) {
                $viewCollection->add(
                    $this->activityViewBuilderFactory->createActivityListViewBuilder(static::REVIEW_EDIT_FORM_VIEW . "activity", "/activity", Review::RESOURCE_KEY)
                        ->setParent(static::REVIEW_EDIT_FORM_VIEW)
                );
            }
        }
    }

    /**
     * @return mixed[]
     */
    public function getSecurityContexts()
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                "Review" => [
                    Review::SECURITY_CONTEXT => [
                        PermissionTypes::EDIT,
                        PermissionTypes::VIEW,
                        PermissionTypes::DELETE,
                        PermissionTypes::ADD,
                    ],
                ],
            ],
        ];
    }
}
