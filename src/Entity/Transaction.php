<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amountOriginal = null;

    #[ORM\Column(length: 3)]
    private ?string $currencyOriginal = null;

    #[ORM\Column]
    private ?float $convertedAmount = null;

    #[ORM\Column]
    private ?float $conversionRate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    private ?Category $categoryId = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmountOriginal(): ?float
    {
        return $this->amountOriginal;
    }

    public function setAmountOriginal(float $amountOriginal): static
    {
        $this->amountOriginal = $amountOriginal;

        return $this;
    }

    public function getCurrencyOriginal(): ?string
    {
        return $this->currencyOriginal;
    }

    public function setCurrencyOriginal(string $currencyOriginal): static
    {
        $this->currencyOriginal = $currencyOriginal;

        return $this;
    }

    public function getConvertedAmount(): ?float
    {
        return $this->convertedAmount;
    }

    public function setConvertedAmount(float $convertedAmount): static
    {
        $this->convertedAmount = $convertedAmount;

        return $this;
    }

    public function getConversionRate(): ?float
    {
        return $this->conversionRate;
    }

    public function setConversionRate(float $conversionRate): static
    {
        $this->conversionRate = $conversionRate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCategoryId(): ?Category
    {
        return $this->categoryId;
    }

    public function setCategoryId(?Category $categoryId): static
    {
        $this->categoryId = $categoryId;

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
