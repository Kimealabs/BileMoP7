<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
    private ?string $Firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers", "getUser"])]
    private ?string $Secondname = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUser"])]
    private ?string $Address = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUser"])]
    private ?string $Email = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $Client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->Firstname;
    }

    public function setFirstname(string $Firstname): self
    {
        $this->Firstname = $Firstname;

        return $this;
    }

    public function getSecondname(): ?string
    {
        return $this->Secondname;
    }

    public function setSecondname(string $Secondname): self
    {
        $this->Secondname = $Secondname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->Address;
    }

    public function setAddress(string $Address): self
    {
        $this->Address = $Address;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->Client;
    }

    public function setClient(?Client $Client): self
    {
        $this->Client = $Client;

        return $this;
    }
}
