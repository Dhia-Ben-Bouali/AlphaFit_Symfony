<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Article::class)]
    private Collection $article;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?float $totale = null;

    #[ORM\OneToMany(mappedBy: 'id_commande', targetEntity: Lignecommande::class)]
    private Collection $lignecommandes;


    #[ORM\Column(type: "string")]
    private ?string $firstName = null;

    #[ORM\Column(type: "string")]
    private ?string $lastName = null;

    #[ORM\Column(type: "string")]
    private ?string $address = null;

    #[ORM\Column(type: "string")]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: "string")]
    private ?string $paymentType = null; // 'online' or 'delivery'

    #[ORM\Column(type: "boolean")]
    private bool $isValid = false; // Defaults to false

    public function __construct()
    {
        $this->article = new ArrayCollection();
        $this->lignecommandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, article>
     */
    public function getArticle(): Collection
    {
        return $this->article;
    }

    public function addArticle(article $article): static
    {
        if (!$this->article->contains($article)) {
            $this->article->add($article);
            $article->setCommande($this);
        }

        return $this;
    }

    public function removeArticle(article $article): static
    {
        if ($this->article->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getCommande() === $this) {
                $article->setCommande(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTotale(): ?float
    {
        return $this->totale;
    }

    public function setTotale(float $totale): static
    {
        $this->totale = $totale;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    // lastName property
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    // address property
    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    // phoneNumber property
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    // paymentType property
    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(string $paymentType): self
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    // isValid property
    public function getIsValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): self
    {
        $this->isValid = $isValid;
        return $this;
    }

    /**
     * @return Collection<int, Lignecommande>
     */
    public function getLignecommandes(): Collection
    {
        return $this->lignecommandes;
    }

    public function addLignecommande(Lignecommande $lignecommande): static
    {
        if (!$this->lignecommandes->contains($lignecommande)) {
            $this->lignecommandes->add($lignecommande);
            $lignecommande->setIdCommande($this);
        }

        return $this;
    }

    public function removeLignecommande(Lignecommande $lignecommande): static
    {
        if ($this->lignecommandes->removeElement($lignecommande)) {
            // set the owning side to null (unless already changed)
            if ($lignecommande->getIdCommande() === $this) {
                $lignecommande->setIdCommande(null);
            }
        }

        return $this;
    }
}
