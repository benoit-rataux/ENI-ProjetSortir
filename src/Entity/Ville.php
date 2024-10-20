<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville {
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
    
    #[ORM\Column(length: 15)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 15,
        minMessage: 'Le code postal doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le code postal ne peut excéder {{ limit }} caractères',
    )]
    private ?string $codePostal = null;
    
    #[ORM\OneToMany(mappedBy: 'ville', targetEntity: Lieu::class)]
    private Collection $lieux;
    
    public function __construct() {
        $this->lieux = new ArrayCollection();
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
    
    public function getCodePostal(): ?string {
        return $this->codePostal;
    }
    
    public function setCodePostal(string $codePostal): self {
        $this->codePostal = $codePostal;
        
        return $this;
    }
    
    /**
     * @return Collection<int, Lieu>
     */
    public function getLieux(): Collection {
        return $this->lieux;
    }
    
    public function addLieux(Lieu $lieux): self {
        if(!$this->lieux->contains($lieux)) {
            $this->lieux->add($lieux);
            $lieux->setVille($this);
        }
        
        return $this;
    }
    
    public function removeLieux(Lieu $lieux): self {
        if($this->lieux->removeElement($lieux)) {
            // set the owning side to null (unless already changed)
            if($lieux->getVille() === $this) {
                $lieux->setVille(null);
            }
        }
        
        return $this;
    }
}
