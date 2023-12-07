<?php

namespace Pixel\ReviewBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Pixel\ReviewBundle\Entity\Review;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

class ReviewRepository extends EntityRepository implements DataProviderRepositoryInterface
{
    use DataProviderRepositoryTrait;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, new ClassMetadata(Review::class));
    }

    public function create(string $locale): Review
    {
        $review = new Review();
        $review->setDefaultLocale($locale);
        $review->setLocale($locale);
        return $review;
    }

    public function save(Review $review): void
    {
        $this->getEntityManager()->persist($review);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id, string $locale): ?Review
    {
        $review = $this->find($id);
        if (!$review) {
            return null;
        }
        $review->setLocale($locale);
        return $review;
    }

    /**
     * @return array<Review>
     */
    public function getLatestReviews(int $limit, string $locale): array
    {
        $query = $this->createQueryBuilder("r")
            ->select('r.name', 'r.rating', 'r.date', 'clientImage.id AS client_image_id', 't.message')
            ->leftJoin("r.translations", "t")
            ->leftJoin("r.clientImage", "clientImage")
            ->where("r.isActive = 1")
            ->andWhere("t.locale = :locale")
            ->orderBy("r.date", "desc")
            ->setMaxResults($limit)
            ->setParameter("locale", $locale);
        return $query->getQuery()->getResult();
    }

    /**
     * @param string $alias
     * @param string $locale
     */
    public function appendJoins(QueryBuilder $queryBuilder, $alias, $locale): void
    {
    }
}
