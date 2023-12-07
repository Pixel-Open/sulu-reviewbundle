<?php

declare(strict_types=1);

namespace Pixel\ReviewBundle\Content;

use JMS\Serializer\Annotation as Serializer;
use Pixel\ReviewBundle\Entity\Review;
use Sulu\Component\SmartContent\ItemInterface;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class ReviewDataItem implements ItemInterface
{
    private Review $entity;

    public function __construct(Review $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getId(): string
    {
        return (string)$this->entity->getId();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getTitle(): string
    {
        return (string)$this->entity->getName();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getImage(): ?string
    {
        return null;
    }

    public function getResource(): Review
    {
        return $this->entity;
    }
}
