<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $star1 = null;

    #[ORM\Column(nullable: true)]
    private ?bool $star2 = null;

    #[ORM\Column(nullable: true)]
    private ?bool $star3 = null;

    #[ORM\Column(nullable: true)]
    private ?bool $star4 = null;

    #[ORM\Column(nullable: true)]
    private ?bool $star5 = null;

    #[ORM\OneToOne(inversedBy: 'avis', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isStar1(): ?bool
    {
        return $this->star1;
    }

    public function setStar1(?bool $star1): static
    {
        $this->star1 = $star1;

        return $this;
    }

    public function isStar2(): ?bool
    {
        return $this->star2;
    }

    public function setStar2(?bool $star2): static
    {
        $this->star2 = $star2;

        return $this;
    }

    public function isStar3(): ?bool
    {
        return $this->star3;
    }

    public function setStar3(?bool $star3): static
    {
        $this->star3 = $star3;

        return $this;
    }

    public function isStar4(): ?bool
    {
        return $this->star4;
    }

    public function setStar4(?bool $star4): static
    {
        $this->star4 = $star4;

        return $this;
    }

    public function isStar5(): ?bool
    {
        return $this->star5;
    }

    public function setStar5(?bool $star5): static
    {
        $this->star5 = $star5;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }
}
