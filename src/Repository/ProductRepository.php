<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

/**
     * Recherche avancée multi-critères
     *
     * @param array $filters Les critères de recherche
     * @return Product[]
     */
    public function search(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->where('p.active = true');

        // --- Filtre par nom 
        if (!empty($filters['name'])) {
            $qb->andWhere('LOWER(p.name) LIKE LOWER(:name)')
               ->setParameter('name', '%' . $filters['name'] . '%');
        }

        // --- Filtre par catégorie-
        if (!empty($filters['category'])) {
            $qb->andWhere('p.category = :category')
               ->setParameter('category', $filters['category']);
        }

        // --- Filtre par prix min
        if (!empty($filters['minPrice'])) {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $filters['minPrice']);
        }

        // --- Filtre par prix max
        if (!empty($filters['maxPrice'])) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $filters['maxPrice']);
        }

        // --- Filtre par pays 
        if (!empty($filters['country'])) {
            $qb->andWhere('LOWER(p.country) LIKE LOWER(:country)')
               ->setParameter('country', '%' . $filters['country'] . '%');
        }

        // --- Filtre par tag
        if (!empty($filters['tag'])) {
            $qb->andWhere('p.tag = :tag')
               ->setParameter('tag', $filters['tag']);
        }

        // --- Filtre en stock
        if (!empty($filters['inStock'])) {
            $qb->andWhere('p.stock > 0');
        }

        // Tri 
        $sortBy = $filters['sortBy'] ?? null;
        switch ($sortBy) {
            case 'price_asc':
                $qb->orderBy('p.price', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('p.price', 'DESC');
                break;
            case 'name_asc':
                $qb->orderBy('p.name', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('p.name', 'DESC');
                break;
            case 'oldest':
                $qb->orderBy('p.createdAt', 'ASC');
                break;
            default:
                $qb->orderBy('p.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

}
