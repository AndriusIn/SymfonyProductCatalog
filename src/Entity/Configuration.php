<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConfigurationRepository")
 */
class Configuration
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $taxPercentage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $taxInclusionFlag;

    /**
     * @ORM\Column(type="integer")
     */
    private $globalDiscountPercentage;

    // GET methods
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTaxPercentage(): ?int
    {
        return $this->taxPercentage;
    }
    public function getTaxInclusionFlag(): ?bool
    {
        return $this->taxInclusionFlag;
    }
    public function getGlobalDiscountPercentage(): ?int
    {
        return $this->globalDiscountPercentage;
    }

    // SET methods
    public function setTaxPercentage(?int $taxPercentage)
    {
        $this->taxPercentage = $taxPercentage;
    }
    public function setTaxInclusionFlag(?bool $taxInclusionFlag)
    {
        $this->taxInclusionFlag = $taxInclusionFlag;
    }
    public function setGlobalDiscountPercentage(?int $globalDiscountPercentage)
    {
        $this->globalDiscountPercentage = $globalDiscountPercentage;
    }
}
