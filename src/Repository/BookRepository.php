<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findPaginatedBooks(?string $search, int $page, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('b');

        if ($search) {
            $queryBuilder->andWhere('b.title LIKE :search OR b.author LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy('b.id', 'DESC');

        $maxResultCount = $this->countBooks($search);

        return [
            'books' => $queryBuilder->getQuery()->getResult(),
            'total' => $maxResultCount,
            'pages' => round($maxResultCount / $limit),
        ];
    }

    private function countBooks(?string $search): int
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)');

        if ($search) {
            $queryBuilder->andWhere('b.title LIKE :search OR b.author LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
