<?php

namespace App\Entity;

use App\Repository\PostcodeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostcodeRepository::class)]
class Postcode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 16, unique: true)]
    private ?string $postcode = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $eastings = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $northings = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getEastings(): ?string
    {
        return $this->eastings;
    }

    public function setEastings(string $eastings): self
    {
        $this->eastings = $eastings;

        return $this;
    }

    public function getNorthings(): ?string
    {
        return $this->northings;
    }

    public function setNorthings(string $northings): self
    {
        $this->northings = $northings;

        return $this;
    }
}
