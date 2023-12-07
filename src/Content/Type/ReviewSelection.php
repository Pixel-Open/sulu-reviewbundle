<?php

declare(strict_types=1);

namespace Pixel\ReviewBundle\Content\Type;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\ReviewBundle\Entity\Review;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class ReviewSelection extends SimpleContentType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct("review_selection", []);
    }

    /**
     * @return Review[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();
        if (empty($ids)) {
            return [];
        }
        $reviews = $this->entityManager->getRepository(Review::class)->findBy([
            "id" => $ids,
        ]);
        $idPositions = array_flip($ids);
        usort($reviews, function (Review $a, Review $b) use ($idPositions) {
            return $idPositions[$a->getId()] - $idPositions[$b->getId()];
        });
        return $reviews;
    }

    /**
     * @return array<string, array<int>|null>
     */
    public function getViewData(PropertyInterface $property): array
    {
        return [
            "ids" => $property->getValue(),
        ];
    }
}
