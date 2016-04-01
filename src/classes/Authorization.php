<?php

class Authorization 
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function setCurrentUser($currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function isRoleEqualOrHigher($role)
    {
        // TODO
        return true;
    }

    private $app = null;
    private $currentUser = null;

?>