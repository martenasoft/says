<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    public const PAGE_TYPE = 1;
    public const SECTION_TYPE = 2;
    public const EXTEND_TYPE = 3;

    public const STATUS_ACTIVE = 1;
    public const STATUS_EDIT = 2;

    public const STATUS_DELETED = 3;

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_EDIT => 'Edit',
        self::STATUS_DELETED => 'Deleted',
    ];
    public const TYPES = [
        self::PAGE_TYPE => 'Page',
        self::SECTION_TYPE => 'Section',
        self::EXTEND_TYPE => 'Extend',
    ];
    public const SMALL_IMAGE_TYPE = 1;
    public const BIG_IMAGE_TYPE = 2;
    public const ORIGIN_IMAGE_TYPE = 3;
    public const IMAGE_TYPES = [
        self::ORIGIN_IMAGE_TYPE => 'Origin image',
        self::SMALL_IMAGE_TYPE => 'Small to preview',
        self::BIG_IMAGE_TYPE => 'Main image'
    ];
    public const PATH = [
        self::ORIGIN_IMAGE_TYPE => 'origin',
        self::SMALL_IMAGE_TYPE => 'small',
        self::BIG_IMAGE_TYPE => 'big'
    ];
    public const MIME_TYPES = [
        'image/gif' => 'Gif',
        'image/jpeg' => 'Jpeg',
        'image/jpg' => 'Jpg',

    ];
    public const MAX_SIZES = '10240k';
    public const SIZES = [
        self::SMALL_IMAGE_TYPE => [
            'width' => 300,
            'height' => 300
        ],
        self::BIG_IMAGE_TYPE => [
            'width' => 800,
            'height' => 800
        ]
    ];

    public const MENU_TYPE_VERTICAL = 1;
    public const MENU_TYPE_HORIZONTAL = 2;
    public const MENU_TYPE_BOTH_VERTICAL_AND_HORIZONTAL = 3;
    public const MENU_TYPE_NONE = 4;

    public const MENU_TYPES = [
        self::MENU_TYPE_VERTICAL => 'Vertical',
        self::MENU_TYPE_HORIZONTAL => 'Horizontal',
        self::MENU_TYPE_BOTH_VERTICAL_AND_HORIZONTAL => 'Vertical and horizontal',
        self::MENU_TYPE_NONE => 'No menu',
    ];


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $preview = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $children;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $menuType = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $type = self::PAGE_TYPE;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publicAt = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $level = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function setPreview(?string $preview): static
    {
        $this->preview = $preview;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChildren(self $children): static
    {
        if (!$this->children->contains($children)) {
            $this->children->add($children);
            $children->setParent($this);
        }

        return $this;
    }

    public function removeChildren(self $children): static
    {
        if ($this->children->removeElement($children)) {
            // set the owning side to null (unless already changed)
            if ($children->getParent() === $this) {
                $children->setParent(null);
            }
        }

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return Collection<int, PageUrl>
     */
    public function getPageUrls(): Collection
    {
        return $this->pageUrls;
    }

    public function addPageUrl(PageUrl $pageUrl): static
    {
        if (!$this->pageUrls->contains($pageUrl)) {
            $this->pageUrls->add($pageUrl);
            $pageUrl->setPage($this);
        }

        return $this;
    }

    public function removePageUrl(PageUrl $pageUrl): static
    {
        if ($this->pageUrls->removeElement($pageUrl)) {
            // set the owning side to null (unless already changed)
            if ($pageUrl->getPage() === $this) {
                $pageUrl->setPage(null);
            }
        }

        return $this;
    }

    public function getMenuType(): ?int
    {
        return $this->menuType;
    }

    public function setMenuType(int $menuType): static
    {
        $this->menuType = $menuType;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPublicAt(): ?\DateTimeImmutable
    {
        return $this->publicAt;
    }

    public function setPublicAt(?\DateTimeImmutable $publicAt): static
    {
        $this->publicAt = $publicAt;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }
}
