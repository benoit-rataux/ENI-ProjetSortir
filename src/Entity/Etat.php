<?php

namespace App\Entity;

use App\Repository\EtatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
#[UniqueEntity(fields: ['libelle'])]
class Etat {
    public const LABEL_CREEE    = 'Créée';
    public const LABEL_OUVERTE  = 'Ouverte';
    public const LABEL_CLOTUREE = 'Clôturée';
    public const LABEL_EN_COURS = 'Activité en cours';
    public const LABEL_PASSEE   = 'passée';
    public const LABEL_ANNULEE  = 'Annulée';
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(
        length: 50,
        unique: true
    )]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le libellé doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le libellé ne peut excéder {{ limit }} caractères',
    )]
    private ?string $libelle = null;
    
    #[ORM\OneToMany(mappedBy: 'etat', targetEntity: Sortie::class)]
    private Collection $sorties;
    
    public function __construct() {
        $this->sorties = new ArrayCollection();
    }
    
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getLibelle(): ?string {
        return $this->libelle;
    }
    
    public function setLibelle(string $libelle): self {
        $this->libelle = $libelle;
        
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
            $sorty->setEtat($this);
        }
        
        return $this;
    }
    
    public function removeSorty(Sortie $sorty): self {
        if($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if($sorty->getEtat() === $this) {
                $sorty->setEtat(null);
            }
        }
        
        return $this;
    }
}
