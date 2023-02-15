<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use function PHPUnit\Framework\isEmpty;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws \Exception
     */
    public function findAllWithFilters (
        Site        $site,
        string      $filtreNom,
        bool        $filtreDates,
        \DateTime   $dateMin,
        \DateTime   $dateMax,
        Participant $utilisateur,
        bool        $cocheOrganisateur,
        bool        $cocheInscrit,
        bool        $cocheNonInscrit,
        bool        $cochePassees
    ): array {

        $queryBuilder = $this->createQueryBuilder('sortie');
        //test si site existant
        if ($site->getId() > 0) {
            $queryBuilder->andWhere('sortie.site = :site')
                         ->setParameter('site', $site);
        }
        if ($filtreNom !== '') {
            $queryBuilder->andWhere('sortie.nom like :nom')
                         ->setParameter('nom', '%'.$filtreNom.'%');
        }

        if ($filtreDates) {
            $queryBuilder->andWhere('sortie.dateHeureDeb >= :dateMin')
                         ->setParameter('dateMin', $dateMin)
                         ->andWhere('sortie.dateHeureDeb <= :dateMax')
                         ->setParameter('dateMax', $dateMax);
        }

        $querySorties = $queryBuilder->orderBy('sortie.dateHeureDeb', 'ASC')
//          ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;

        //Gestion des cases à cocher par parcours du tableau de sorties issu de la requête
        // pour construire le tableau résultat
        if ($cocheOrganisateur || $cocheInscrit || $cocheNonInscrit || $cochePassees) {

            $sorties = new ArrayCollection();

            if ($cocheOrganisateur) {
                foreach ($querySorties as $sortie) {
                    if ($sortie->getOrganisateur() == $utilisateur) {
                        $sorties->add($sortie);
                    }
                }
            }
            if ($cocheInscrit) {
                foreach ($querySorties as $sortie) {
                    if (($sortie->getParticipants()->contains($utilisateur))
                        && !$sorties->contains($sortie)) {
                       $sorties->add($sortie);
                    }
                }
            }
            if ($cocheNonInscrit) {
                foreach ($querySorties as $sortie) {
                    if ( !($sortie->getParticipants()->contains($utilisateur))
                        && !$sorties->contains($sortie)) {
                        $sorties->add($sortie);
                    }
                }
            }
            if ($cochePassees) {
                $now = new \DateTime('now');
                foreach ($querySorties as $sortie) {
                    $dateDebSortieStr = $sortie->getDateHeureDeb()->format('Y-m-d H:i:s');
                    $dateDebSortieSec = strtotime($dateDebSortieStr) + ($sortie->getDuree() * 60);
                    $dateFinSortie = new \DateTime(date("Y-m-d H:i:s", $dateDebSortieSec));
                    if ($dateFinSortie < $now
                        && !$sorties->contains($sortie)) {
                        $sorties->add($sortie);
                    }
                }
            }
            $tabSorties = array();
            if (! empty($sorties)) {
                foreach ($sorties as $sortie) {
                    $tabSorties[] = $sortie;
                }
            }
            return $tabSorties;
        }
        else {
            return $querySorties;
        }
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
