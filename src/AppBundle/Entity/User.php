<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\CreatedByTrait;
use AppBundle\Entity\Traits\CreatedAtTrait;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\ModifiedAtTrait;
use AppBundle\Entity\Traits\ModifiedByTrait;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User.
 * Admins, clients, seo specialists...
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 * @ORM\HasLifecycleCallbacks
 * @ORM\MappedSuperclass
 * @UniqueEntity(fields={"usernameCanonical"}, message="Error: This username already used by other.")
 * @UniqueEntity(fields={"emailCanonical"}, message="Error: This email already used by other.")
 */
class User extends BaseUser
{
    const ROLE_DEFAULT = 'ROLE_USER';   // Low authenticated user.
    const ROLE_CLIENT = 'ROLE_CLIENT';  // Client. Basically, watch for their owned sites.
    const ROLE_SEO = 'ROLE_SEO';        // SEO specialist. Moderate their owned sites.
    const ROLE_ADMIN = 'ROLE_ADMIN';    // Moderate all previous users. Can't delete users. Can't make them SUPER_ADMIN.
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN'; // Super admin. Own ALL.
    const AVAILABLE_ROLES = [
        //self::ROLE_DEFAULT,
        self::ROLE_CLIENT,
        self::ROLE_SEO,
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN,
    ];
    const DEFAULT_LOWEST_ROLE = self::ROLE_CLIENT;  // Lowest role for authenticated user.

    use IdTrait;
    use CreatedByTrait;
    use ModifiedByTrait;
    use CreatedAtTrait;
    use ModifiedAtTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serialization\Groups({"list"})
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "username must be at least {{ limit }} characters long",
     *      maxMessage = "username cannot be longer than {{ limit }} characters"
     * )
     * @Serialization\Groups({"list"})
     */
    protected $username;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Serialization\Groups({"list"})
     */
    protected $email;

    /**
     * @var boolean
     *
     * @Serialization\Groups({"list"})
     */
    protected $enabled;

    /**
     * @var \DateTime
     *
     * @Serialization\Groups({"list"})
     */
    protected $lastLogin;

    /**
     * @var array
     *
     * @Serialization\Groups({"list"})
     */
    protected $roles;


    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param $enabled
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
        return $this;
    }

    /**
     * @ORM\PrePersist
     * //@ORM\PreFlush // We cant use this, because user automatically updated every login
     */
    public function setModifiedAtChange()
    {
        $this->setModifiedAt(new \DateTime('now'));
    }
}