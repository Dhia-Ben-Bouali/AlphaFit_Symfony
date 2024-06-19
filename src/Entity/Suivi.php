<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\SuiviRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SuiviRepository::class)]
class Suivi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plan_coach = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plan_nutritionniste = null;

    /**
     * @Assert\Callback
     */
    public function validatePlans(ExecutionContextInterface $context): void
    {
        if ($this->plan_coach === null && $this->plan_nutritionniste === null) {
            $context->buildViolation('Choose either plan_coach or plan_nutritionniste')
                ->atPath('plan_coach')
                ->addViolation();
        }
    }

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message:"Title is required")]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $end = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message:"Description is required")]
    private ?string $description = null;


    #[ORM\Column]
    private ?bool $all_day = null;

    #[ORM\Column(length: 7)]
    private ?string $background_colar = null;

    #[ORM\Column(length: 7)]
    #[Assert\NotBlank(message:"Border color is required")]
    private ?string $border_color = null;

    #[ORM\Column(length: 7)]
    #[Assert\NotBlank(message:"Text color is required")]
    private ?string $text_color = null;

    #[ORM\ManyToOne(inversedBy: 'suivis')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlanCoach(): ?string
    {
        return $this->plan_coach;
    }

    public function setPlanCoach(?string $plan_coach): static
    {
        $this->plan_coach = $plan_coach;

        return $this;
    }

    public function getPlanNutritionniste(): ?string
    {
        return $this->plan_nutritionniste;
    }

    public function setPlanNutritionniste(?string $plan_nutritionniste): static
    {
        $this->plan_nutritionniste = $plan_nutritionniste;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isAllDay(): ?bool
    {
        return $this->all_day;
    }

    public function setAllDay(bool $all_day): static
    {
        $this->all_day = $all_day;

        return $this;
    }

    public function getBackgroundColar(): ?string
    {
        return $this->background_colar;
    }

    public function setBackgroundColar(string $background_colar): static
    {
        $this->background_colar = $background_colar;

        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->border_color;
    }

    public function setBorderColor(string $border_color): static
    {
        $this->border_color = $border_color;

        return $this;
    }

    public function getTextColor(): ?string
    {
        return $this->text_color;
    }

    public function setTextColor(string $text_color): static
    {
        $this->text_color = $text_color;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
