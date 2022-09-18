<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUsers", "getUser"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers", "getUser"])]
    #[Assert\NotBlank(message: "Firstname is required")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Firstname must be at least {{ limit }} characters", maxMessage: "Firstname cannot be longer than {{ limit }} characters")]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers", "getUser"])]
    #[Assert\NotBlank(message: "Secondname is required")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Secondname must be at least {{ limit }} characters", maxMessage: "Secondname cannot be longer than {{ limit }} characters")]
    private ?string $secondname = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUser"])]
    #[Assert\NotBlank(message: "Address is required")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Address must be at least {{ limit }} characters", maxMessage: "Address cannot be longer than {{ limit }} characters")]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUser"])]
    #[Assert\NotBlank(message: "Email is required")]
    #[Assert\Email(message: 'The email is not valid')]
    #[Assert\Length(min: 1, max: 255, minMessage: "Email must be at least {{ limit }} characters", maxMessage: "Email cannot be longer than {{ limit }} characters")]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'user')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[Groups(["getUsers"])]
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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getSecondname(): ?string
    {
        return $this->secondname;
    }

    public function setSecondname(string $secondname): self
    {
        $this->secondname = $secondname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
