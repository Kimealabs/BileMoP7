<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getProducts"])]
    /**
     * @OA\Property(property="id", type="integer", example=456789)
     */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getProducts"])]
    /**
     * @OA\Property(property="model", type="string", example="Mi9T")
     */
    private ?string $model = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getProducts"])]
    /**
     * @OA\Property(property="brand", type="string", example="Xiaomi")
     */
    private ?string $brand = null;

    #[ORM\Column]
    /**
     * @OA\Property(property="createdAt", type="date-time", example="2021-09-22T07:18:55.920Z")
     */
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    /**
     * @OA\Property(property="releaseDate", type="date-time", example="2021-09-22T07:18:55.920Z")
     */
    private ?\DateTimeInterface $releaseDate = null;

    #[ORM\Column(length: 255)]
    /**
     * @OA\Property(property="display", type="string", example="6.04inch 1200x4000")
     */
    private ?string $display = null;

    #[ORM\Column(length: 255)]
    /**
     * @OA\Property(property="frontCamera", type="string", example="12MP")
     */
    private ?string $frontCamera = null;

    #[ORM\Column(length: 255)]
    /**
     * @OA\Property(property="rearCamera", type="string", example="12MP + 24MP")
     */
    private ?string $rearCamera = null;

    #[ORM\Column(length: 255)]
    /**
     * @OA\Property(property="processor", type="string", example="Octa-core Miktek 12")
     */
    private ?string $processor = null;

    #[ORM\Column(length: 255)]
    /**
     * @OA\Property(property="price", type="string", example="249.90")
     */
    private ?string $price = null;

    #[Groups(["getProducts"])]
    /**
     * @OA\Property(
     *  property="links",
     *  type="array",
     *  @OA\Items(example={"href": "/api/products/456789", "rel": "self", "method": "GET"})
     * )
     */

    private ?array $links = null;

    public function setLinks(array $links): self
    {
        $this->links = $links;
        return $this;
    }

    public function getLinks(): ?array
    {
        return $this->links;
    }

    public function removeLinks(): void
    {
        unset($this->links);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTimeInterface $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getDisplay(): ?string
    {
        return $this->display;
    }

    public function setDisplay(string $display): self
    {
        $this->display = $display;

        return $this;
    }

    public function getFrontCamera(): ?string
    {
        return $this->frontCamera;
    }

    public function setFrontCamera(string $frontCamera): self
    {
        $this->frontCamera = $frontCamera;

        return $this;
    }

    public function getRearCamera(): ?string
    {
        return $this->rearCamera;
    }

    public function setRearCamera(string $rearCamera): self
    {
        $this->rearCamera = $rearCamera;

        return $this;
    }

    public function getProcessor(): ?string
    {
        return $this->processor;
    }

    public function setProcessor(string $processor): self
    {
        $this->processor = $processor;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }
}
