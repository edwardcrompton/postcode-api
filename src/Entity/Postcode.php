<?php

namespace App\Entity;

use App\Repository\PostcodeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: PostcodeRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(denormalizationContext: ['groups' => 'postcode:list'])
    ],
    order: ['postcode' => 'ASC'],
    routePrefix: 'v1',
)]
#[ApiFilter(SearchFilter::class, properties: ['postcode' => 'ipartial'])]
class Postcode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 16, unique: true)]
    #[Groups(['postcode:list'])]
    private ?string $postcode = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Groups(['postcode:list'])]
    private ?string $eastings = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Groups(['postcode:list'])]
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
