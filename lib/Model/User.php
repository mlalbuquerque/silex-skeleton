<?php

// Your system's user entity should extends this class
namespace Model;

class User extends Entity
{
    
    public $profile;
    
    public function setPermission($permission)
    {
        $this->profile = $permission;
    }
    
    public function getPermission()
    {
        return $this->profile;
    }
    
}
