<?php
declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Exception\UserAlreadyExistsException;
use AppBundle\Exception\UserNotExistsException;
use AppBundle\Repository\UserRepository;
use AppBundle\Security\Core\RsAcl;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(ValidatorInterface $validator, EntityManager $em)
    {
        $this->validator = $validator;
        $this->em = $em;

        $this->userRepository = $this->em->getRepository('AppBundle:User');
    }

    /**
     * @param array $userData
     * @param User  $creator
     * @return User
     * @throws UserAlreadyExistsException
     */
    public function createNewUser(array $userData, User $creator): User
    {
        // Prepare new user
        $user = new User();
        $user->setUsername($userData['username'])
            ->setEmail($userData['email'])
            ->setPlainPassword($userData['password'])
            ->setEnabled($userData['enabled'])
            ->setCreatedBy($creator)
            ->setModifiedBy($creator);

        //if (!empty($userData['id'])) {
        //    /** @var \Doctrine\ORM\Mapping\ClassMetadata $metadata */
        //    $metadata = $this->em->getClassMetaData(User::class);
        //    $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator()); // For save our explicitly setted id.
        //    $user->setId($userData['id']);
        //}

        // Sanitize user role
        $userRole = $this->evaluateAvailableRoleForSetToUser($userData['roles'], $creator);
        $user->setRoles([$userRole]);

        // Validate user
        $this->validateUser($user);

        // Try to create new user
        try {
            // User already exist?
            $this->userRepository->getByUserName($user->getUsername());
        } catch (UserNotExistsException $e) {
            // User not exists, so create new user
            $this->em->persist($user);
            $this->em->flush();

            return $user;
        }

        throw new UserAlreadyExistsException();
    }

    /**
     * @param       $id
     * @param array $userData
     * @param User  $creator
     * @return User
     * @throws UserNotExistsException
     */
    public function updateUser($id, array $userData, User $creator): User
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Cannot update user with empty id.');
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $id]);
        if (empty($user)) {
            throw new UserNotExistsException();
        }

        // Set user data
        isset($userData['username']) ? $user->setUsername($userData['username']) : null;
        isset($userData['email']) ? $user->setEmail($userData['email']) : null;
        !empty($userData['password']) ? $user->setPlainPassword($userData['password']) : null;
        isset($userData['enabled']) ? $user->setEnabled($userData['enabled']) : null;
        $user->setModifiedBy($creator);
        $user->setModifiedAtChange();

        if (empty($userData['roles'])) {
            $userData['roles'] = $user->getRoles();
        }
        // Sanitize user role
        $userRole = $this->evaluateAvailableRoleForSetToUser($userData['roles'], $creator);
        $user->setRoles([$userRole]);


        // Validate user
        $this->validateUser($user);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @param      $id
     * @param User $creator
     * @return bool
     * @throws UserNotExistsException
     */
    public function deleteUser($id, User $creator): bool
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Cannot delete user with empty id.');
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $id]);
        if (empty($user)) {
            throw new UserNotExistsException();
        }

        // BUSINESS LOGIC: User cannot delete themself.
        if ($creator->getId() === $user->getId()) {
            throw new PreconditionFailedHttpException('User cannot delete themself.');
        }

        //$user->setEnabled(false); //todo: Do not remove user! Only mark as removed.
        //$this->em->persist($user);
        $this->em->remove($user);
        $this->em->flush();

        return true;
    }

    /**
     * Evaluate role.
     * Check: Can $creator set a $role to another user?
     * Return: maximum available role for "set role to another user".
     *
     * @param      $role
     * @param User $creator
     * @return string role
     * @throws PreconditionFailedHttpException
     */
    public function evaluateAvailableRoleForSetToUser($role, User $creator): string
    {
        if (is_array($role)) { // BUSINESS LOGIC: Support only single role for user.
            $role = $role[0];
        }
        if (in_array($role, USER::AVAILABLE_ROLES)) { // Not malformed or unavailable role
            // BUSINESS LOGIC: Only ROLE_SUPER_ADMIN users can set role "ROLE_SUPER_ADMIN" to other users!
            //                 ROLE_ADMIN users CANT set role "ROLE_SUPER_ADMIN" to other users!
            if ($role === USER::ROLE_SUPER_ADMIN && !$creator->hasRole(USER::ROLE_SUPER_ADMIN)) {
                throw new PreconditionFailedHttpException(
                    'Only users with role "' . USER::ROLE_SUPER_ADMIN
                    . '" can set role "' . USER::ROLE_SUPER_ADMIN . '" to other users');
            }
        } else {
            USER::DEFAULT_LOWEST_ROLE;
        }

        return $role;
    }

    /**
     * @param User $user
     * @throws \InvalidArgumentException
     */
    protected function validateUser(User $user)
    {
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string)$errors);
        }
    }
}