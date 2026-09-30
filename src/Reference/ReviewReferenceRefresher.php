<?php

declare(strict_types=1);

namespace Pixel\ReviewBundle\Reference;

use Pixel\ReviewBundle\Entity\Review;
use Pixel\ReviewBundle\Repository\ReviewRepository;
use Sulu\Bundle\ReferenceBundle\Application\Refresh\ReferenceRefresherInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class ReviewReferenceRefresher implements ReferenceRefresherInterface
{
    public function __construct(
        private ReviewReferenceProvider $reviewReferenceProvider,
        private ReviewRepository $reviewRepository,
        private WebspaceManagerInterface $webspaceManager,
        private string $suluContext,
    ) {
    }

    public static function getResourceKey(): string
    {
        return Review::RESOURCE_KEY;
    }

    public function refresh(): \Generator
    {
        $locales = $this->webspaceManager->getAllLocales();

        foreach ($this->reviewRepository->findAll() as $review) {
            foreach ($locales as $locale) {
                $review->setLocale($locale);
                $this->reviewReferenceProvider->updateReferences($review, $locale, $this->suluContext);
            }
            yield $review;
        }
    }
}
