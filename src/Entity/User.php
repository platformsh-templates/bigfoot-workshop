<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "`user`")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: 'string', unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $username = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    /**
     * @var BigFootSighting[]|Collection
     */
    #[ORM\OneToMany(mappedBy: "owner", targetEntity: BigFootSighting::class)]
    private Collection $bigFootSightings;

    /**
     * @var Comment[]|Collection
     */
    #[ORM\OneToMany(mappedBy: "owner", targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $agreedToTermsAt;

    public function __construct()
    {
        $this->bigFootSightings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
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
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection|BigFootSighting[]
     */
    public function getBigFootSightings(): Collection
    {
        return $this->bigFootSightings;
    }

    public function addBigFootSighting(BigFootSighting $bigFootSighting): self
    {
        if (!$this->bigFootSightings->contains($bigFootSighting)) {
            $this->bigFootSightings[] = $bigFootSighting;
            $bigFootSighting->setOwner($this);
        }

        return $this;
    }

    public function removeBigFootSighting(BigFootSighting $bigFootSighting): self
    {
        if ($this->bigFootSightings->contains($bigFootSighting)) {
            $this->bigFootSightings->removeElement($bigFootSighting);
            // set the owning side to null (unless already changed)
            if ($bigFootSighting->getOwner() === $this) {
                $bigFootSighting->setOwner(null);
            }
        }

        return $this;
    }

    public function getAvatarUrl(): string
    {
        return sprintf('https://avatars.dicebear.com/4.5/api/human/%s.svg?mood[]=happy', $this->getEmail());
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getAgreedToTermsAt(): ?\DateTimeInterface
    {
        return $this->agreedToTermsAt;
    }

    public function setAgreedToTermsAt(\DateTimeInterface $agreedToTermsAt): self
    {
        $this->agreedToTermsAt = $agreedToTermsAt;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function __serialize(): array
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return [$this->id, $this->username, $this->password];
    }

    public function __unserialize(array $data): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->username, $this->password] = $data;
    }
}
