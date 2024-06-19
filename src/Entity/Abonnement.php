<?php

namespace App\Entity;

use App\Repository\AbonnementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Pack;


#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'abonnement', targetEntity: User::class)]
    private Collection $abuser;

    #[ORM\ManyToOne(inversedBy: 'abonnements')]
    private ?Pack $npack = null;

    #[ORM\ManyToOne(inversedBy: 'adherents')]
    private ?User $coach = null;

    #[ORM\ManyToOne(inversedBy: 'patients')]
    private ?User $nutri = null;

    #[ORM\ManyToOne(inversedBy: 'abonnements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    public function __construct()
    {
        $this->abuser = new ArrayCollection();
    }

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $expirationDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAbuser(): Collection
    {
        return $this->abuser;
    }

    public function addAbuser(user $abuser): static
    {
        if (!$this->abuser->contains($abuser)) {
            $this->abuser->add($abuser);
            $abuser->setAbonnement($this);
        }

        return $this;
    }

    public function removeAbuser(User $abuser): static
    {
        if ($this->abuser->removeElement($abuser)) {
            // set the owning side to null (unless already changed)
            if ($abuser->getAbonnement() === $this) {
                $abuser->setAbonnement(null);
            }
        }

        return $this;
    }

    public function getNpack(): ?pack
    {
        return $this->npack;
    }

    public function setNpack(?pack $npack): static
    {
        $this->npack = $npack;

        return $this;
    }

    public function getCoach(): ?User
    {
        return $this->coach;
    }

    public function setCoach(?User $coach): static
    {
        $this->coach = $coach;

        return $this;
    }

    public function getNutri(): ?User
    {
        return $this->nutri;
    }

    public function setNutri(?User $nutri): static
    {
        $this->nutri = $nutri;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTimeInterface $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }
}