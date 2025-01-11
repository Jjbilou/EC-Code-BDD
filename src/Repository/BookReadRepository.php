<?php

namespace App\Repository;

use App\Entity\BookRead;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookRead>
 */
class BookReadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookRead::class);
    }

    /**
     * Method to find all ReadBook entities by user_id
     * @param User $user
     * @param bool $readState
     * @return array
     */
    public function findByUser(User $user, bool $readState): array
    {
        // Permet de trouver les livres lu ou non lu d'un user
        return $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.is_read = :isRead')
            ->setParameter('user', $user)
            ->setParameter('isRead', $readState)
            ->orderBy('r.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllByUser(User $user): array
    {
        // Permet de trouver les livres lu et non lu d'un user
        return $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByNameContains(string $searchTerm): array
    {
        // Permet de trouver les livres lu et non lu contenant certains caractères
        return $this->createQueryBuilder('r')
            ->where('r.book_name LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('r.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
