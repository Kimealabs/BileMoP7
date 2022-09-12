<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getProducts"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getProducts"])]
    private ?string $Model = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getProducts"])]
    private ?string $Brand = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $CreatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $ReleaseDate = null;

    #[ORM\Column(length: 255)]
    private ?string $Display = null;

    #[ORM\Column(length: 255)]
    private ?string $FrontCamera = null;

    #[ORM\Column(length: 255)]
    private ?string $RearCamera = null;

    #[ORM\Column(length: 255)]
    private ?string $Processor = null;

    #[ORM\Column(length: 255)]
    private ?string $Price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->Model;
    }

    public function setModel(string $Model): self
    {
        $this->Model = $Model;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->Brand;
    }

    public function setBrand(string $Brand): self
    {
        $this->Brand = $Brand;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->ReleaseDate;
    }

    public function setReleaseDate(\DateTimeInterface $ReleaseDate): self
    {
        $this->ReleaseDate = $ReleaseDate;

        return $this;
    }

    public function getDisplay(): ?string
    {
        return $this->Display;
    }

    public function setDisplay(string $Display): self
    {
        $this->Display = $Display;

        return $this;
    }

    public function getFrontCamera(): ?string
    {
        return $this->FrontCamera;
    }

    public function setFrontCamera(string $FrontCamera): self
    {
        $this->FrontCamera = $FrontCamera;

        return $this;
    }

    public function getRearCamera(): ?string
    {
        return $this->RearCamera;
    }

    public function setRearCamera(string $RearCamera): self
    {
        $this->RearCamera = $RearCamera;

        return $this;
    }

    public function getProcessor(): ?string
    {
        return $this->Processor;
    }

    public function setProcessor(string $Processor): self
    {
        $this->Processor = $Processor;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->Price;
    }

    public function setPrice(string $Price): self
    {
        $this->Price = $Price;

        return $this;
    }
}
