<?php

declare(strict_types=1);

namespace Pixel\ReviewBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\ReviewBundle\Admin\ReviewAdmin;
use Pixel\ReviewBundle\Domain\Event\ReviewRestoredEvent;
use Pixel\ReviewBundle\Entity\Review;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;

class ReviewTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    private TrashItemRepositoryInterface $trashItemRepository;
    private EntityManagerInterface $entityManager;
    private DoctrineRestoreHelperInterface $doctrineRestoreHelper;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        TrashItemRepositoryInterface   $trashItemRepository,
        EntityManagerInterface         $entityManager,
        DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        DomainEventCollectorInterface  $domainEventCollector
    ) {
        $this->trashItemRepository = $trashItemRepository;
        $this->entityManager = $entityManager;
        $this->doctrineRestoreHelper = $doctrineRestoreHelper;
        $this->domainEventCollector = $domainEventCollector;
    }

    public static function getResourceKey(): string
    {
        return Review::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        $clientImage = $resource->getClientImage();

        $data = [
            "name" => $resource->getName(),
            "date" => $resource->getDate(),
            "rating" => $resource->getRating(),
            "message" => $resource->getMessage(),
            "isFromGoogle" => $resource->isFromGoogle(),
            "clientImageId" => $clientImage ? $clientImage->getId() : null,
            "isActive" => $resource->isActive(),
            "defaultLocal" => $resource->getDefaultLocale(),
        ];

        return $this->trashItemRepository->create(
            Review::RESOURCE_KEY,
            (string)$resource->getId(),
            $resource->getName(),
            $data,
            null,
            $options,
            Review::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $data = $trashItem->getRestoreData();
        $reviewId = (int)$trashItem->getResourceId();
        $review = new Review();
        $review->setName($data['name']);
        $review->setDate(new \DateTimeImmutable($data['date']['date']));
        $review->setRating($data['rating']);
        $review->setMessage($data['message']);
        $review->setIsFromGoogle($data['isFromGoogle']);
        if (isset($data['clientImageId'])) {
            $review->setClientImage($this->entityManager->find(MediaInterface::class, $data['clientImageId']));
        }
        $review->setIsActive($data['isActive']);
        if (isset($data['defaultLocale'])) {
            $review->setDefaultLocale($data['defaultLocale']);
        }

        $this->domainEventCollector->collect(
            new ReviewRestoredEvent($review, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($review, $reviewId);
        return $review;
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(null, ReviewAdmin::REVIEW_EDIT_FORM_VIEW, [
            'id' => 'id',
        ]);
    }
}
