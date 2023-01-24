<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CampusRepository::class)]
#[UniqueEntity(fields: ['nom'])]
class Campus {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(
        length: 180,
        unique: true,
    )]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 180,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut excéder {{ limit }} caractères',
    )]
    private ?string $nom = null;
    
    #[ORM\OneToMany(mappedBy: 'campus', targetEntity: Sortie::class)]
    private Collection $sorties;
    
    #[ORM\OneToMany(mappedBy: 'campus', targetEntity: Participant::class)]
    private Collection $participants;
    
    public function __construct() {
        $this->sorties      = new ArrayCollection();
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
    
    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection {
        return $this->sorties;
    }
    
    public function addSorty(Sortie $sorty): self {
        if(!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->setCampus($this);
        }
        
        return $this;
    }
    
    public function removeSorty(Sortie $sorty): self {
        if($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if($sorty->getCampus() === $this) {
                $sorty->setCampus(null);
            }
        }
        
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
            $participant->setCampus($this);
        }
        
        return $this;
    }
    
    public function removeParticipant(Participant $participant): self {
        if($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if($participant->getCampus() === $this) {
                $participant->setCampus(null);
            }
        }
        
        return $this;
    }
}
