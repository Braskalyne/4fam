<?php

namespace App\Entity;

use App\Repository\ArtistProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistProfileRepository::class)]
class ArtistProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // 'maud' ou 'camille'
    #[ORM\Column(length: 50, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagramUrl = null;

    /**
     * @var Collection<int, ArtistImage>
     */
    #[ORM\OneToMany(targetEntity: ArtistImage::class, mappedBy: 'artist', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getBio(): ?string { return $this->bio; }
    public function setBio(?string $bio): static { $this->bio = $bio; return $this; }

    public function getInstagramUrl(): ?string { return $this->instagramUrl; }
    public function setInstagramUrl(?string $instagramUrl): static { $this->instagramUrl = $instagramUrl; return $this; }

    public function getImages(): Collection { return $this->images; }

    public function addImage(ArtistImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setArtist($this);
        }
        return $this;
    }

    public function removeImage(ArtistImage $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getArtist() === $this) {
                $image->setArtist(null);
            }
        }
        return $this;
    }
}
