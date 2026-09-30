<?php

declare(strict_types=1);

namespace Pixel\ReviewBundle\Content\Type;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\ReviewBundle\Entity\Review;
use Sulu\Bundle\ReferenceBundle\Application\Collector\ReferenceCollectorInterface;
use Sulu\Bundle\ReferenceBundle\Infrastructure\Sulu\ContentType\ReferenceContentTypeInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class SingleReviewSelection extends SimpleContentType implements ReferenceContentTypeInterface
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

    public function getReferences(PropertyInterface $property, ReferenceCollectorInterface $referenceCollector, string $propertyPrefix = ''): void
    {
        $data = $property->getValue();
        if (!isset($data) || !is_int($data)) {
            return;
        }

        $referenceCollector->addReference(
            Review::RESOURCE_KEY,
            (string) $data,
            $propertyPrefix . $property->getName()
        );
    }
}
