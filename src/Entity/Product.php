<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $sku;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $individualDiscountPercentage;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $basePrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $specialPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $globalDiscountPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $noTaxSpecialPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $noTaxGlobalDiscountPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $taxPrice;

    /**
     * @ORM\Column(type="text")
     */
    private $imageURL;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reviewCount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reviewSum;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=1, nullable=true)
     */
    private $reviewAverageScore;

    // GET methods
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function getSKU(): ?int
    {
        return $this->sku;
    }
    public function getStatus(): ?bool
    {
        return $this->status;
    }
    public function getIndividualDiscountPercentage(): ?int
    {
        return $this->individualDiscountPercentage;
    }
    public function getBasePrice(): ?float
    {
        return $this->basePrice;
    }
    public function getSpecialPrice(): ?float
    {
        return $this->specialPrice;
    }
    public function getGlobalDiscountPrice(): ?float
    {
        return $this->globalDiscountPrice;
    }
    public function getNoTaxSpecialPrice(): ?float
    {
        return $this->noTaxSpecialPrice;
    }
    public function getNoTaxGlobalDiscountPrice(): ?float
    {
        return $this->noTaxGlobalDiscountPrice;
    }
    public function getTaxPrice(): ?float
    {
        return $this->taxPrice;
    }
    public function getImageURL(): ?string
    {
        return $this->imageURL;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function getReviewCount(): ?int
    {
        return $this->reviewCount;
    }
    public function getReviewSum(): ?int
    {
        return $this->reviewSum;
    }
    public function getReviewAverageScore(): ?float
    {
        return $this->reviewAverageScore;
    }

    // SET methods
    public function setName(?string $name)
    {
        $this->name = $name;
    }
    public function setSKU(?int $sku)
    {
        $this->sku = $sku;
    }
    public function setStatus(?bool $status)
    {
        $this->status = $status;
    }
    public function setIndividualDiscountPercentage(?int $individualDiscountPercentage)
    {
        $this->individualDiscountPercentage = $individualDiscountPercentage;
    }
    public function setBasePrice(?float $basePrice)
    {
        $this->basePrice = $basePrice;
    }
    public function setSpecialPrice(?float $specialPrice)
    {
        $this->specialPrice = $specialPrice;
    }
    public function setGlobalDiscountPrice(?float $globalDiscountPrice)
    {
        $this->globalDiscountPrice = $globalDiscountPrice;
    }
    public function setNoTaxSpecialPrice(?float $noTaxSpecialPrice)
    {
        $this->noTaxSpecialPrice = $noTaxSpecialPrice;
    }
    public function setNoTaxGlobalDiscountPrice(?float $noTaxGlobalDiscountPrice)
    {
        $this->noTaxGlobalDiscountPrice = $noTaxGlobalDiscountPrice;
    }
    public function setTaxPrice(?float $taxPrice)
    {
        $this->taxPrice = $taxPrice;
    }
    public function setImageURL(?string $imageURL)
    {
        $this->imageURL = $imageURL;
    }
    public function setDescription(?string $description)
    {
        $this->description = $description;
    }
    public function setReviewCount(?int $reviewCount)
    {
        $this->reviewCount = $reviewCount;
    }
    public function setReviewSum(?int $reviewSum)
    {
        $this->reviewSum = $reviewSum;
    }
    public function setReviewAverageScore(?float $reviewAverageScore)
    {
        $this->reviewAverageScore = $reviewAverageScore;
    }

    // Calculates and returns average score of review ratings
    public function countReviewAverageScore(): ?float
    {
        if (empty($this->getReviewSum()) || empty($this->getReviewCount()))
        {
            return NULL;
        }
        else
        {
            return $this->getReviewSum() / $this->getReviewCount();
        }
    }

    // Calculates and returns price
    public function countPrice(?int $taxPercentage, ?int $individualDiscountPercentage, ?int $globalDiscountPercentage): ?float
    {
        if (empty($this->getBasePrice()))
        {
            return NULL;
        }
        else
        {
            $tax = 0;
            $individualDiscount = 0;
            $globalDiscount = 0;
            if (!empty($taxPercentage))
            {
                $tax = $taxPercentage / 100 * $this->getBasePrice();
            }
            if (!empty($individualDiscountPercentage))
            {
                $individualDiscount = $individualDiscountPercentage / 100 * ($this->getBasePrice() + $tax);
            }
            else if (!empty($globalDiscountPercentage))
            {
                $globalDiscount = $globalDiscountPercentage / 100 * ($this->getBasePrice() + $tax);
            }
            return $this->getBasePrice() + $tax - $individualDiscount - $globalDiscount;
        }   
    }
}
