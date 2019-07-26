<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReviewRepository")
 */
class Review
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
    private $rating;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    // GET methods
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getRating(): ?int
    {
        return $this->rating;
    }
    public function getText(): ?string
    {
        return $this->text;
    }
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    // SET methods
    public function setRating(?int $rating)
    {
        $this->rating = $rating;
    }
    public function setText(?string $text)
    {
        $this->text = $text;
    }
    public function setProduct(?Product $product)
    {
        $this->product = $product;
    }
}
