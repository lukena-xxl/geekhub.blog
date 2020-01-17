<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 */
class Users implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $login;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registration_date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Articles", mappedBy="user")
     */
    private $articles;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Statuses", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Articles", inversedBy="in_favorites")
     */
    private $favorite_articles;

    /**
     * @ORM\Column(type="boolean")
     */
    private $target = false;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birth_date;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Users", inversedBy="in_favorites")
     */
    private $favorite_users;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Users", mappedBy="favorite_users")
     */
    private $in_favorites;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $email;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->favorite_articles = new ArrayCollection();
        $this->favorite_users = new ArrayCollection();
        $this->in_favorites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->login;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {

    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {

    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registration_date;
    }

    public function setRegistrationDate(\DateTimeInterface $registration_date): self
    {
        $this->registration_date = $registration_date;

        return $this;
    }

    /**
     * @return Collection|Articles[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Articles $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setUser($this);
        }

        return $this;
    }

    public function removeArticle(Articles $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getUser() === $this) {
                $article->setUser(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?Statuses
    {
        return $this->status;
    }

    public function setStatus(?Statuses $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Articles[]
     */
    public function getFavoriteArticles(): Collection
    {
        return $this->favorite_articles;
    }

    public function addFavoriteArticle(Articles $favoriteArticle): self
    {
        if (!$this->favorite_articles->contains($favoriteArticle)) {
            $this->favorite_articles[] = $favoriteArticle;
        }

        return $this;
    }

    public function removeFavoriteArticle(Articles $favoriteArticle): self
    {
        if ($this->favorite_articles->contains($favoriteArticle)) {
            $this->favorite_articles->removeElement($favoriteArticle);
        }

        return $this;
    }

    public function getTarget(): ?bool
    {
        return $this->target;
    }

    public function setTarget(?bool $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birth_date;
    }

    public function setBirthDate(?\DateTimeInterface $birth_date): self
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getFavoriteUsers(): Collection
    {
        return $this->favorite_users;
    }

    public function addFavoriteUser(self $favoriteUser): self
    {
        if (!$this->favorite_users->contains($favoriteUser)) {
            $this->favorite_users[] = $favoriteUser;
        }

        return $this;
    }

    public function removeFavoriteUser(self $favoriteUser): self
    {
        if ($this->favorite_users->contains($favoriteUser)) {
            $this->favorite_users->removeElement($favoriteUser);
        }

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getInFavorites(): Collection
    {
        return $this->in_favorites;
    }

    public function addInFavorite(self $inFavorite): self
    {
        if (!$this->in_favorites->contains($inFavorite)) {
            $this->in_favorites[] = $inFavorite;
            $inFavorite->addFavoriteUser($this);
        }

        return $this;
    }

    public function removeInFavorite(self $inFavorite): self
    {
        if ($this->in_favorites->contains($inFavorite)) {
            $this->in_favorites->removeElement($inFavorite);
            $inFavorite->removeFavoriteUser($this);
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
