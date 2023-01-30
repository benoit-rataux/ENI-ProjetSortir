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
        return $this
            ->createQueryBuilder('sortie')
            ->select('sortie.*, count(participant.*) as nb_inscrits')
            ->join('sortie.participants', 'participant')
            ->orWhere('nb_inscrits >= sortie.nbInscriptionsMax')
            ->orWhere(':today > sortie.dateLimiteInscription')
            ->setParameter('today', new DateTime())
            ->getQuery()->getResult()
        ;
    }
    
    public function findSortiesAReouvrir() {
        return $this
            ->createQueryBuilder('sortie')
            ->select('sortie.*, count(participant.*) as nb_inscrits')
            ->join('sortie.participants', 'participant')
            ->andWhere('nb_inscrits < sortie.nbInscriptionsMax')
            ->andWhere(':today < sortie.dateLimiteInscription')
            ->setParameter('today', new DateTime())
            ->getQuery()->getResult()
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


    public function findAllActiveByCampus(Participant $participant){

        return $this->createQueryBuilder('sortie')
            ->orWhere('sortie.organisateur = :utilisateurConnectee and etat.libelle = :etatCre')
            ->setParameter('etatCre',Etat::LABEL_CREEE)
            ->setParameter('utilisateurConnectee',$participant)
            ->join('sortie.etat','etat')
            ->orWhere('etat.libelle = :etatPublie')
            ->setParameter('etatPublie',Etat::LABEL_OUVERTE)
            ->orWhere('etat.libelle = :etatCloture')
            ->setParameter('etatCloture',Etat::LABEL_CLOTUREE)
            ->orWhere('etat.libelle = :etatEnCours')
            ->setParameter('etatEnCours',Etat::LABEL_EN_COURS)
//            todo
//              a voir si on la vire
            ->orWhere('etat.libelle = :etatPassee')
            ->setParameter('etatPassee',Etat::LABEL_PASSEE)
            ->orWhere('etat.libelle = :etatAnnulee')
            ->setParameter('etatAnnulee',Etat::LABEL_ANNULEE)
            ->getQuery()
            ->getResult()
            ;



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
