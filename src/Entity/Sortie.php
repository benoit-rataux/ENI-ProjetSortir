<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut excéder {{ limit }} caractères',
    )]
    private ?string $nom = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\DateTime]
    #[Assert\GreaterThan('today')]
    private ?\DateTimeInterface $dateHeureDebut = null;
    
    #[ORM\Column]
    #[Assert\Positive]
    private ?int $duree = null;
    
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\DateTime]
    #[Assert\GreaterThan(propertyPath: 'dateHeureDebut')]
    private ?\DateTimeInterface $dateLimiteInscription = null;
    
    #[ORM\Column]
    private ?int $nbInscriptionsMax = null;
    
    #[ORM\Column(type: Types::TEXT)]
    private ?string $infosSortie = null;
    
    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;
    
    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;
    
    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;
    
    #[ORM\ManyToMany(targetEntity: Participant::class, mappedBy: 'sortiesEstInscrit')]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $participants;
    
    #[ORM\ManyToOne(inversedBy: 'sortiesOrganisees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organisateur = null;
    
    public function __construct() {
        $this->participants = new ArrayCollection();
    }
    
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getNom(): ?string {
        return $this->nom;
    }
    
    public function setNom(string $nom): self {
        $this->nom = $nom;
        
        return $this;
    }
    
    public function getDateHeureDebut(): ?\DateTimeInterface {
        return $this->dateHeureDebut;
    }
    
    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): self {
        $this->dateHeureDebut = $dateHeureDebut;
        
        return $this;
    }
    
    public function getDuree(): ?int {
        return $this->duree;
    }
    
    public function setDuree(int $duree): self {
        $this->duree = $duree;
        
        return $this;
    }
    
    public function getDateLimiteInscription(): ?\DateTimeInterface {
        return $this->dateLimiteInscription;
    }
    
    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self {
        $this->dateLimiteInscription = $dateLimiteInscription;
        
        return $this;
    }
    
    public function getNbInscriptionsMax(): ?int {
        return $this->nbInscriptionsMax;
    }
    
    public function setNbInscriptionsMax(int $nbInscriptionsMax): self {
        $this->nbInscriptionsMax = $nbInscriptionsMax;
        
        return $this;
    }
    
    public function getInfosSortie(): ?string {
        return $this->infosSortie;
    }
    
    public function setInfosSortie(?string $infosSortie): self {
        $this->infosSortie = $infosSortie;
        
        return $this;
    }
    
    public function getEtat(): ?Etat {
        return $this->etat;
    }
    
    public function setEtat(?Etat $etat): self {
        $this->etat = $etat;
        
        return $this;
    }
    
    public function getLieu(): ?Lieu {
        return $this->lieu;
    }
    
    public function setLieu(?Lieu $lieu): self {
        $this->lieu = $lieu;
        
        return $this;
    }
    
    public function getCampus(): ?Campus {
        return $this->campus;
    }
    
    public function setCampus(?Campus $campus): self {
        $this->campus = $campus;
        
        return $this;
    }
    
    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection {
        return $this->participants;
    }
    
    public function addParticipant(Participant $participant): self {
        if(!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->addSortiesEstInscrit($this);
        }
        
        return $this;
    }
    
    public function removeParticipant(Participant $participant): self {
        if($this->participants->removeElement($participant)) {
            $participant->removeSortiesEstInscrit($this);
        }
        
        return $this;
    }
    
    public function getOrganisateur(): ?Participant {
        return $this->organisateur;
    }
    
    public function setOrganisateur(?Participant $organisateur): self {
        $this->organisateur = $organisateur;
        
        return $this;
    }
}
