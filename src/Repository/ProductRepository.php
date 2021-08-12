<?php

namespace App\Repository;

use App\Entity\Product;
use App\Data\SearchData;
use App\Entity\OrderDetails;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */

    public function getCountry($classification)
    {
        $results = $this->createQueryBuilder('p')
            ->select('c.country')
            ->innerJoin('p.country', 'c')
            ->andWhere('p.classification = :classisication')
            ->setParameter('classisication', $classification)
            ->andWhere('p.stock > 0')
            ->orderBy('c.country', 'ASC')
            ->groupBy('c.country')
            ->getQuery()
            ->getResult();

        return $this->finalArray($results);
    }
    public function getBrands($classification)
    {
        $results = $this->createQueryBuilder('p')
            ->select('p.brand')
            ->andWhere('p.classification = :classisication')
            ->setParameter('classisication', $classification)
            ->andWhere('p.stock > 0')
            ->orderBy('p.brand', 'ASC')
            ->groupBy('p.brand')
            ->getQuery()
            ->getResult();
        return $this->finalArray($results);
    }
    public function getMaxPrice($classification)
    {
        return  $this->createQueryBuilder('p')
            ->select('MAX(p.price)')
            ->andWhere('p.classification = :classisication')
            ->setParameter('classisication', $classification)
            ->andWhere('p.stock > 0')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getMinPrice($classification)
    {
        return  $this->createQueryBuilder('p')
            ->select('MIN(p.price)')
            ->andWhere('p.classification = :classisication')
            ->setParameter('classisication', $classification)
            ->andWhere('p.stock > 0')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getMaxCapacity($classification)
    {
        return  $this->createQueryBuilder('p')
            ->select('MAX(p.capacity)')
            ->andWhere('p.classification = :classisication')
            ->setParameter('classisication', $classification)
            ->andWhere('p.stock > 0')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getMinCapacity($classification)
    {
        return  $this->createQueryBuilder('p')
            ->select('MIN(p.capacity)')
            ->andWhere('p.classification = :classisication')
            ->setParameter('classisication', $classification)
            ->andWhere('p.stock > 0')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getCategories($classification)
    {
        $results = $this->createQueryBuilder('p')
            ->select('p.category')
            ->andWhere('p.classification = :classisication')
            ->setParameter('classisication', $classification)
            ->andWhere('p.stock > 0')
            ->orderBy('p.category', 'ASC')
            ->groupBy('p.category')
            ->getQuery()
            ->getResult();
        return $this->finalArray($results);
    }
    public function finalArray($results)
    {
        $finalArray = [];
        foreach ($results as $result => $value) {
            $finalArray[$value[key($value)]] = $value[key($value)];
        }
        return $finalArray;
    }


    /**
     * Récupère les cartes en lien avec une recherche
     * @return Product[]
     */
    public function findSearch(SearchData $search, $classification): array
    {
        $query = $this
            ->createQueryBuilder('p')
            ->select('p')
            ->innerJoin('p.country', 'c')
            ->andWhere('p.classification = :classification')
            ->setParameter('classification', $classification)
            ->andWhere('p.stock > 0');

        if (!empty($search->q)) {
            $query = $query
                ->andWhere('p.title LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }

        if (!empty($search->maxPrice)) {
            $query = $query
                ->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $search->maxPrice);
        }
        if (!empty($search->minPrice)) {
            $query = $query
                ->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $search->minPrice);
        }
        if (!empty($search->maxCapacity)) {
            $query = $query
                ->andWhere('p.capacity <= :maxCapacity')
                ->setParameter('maxCapacity', $search->maxCapacity);
        }
        if (!empty($search->minCapacity)) {
            $query = $query
                ->andWhere('p.capacity >= :minCapacity')
                ->setParameter('minCapacity', $search->minCapacity);
        }
        if (!empty($search->brand)) {
            $query = $query
                ->andWhere('p.brand = :brand')
                ->setParameter('brand', $search->brand);
        }
        if (!empty($search->category)) {
            $query = $query
                ->andWhere('p.category = :category')
                ->setParameter('category', $search->category);
        }
        if (!empty($search->country)) {
            $query = $query
                ->andWhere('c.country = :country')
                ->setParameter('country', $search->country);
        }
        if (!empty($_GET['order'])) {
            switch ($_GET['order']) {
                case 'title':
                    $query = $query->orderBy('p.title', 'ASC');
                    break;
                case 'capacity':
                    $query = $query->orderBy('p.capacity', 'ASC');
                    break;
                case 'price':
                    $query = $query->orderBy('p.price', 'ASC');
                    break;
                case 'brand':
                    $query = $query->orderBy('p.brand', 'ASC');
                    break;
                case 'category':
                    $query = $query->orderBy('p.category', 'ASC');
                    break;
                default:
                    $query = $query->orderBy('p.title', 'ASC');
                    break;
            }
        } else {
            $query = $query->orderBy('p.price', 'ASC');
        }
        return $query->getQuery()->getResult();
    }

    /**
     * Récupère les cartes en lien avec une recherche
     * @return Product[]
     */
    public function search($search): array
    {
        $query = $this
            ->createQueryBuilder('p')
            ->select('p')
            ->innerJoin('p.country', 'c');

        if (!empty($search)) {
            $query = $query
                ->orWhere('p.title LIKE :q')
                ->setParameter('q', "%{$search}%")
                ->orWhere('p.brand LIKE :q')
                ->setParameter('q', "%{$search}%")
                ->orWhere('p.category LIKE :q')
                ->setParameter('q', "%{$search}%")
                ->orWhere('p.description LIKE :q')
                ->setParameter('q', "%{$search}%")
                ->andWhere('p.stock > 0');
        }
        return $query->getQuery()->getResult();
    }

    /**
     * Récupère les cartes en lien avec une recherche
     * @return Product[]
     */
    public function findSuggestion($category, $id): array
    {
        $query = $this
            ->createQueryBuilder('p')
            ->select('p')
            ->innerJoin('p.country', 'c')
            ->orderBy('RAND()')
            ->setMaxResults(3)
            ->andWhere('p.stock > 0');

        if (!empty($category)) {
            $query = $query
                ->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }
        if (!empty($id)) {
            $query = $query
                ->andWhere('p.id != :id')
                ->setParameter('id', $id);
        }
        return $query->getQuery()->getResult();
    }

    /**
     * @return Product[] Returns an array of Product
     */

    public function findLastProduct()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.updatedAt', 'DESC')
            ->andWhere('p.stock > 0')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
    }
    /**
     * @return Product[] Returns an array of Product
     */
    public function findPopProduct(int $nbrProduct)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin(OrderDetails::class, 'o', Join::WITH, 'o.product = p')
            ->andWhere('p.stock > 0')
            ->orderBy('COUNT(o.product)', 'DESC')
            ->groupBy('o.product')
            ->setMaxResults($nbrProduct)
            ->getQuery()
            ->getResult();
    }
}
