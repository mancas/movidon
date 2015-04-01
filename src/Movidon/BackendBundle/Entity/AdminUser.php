<?php
namespace Movidon\BackendBundle\Entity;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table()
 * @ORM\Entity
 * @DoctrineAssert\UniqueEntity("username")
 * @UniqueEntity("username")
 */
class AdminUser implements UserInterface, \Serializable, EquatableInterface
{
    const AUTH_SALT = "R4y0d3Lu24b1sMa7";

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=250, unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min = 6)
     * */
    protected $password;

    /**
     * @ORM\Column(name="salt", type="string", length=255)
     */
    protected $salt;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="registeredDate", type="datetime", nullable=true)
     * @Assert\Date()
     */
    protected $registeredDate;

    public function serialize()
    {
        return serialize(array($this->id, $this->password, $this->username));
    }

    public function unserialize($serialized)
    {
        list($this->id, $this->password, $this->username) = unserialize(
            $serialized);
    }

    public function __toString()
    {

        return $this->getUsername();
    }

    public function isEqualTo(
        \Symfony\Component\Security\Core\User\UserInterface $user)
    {
        return $this->getUsername() == $user->getUsername();
    }

    public function eraseCredentials()
    {
    }

    public function getRoles()
    {
        return array('ROLE_SUPER_ADMIN');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    public function getRegisteredDate()
    {
        return $this->registeredDate;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

}