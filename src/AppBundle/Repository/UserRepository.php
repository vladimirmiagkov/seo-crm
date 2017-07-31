<?php
declare(strict_types=1);

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Exception\UserNotExistsException;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param string $username
     * @return User
     * @throws UserNotExistsException
     */
    public function getByUserName(string $username): User
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('Empty $username was provided.');
        }

        /** @var User $user */
        $user = $this->findOneBy(['username' => $username]);
        if (empty($user)) {
            throw new UserNotExistsException('Cant find user with $username=' . htmlspecialchars($username));
        }

        return $user;
    }
}