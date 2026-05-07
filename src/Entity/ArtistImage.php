<?php

namespace App\Entity;

use App\Repository\ArtistImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistImageRepository::class)]
class ArtistImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $caption = null;

    #[ORM\Column]
    private ?int $position = 0;

    #[ORM\ManyToOne(targetEntity: ArtistProfile::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ArtistProfile $artist = null;

    public function getId(): ?int { return $this->id; }

    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(string $filename): static { $this->filename = $filename; return $this; }

    public function getCaption(): ?string { return $this->caption; }
    public function setCaption(?string $caption): static { $this->caption = $caption; return $this; }

    public function getPosition(): ?int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function getArtist(): ?ArtistProfile { return $this->artist; }
    public function setArtist(?ArtistProfile $artist): static { $this->artist = $artist; return $this; }

    public function getImagePath(): string
    {
        return '/uploads/gallery/' . $this->filename;
    }
}
