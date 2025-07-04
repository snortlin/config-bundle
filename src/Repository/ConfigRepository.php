<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Snortlin\Bundle\ConfigBundle\Entity\AbstractConfig;

/**
 * @method AbstractConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractConfig[]    findAll()
 * @method AbstractConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,
                                string          $configClass)
    {
        parent::__construct($registry, $configClass);
    }

    public function findOneByKey(string $key): ?AbstractConfig
    {
        return $this->findOneBy([
            'key' => $key,
        ]);
    }

    public function persist(AbstractConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AbstractConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
