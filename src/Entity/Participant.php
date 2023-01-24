<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[UniqueEntity('pseudo')]
#[UniqueEntity(fields: ['mail'], message: 'There is already an account with this mail')]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(
        max: 180,
        maxMessage: 'la taille ne peut excéder 180 caractères',
    )]
    #[Assert\email(
        message: '{{value}} n\'est pas un email valide',
    )]
    private ?string $mail = null;
    
    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\Length(
        min: 8,
        max: 50,
        minMessage: 'Le mot de passe doit contenir au moins 8 caractères',
        maxMessage: 'Le mot de passe ne peut excéder 50 caractères',
    )]/**/
    #[Assert\Regex(
        pattern: '/^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9]+)$/',
        message: 'Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre',
        match: true,
    )]/**/
    private ?string $motPasse = null;
    
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut excéder {{ limit }} caractères',
    )]
    private ?string $nom = null;
    
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $prenom = null;
    
    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^\d{10}$/',
        message: 'Le numéros de téléphone doit contnir 10 chiffres',
        match: true,
    )]
    private ?string $telephone = null;
    
    #[ORM\Column]
    private ?bool $administrateur = null;
    
    #[ORM\Column]
    private ?bool $actif = null;
    
    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;
    
    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $sortiesEstInscrit;
    
    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class)]
    private Collection $sortiesOrganisees;
    
    #[ORM\Column(
        length: 25,
        unique: true,
    )]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 25,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut excéder {{ limit }} caractères',
    )]
    private ?string $pseudo = null;
    
    public function __construct() {
        $this->sortiesEstInscrit = new ArrayCollection();
        $this->sortiesOrganisees = new ArrayCollection();
    }
    
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getMail(): ?string {
        return $this->mail;
    }
    
    public function setMail(string $mail): self {
        $this->mail = $mail;
        
        return $this;
    }
    
    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string {
        return (string)$this->mail;
    }
    
    /**
     * @see UserInterface
     */
    public function getRoles(): array {
        return $this->administrateur ? ['ROLE_ADMIN'] : ['ROLE_USER'];
    }
    
    /**
     * @see UserInterface
     */
    public function eraseCredentials() {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
    
    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string {
        return $this->motPasse;
    }
    
    public function getMotPasse(): string {
        return $this->motPasse;
    }
    
    public function setMotPasse(string $motPasse): self {
        $this->motPasse = $motPasse;
        
        return $this;
    }
    
    public function getNom(): ?string {
        return $this->nom;
    }
    
    public function setNom(string $nom): self {
        $this->nom = $nom;
        
        return $this;
    }
    
    public function getPrenom(): ?string {
        return $this->prenom;
    }
    
    public function setPrenom(string $prenom): self {
        $this->prenom = $prenom;
        
        return $this;
    }
    
    public function getTelephone(): ?string {
        return $this->telephone;
    }
    
    public function setTelephone(?string $telephone): self {
        $this->telephone = $telephone;
        
        return $this;
    }
    
    public function isAdministrateur(): ?bool {
        return $this->administrateur;
    }
    
    public function setAdministrateur(bool $administrateur): self {
        $this->administrateur = $administrateur;
        
        return $this;
    }
    
    public function isActif(): ?bool {
        return $this->actif;
    }
    
    public function setActif(bool $actif): self {
        $this->actif = $actif;
        
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
     * @return Collection<int, Sortie>
     */
    public function getSortiesEstInscrit(): Collection {
        return $this->sortiesEstInscrit;
    }
    
    public function addSortiesEstInscrit(Sortie $sortiesEstInscrit): self {
        if(!$this->sortiesEstInscrit->contains($sortiesEstInscrit)) {
            $this->sortiesEstInscrit->add($sortiesEstInscrit);
        }
        
        return $this;
    }
    
    public function removeSortiesEstInscrit(Sortie $sortiesEstInscrit): self {
        $this->sortiesEstInscrit->removeElement($sortiesEstInscrit);
        
        return $this;
    }
    
    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrganisees(): Collection {
        return $this->sortiesOrganisees;
    }
    
    public function addSortiesOrganisee(Sortie $sortiesOrganisee): self {
        if(!$this->sortiesOrganisees->contains($sortiesOrganisee)) {
            $this->sortiesOrganisees->add($sortiesOrganisee);
            $sortiesOrganisee->setOrganisateur($this);
        }
        
        return $this;
    }
    
    public function removeSortiesOrganisee(Sortie $sortiesOrganisee): self {
        if($this->sortiesOrganisees->removeElement($sortiesOrganisee)) {
            // set the owning side to null (unless already changed)
            if($sortiesOrganisee->getOrganisateur() === $this) {
                $sortiesOrganisee->setOrganisateur(null);
            }
        }
        
        return $this;
    }
    
    public function getPseudo(): ?string {
        return $this->pseudo;
    }
    
    public function setPseudo(string $pseudo): self {
        $this->pseudo = $pseudo;
        
        return $this;
    }
}
