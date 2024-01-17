<?php

namespace App\Entity;

use App\Repository\BookCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: BookCategoryRepository::class)]
#[UniqueEntity('title')]
class BookCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $title = null;

    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'categories')]
    private Collection $books;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childCategories')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?self $parentCategory = null;

    #[ORM\OneToMany(mappedBy: 'parentCategory', targetEntity: self::class)]
    private Collection $childCategories;

    public function __construct()
    {
        $this->books = new ArrayCollection();
        $this->childCategories = new ArrayCollection();
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

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->addCategory($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            $book->removeCategory($this);
        }

        return $this;
    }

    public function getParentCategory(): ?self
    {
        return $this->parentCategory;
    }

    public function setParentCategory(?self $parentCategory): static
    {
        $this->parentCategory = $parentCategory;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildCategories(): Collection
    {
        return $this->childCategories;
    }

    public function addChildCategory(self $childCategory): static
    {
        if (!$this->childCategories->contains($childCategory)) {
            $this->childCategories->add($childCategory);
            $childCategory->setParentCategory($this);
        }

        return $this;
    }

    public function removeChildCategory(self $childCategory): static
    {
        if ($this->childCategories->removeElement($childCategory)) {
            // set the owning side to null (unless already changed)
            if ($childCategory->getParentCategory() === $this) {
                $childCategory->setParentCategory(null);
            }
        }

        return $this;
    }
}
