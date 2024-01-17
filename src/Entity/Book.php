<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[UniqueEntity('isbn')]
class Book
{
    const THUMBNAIL_UPLOAD_PATH = '/uploads/books/';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $isbn = null;

    #[ORM\Column]
    private ?int $pageCount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?BookStatus $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thumbnail = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publishedDate = null;

    #[ORM\ManyToMany(targetEntity: BookCategory::class, inversedBy: 'books')]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: BookAuthor::class, inversedBy: 'books')]
    private Collection $authors;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->authors = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getPageCount(): ?int
    {
        return $this->pageCount;
    }

    public function setPageCount(int $pageCount): static
    {
        $this->pageCount = $pageCount;

        return $this;
    }

    public function getStatus(): ?BookStatus
    {
        return $this->status;
    }

    public function setStatus(?BookStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getThumbnailPath(): ?string
    {
        if ($this->thumbnail) {
            return $this::THUMBNAIL_UPLOAD_PATH . $this->thumbnail;
        };

        return null;
    }

    public function getPublishedDate(): ?\DateTimeInterface
    {
        return $this->publishedDate;
    }

    public function setPublishedDate(?\DateTimeInterface $publishedDate): static
    {
        $this->publishedDate = $publishedDate;

        return $this;
    }

    /**
     * @return Collection<int, BookCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(BookCategory $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(BookCategory $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return Collection<int, BookAuthor>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(BookAuthor $author): static
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
        }

        return $this;
    }

    public function removeAuthor(BookAuthor $author): static
    {
        $this->authors->removeElement($author);

        return $this;
    }
}
