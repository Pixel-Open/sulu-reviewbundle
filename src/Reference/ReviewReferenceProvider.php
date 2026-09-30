<?php

declare(strict_types=1);

namespace Pixel\ReviewBundle\Reference;

use Pixel\ReviewBundle\Entity\Review;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\ReferenceBundle\Application\Collector\ReferenceCollector;
use Sulu\Bundle\ReferenceBundle\Domain\Repository\ReferenceRepositoryInterface;

class ReviewReferenceProvider
{
    public function __construct(
        private ReferenceRepositoryInterface $referenceRepository,
    ) {
    }

    public function updateReferences(Review $review, string $locale, string $context): void
    {
        $referenceCollector = new ReferenceCollector(
            $this->referenceRepository,
            Review::RESOURCE_KEY,
            (string) $review->getId(),
            $locale,
            mb_substr($review->getName() ?? '', 0, 191),
            $context,
            ['id' => $review->getId(), 'locale' => $locale],
        );

        if ($clientImage = $review->getClientImage()) {
            $referenceCollector->addReference(MediaInterface::RESOURCE_KEY, (string) $clientImage->getId(), 'clientImage');
        }

        $referenceCollector->persistReferences();
        $this->referenceRepository->flush();
    }
}
