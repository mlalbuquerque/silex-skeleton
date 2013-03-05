<?php

// Your system's user entity should extends this class
namespace Model;

class User extends Entity
{
    
    public function setPermission($permission)
    {
        $auth_attr = USER_AUTH_ATTR;
        $this->$auth_attr = $permission;
    }
    
    public function getPermission()
    {
        $auth_attr = USER_AUTH_ATTR;
        return $this->$auth_attr;
    }
    
}
