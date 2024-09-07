<?php

namespace App\Service;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;

class UserRoleService
{
    public function __construct(private RoleRepository $roleRepository)
    {

    }
    public function addUserRoles(User $user, array $roles): void
    {
        $collection = $user->getRoleEntities();

        foreach ($collection as $item) {
          if ($item instanceof Role) {
              $user->removeRole($item);
          } else {
              $collection->remove($item);
          }
        }

        $collection->clear();

        $rolesInDb = $this->roleRepository->findAllByNames($roles);
        foreach ($rolesInDb as $item) {
            $user->addRole($item);
        }
    }
}