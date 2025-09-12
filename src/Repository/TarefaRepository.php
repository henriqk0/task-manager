<?php

namespace App\Repository;

use App\Entity\Tarefa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tarefa>
 */
class TarefaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tarefa::class);
    }

    public function totalTarefasApresentadas(): ?int
    {
        return $this->createQueryBuilder('t')
        ->select('COUNT(t.id)')
        ->getQuery()
        ->getSingleScalarResult();
    }

    /**
     * @return Tarefa[]
     */
    public function alterarOrdem(int $ordemAntes, int $ordemDepois)
    {
        $entityManager = $this->getEntityManager();

        ($ordemAntes < $ordemDepois)
        ?
        $query = $entityManager->createQuery(
                'SELECT t
                FROM App\Entity\Tarefa t
                WHERE t.ordemDaApresentacao >= :ordemAntes AND t.ordemDaApresentacao <= :ordemDepois
                ORDER BY t.ordemDaApresentacao ASC'
            )->setParameter('ordemAntes', $ordemAntes)
            ->setParameter('ordemDepois', $ordemDepois)
        :
        $query = $entityManager->createQuery(
            'SELECT t
            FROM App\Entity\Tarefa t
            WHERE t.ordemDaApresentacao <= :ordemAntes AND t.ordemDaApresentacao >= :ordemDepois
            ORDER BY t.ordemDaApresentacao ASC'
        )->setParameter('ordemAntes', $ordemAntes)
        ->setParameter('ordemDepois', $ordemDepois);

        return $query->getResult();
    }

    /**
     * @return Tarefa[]
     */
    public function alterarOrdemPosteriores(int $ordemDaApresentacao)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT t
            FROM App\Entity\Tarefa t
            WHERE t.ordemDaApresentacao > :ordemDaApresentacao
            ORDER BY t.ordemDaApresentacao ASC'
        )->setParameter('ordemDaApresentacao', $ordemDaApresentacao);

        return $query->getResult();
    }
//    /**
//     * @return Tarefa[] Returns an array of Tarefa objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Tarefa
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
