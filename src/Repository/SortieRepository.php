<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Sortie::class);
    }
    
    public function findSortiesACloturer() {
        $qb = $this->createQueryBuilder('sortie');
        $qb
            ->innerJoin('sortie.participants', 'participant')
            ->innerJoin('sortie.etat', 'etat')
            ->andWhere('etat.libelle != :etatCloturee')
            ->andWhere(':today > sortie.dateLimiteInscription')
            ->groupBy('sortie.id, sortie.nbInscriptionsMax')
            ->andHaving($qb->expr()->gte(
                'COUNT(participant)', 'sortie.nbInscriptionsMax'
            ))
            ->setParameter('etatCloturee', Etat::LABEL_CLOTUREE)
            ->setParameter('today', (new DateTime())->format('Y-m-d'))
        ;
        
        return $qb->getQuery()->getResult();
    }
    
    public function findSortiesAReouvrir() {
        $qb = $this->createQueryBuilder('sortie');
        $qb
            ->innerJoin('sortie.participants', 'participant')
            ->andWhere(':today <= sortie.dateLimiteInscription')
            ->setParameter('today', (new DateTime())->format('Y-m-d'))
            ->groupBy('sortie.id, sortie.nbInscriptionsMax')
            ->andHaving($qb->expr()->lt(
                'COUNT(participant)', 'sortie.nbInscriptionsMax'
            ))
        ;
        
        return $qb->getQuery()->getResult();
    }
    
    public function findSortiesACommencer() {
        return $this
            ->createQueryBuilder('sortie')
            ->join('sortie.etat', 'etat')
            ->andWhere('etat.libelle = :ouverte OR etat.libelle = :cloturee')
            ->andWhere(':today >= sortie.dateHeureDebut')
            ->setParameter('today', new DateTime())
            ->setParameter('ouverte', Etat::LABEL_OUVERTE)
            ->setParameter('cloturee', Etat::LABEL_CLOTUREE)
            ->getQuery()->getResult()
        ;
    }
    
    public function findSortiesATerminer() {
        return $this
            ->createQueryBuilder('sortie')
            ->join('sortie.etat', 'etat')
            ->andWhere('etat.libelle = :en_cours')
            ->andWhere(":maintenant >= DATE_ADD(sortie.dateHeureDebut, sortie.duree, 'MINUTE')")
            ->setParameter('en_cours', Etat::LABEL_EN_COURS)
            ->setParameter('maintenant', new DateTime())
            ->getQuery()->getResult()
        ;
    }
    
    public function findSortiesAHistoriser() {
        return $this
            ->createQueryBuilder('sortie')
            ->join('sortie.etat', 'etat')
            ->andWhere('etat.libelle = :en_cours')
            ->andWhere(":maintenant >= DATE_ADD(sortie.dateHeureDebut, 30, 'DAY')")
            ->setParameter('en_cours', Etat::LABEL_EN_COURS)
            ->setParameter('maintenant', new DateTime())
            ->getQuery()->getResult()
        ;
    }
    
    
    public function findAllActiveByCampus(Participant $participant) {
        
        return $this->createQueryBuilder('sortie')
                    ->orWhere('sortie.organisateur = :utilisateurConnectee and etat.libelle = :etatCre')
                    ->setParameter('etatCre', Etat::LABEL_CREEE)
                    ->setParameter('utilisateurConnectee', $participant)
                    ->join('sortie.etat', 'etat')
                    ->orWhere('etat.libelle = :etatPublie')
                    ->setParameter('etatPublie', Etat::LABEL_OUVERTE)
                    ->orWhere('etat.libelle = :etatCloture')
                    ->setParameter('etatCloture', Etat::LABEL_CLOTUREE)
                    ->orWhere('etat.libelle = :etatEnCours')
                    ->setParameter('etatEnCours', Etat::LABEL_EN_COURS)
//            todo
//              a voir si on la vire
                    ->orWhere('etat.libelle = :etatPassee')
                    ->setParameter('etatPassee', Etat::LABEL_PASSEE)
                    ->orWhere('etat.libelle = :etatAnnulee')
                    ->setParameter('etatAnnulee', Etat::LABEL_ANNULEE)
                    ->getQuery()
                    ->getResult()
        ;
    }
    
    public function save(Sortie $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);
        
        if($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function remove(Sortie $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);
        
        if($flush) {
            $this->getEntityManager()->flush();
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
