<?php

declare(strict_types=1);

namespace Pixel\ReviewBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Pixel\ReviewBundle\Common\DoctrineListRepresentationFactory;
use Pixel\ReviewBundle\Domain\Event\ReviewCreatedEvent;
use Pixel\ReviewBundle\Domain\Event\ReviewModifiedEvent;
use Pixel\ReviewBundle\Domain\Event\ReviewRemovedEvent;
use Pixel\ReviewBundle\Entity\Review;
use Pixel\ReviewBundle\Repository\ReviewRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("review")
 */
class ReviewController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use RequestParametersTrait;

    private DoctrineListRepresentationFactory $doctrineListRepresentationFactory;
    private EntityManagerInterface $entityManager;
    private MediaManagerInterface $mediaManager;
    private ReviewRepository $reviewRepository;
    private TrashManagerInterface $trashManager;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        EntityManagerInterface $entityManager,
        MediaManagerInterface $mediaManager,
        ReviewRepository $reviewRepository,
        TrashManagerInterface $trashManager,
        DomainEventCollectorInterface $domainEventCollector,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->entityManager = $entityManager;
        $this->mediaManager = $mediaManager;
        $this->reviewRepository = $reviewRepository;
        $this->trashManager = $trashManager;
        $this->domainEventCollector = $domainEventCollector;
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $locale = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Review::RESOURCE_KEY,
            [],
            [
                'locale' => $locale,
            ]
        );
        return $this->handleView($this->view($listRepresentation));
    }

    protected function create(Request $request): Review
    {
        return $this->reviewRepository->create((string)$this->getLocale($request));
    }

    protected function load(int $id, Request $request, string $defaultLocale = null): ?Review
    {
        return $this->reviewRepository->findById($id, ($defaultLocale) ? $defaultLocale : (string)$this->getLocale($request));
    }

    protected function save(Review $review): void
    {
        $this->reviewRepository->save($review);
    }

    public function getAction(int $id, Request $request): Response
    {
        $review = $this->load($id, $request);
        if (!$review) {
            throw new NotFoundHttpException();
        }

        if (!$review->getName() && $review->getDefaultLocale()) {
            $request->setMethod($review->getDefaultLocale());
            $review = $this->load($id, $request, $review->getDefaultLocale());
        }

        return $this->handleView($this->view($review));
    }

    public function putAction(Request $request, int $id): Response
    {
        $review = $this->load($id, $request);
        if (!$review) {
            throw new NotFoundHttpException();
        }
        $data = $request->request->all();
        $this->mapDataToEntity($data, $review, $request);
        $this->domainEventCollector->collect(
            new ReviewModifiedEvent($review, $data)
        );
        $this->save($review);
        return $this->handleView($this->view($review));
    }

    /**
     * @param array<mixed> $data
     * @throws \Sulu\Bundle\CategoryBundle\Exception\CategoryIdNotFoundException
     */
    protected function mapDataToEntity(array $data, Review $entity, Request $request): void
    {
        $clientImageId = $data['clientImage']['id'] ?? null;
        $isActive = $data['isActive'] ?? null;

        $entity->setName($data['name']);
        $entity->setDate(new \DateTimeImmutable($data['date']));
        $entity->setRating($data['rating']);
        $entity->setMessage($data['message']);
        $entity->setClientImage($clientImageId ? $this->mediaManager->getEntityById($clientImageId) : null);
        $entity->setIsActive($isActive);
    }

    public function postAction(Request $request): Response
    {
        $review = $this->create($request);
        $data = $request->request->all();
        $this->mapDataToEntity($data, $review, $request);
        $this->domainEventCollector->collect(
            new ReviewCreatedEvent($review, $data)
        );
        $this->save($review);
        return $this->handleView($this->view($review, 201));
    }

    public function deleteAction(int $id): Response
    {
        $review = $this->entityManager->getRepository(Review::class)->find($id);
        $reviewName = $review->getName();
        if ($review) {
            $this->trashManager->store(Review::RESOURCE_KEY, $review);
            $this->entityManager->remove($review);
            $this->domainEventCollector->collect(
                new ReviewRemovedEvent($id, $reviewName)
            );
        }
        $this->entityManager->flush();
        return $this->handleView($this->view(null, 204));
    }

    public function getSecurityContext()
    {
        return Review::SECURITY_CONTEXT;
    }

    /**
     * @Rest\Post("/reviews/{id}")
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityNotFoundException
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);
        $locale = $this->getRequestParameter($request, 'locale', true);
        try {
            switch ($action) {
                case 'enable':
                    $item = $this->entityManager->getRepository(Review::class)->find($id);
                    $item->setLocale($locale);
                    $item->setIsactive(true);
                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                    break;
                case 'disable':
                    $item = $this->entityManager->getRepository(Review::class)->find($id);
                    $item->setLocale($locale);
                    $item->setIsActive(false);
                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                    break;
                default:
                    throw new BadRequestException(sprintf('Unknown action %s', $action));
            }
        } catch (RestException $exception) {
            $view = $this->view($exception->toArray(), 400);
            return $this->handleView($view);
        }
        return $this->handleView($this->view($item));
    }
}
