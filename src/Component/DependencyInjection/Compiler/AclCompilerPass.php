<?php

namespace GSS\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AclCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $aclConfig = $container->getParameter('acl');
        $roles = [];
        foreach ($aclConfig['roles'] as $aRole => $_) {
            $groupPermissions = !isset($aclConfig['roles'][$aRole]) ? $aclConfig['roles']['default'] : $aclConfig['roles'][$aRole];

            // Build role_hierarchy
            if (isset($aclConfig['role_hierarchy'][$aRole])) {
                foreach ($aclConfig['role_hierarchy'][$aRole] as $role) {
                    if (isset($aclConfig['roles'][$role])) {
                        $groupPermissions = $groupPermissions + $role;
                    }
                }
            }

            $roles[$aRole] = $groupPermissions;
        }

        $container->setParameter('acl.build.roles', $roles);
    }
}
