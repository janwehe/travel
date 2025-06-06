<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'json')]
    private array $maps = [];

    #[ORM\Column(type: 'json')]
    private array $youtube = [];

    #[ORM\Column(type: 'json')]
    private array $weather = [];

    #[ORM\Column]
    private ?\DateTime $created = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updated = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMaps(): array
    {
        return $this->maps;
    }

    public function setMaps(array $maps): static
    {
        $this->maps = $maps;

        return $this;
    }

    public function getYoutube(): array
    {
        return $this->youtube;
    }

    public function setYoutube(array $youtube): static
    {
        $this->youtube = $youtube;

        return $this;
    }

    public function getWeather(): array
    {
        return $this->weather;
    }

    public function setWeather(array $weather): static
    {
        $this->weather = $weather;

        return $this;
    }

    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTime
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTime $updated): static
    {
        $this->updated = $updated;

        return $this;
    }
}
