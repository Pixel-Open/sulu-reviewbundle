<?php

declare(strict_types=1);

namespace Pixel\ReviewBundle\Content\Type;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\ReviewBundle\Entity\Review;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class SingleReviewSelection extends SimpleContentType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct("single_review_selection", null);
    }

    public function getContentData(PropertyInterface $property): ?Review
    {
        $id = $property->getValue();
        if (empty($id)) {
            return null;
        }
        return $this->entityManager->getRepository(Review::class)->find($id);
    }

    /**
     * @return array<string, int|null>
     */
    public function getViewData(PropertyInterface $property): array
    {
        return [
            "id" => $property->getValue(),
        ];
    }
}
