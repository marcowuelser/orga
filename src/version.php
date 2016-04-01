<?php

class Constants
{
    const ORGA_SERVER_NAME = "ORGA Server";
    const ORGA_SERVER_REALM = "ORGA Server";

    const ORGA_SERVER_VERSION_MAJOR = 1;
    const ORGA_SERVER_VERSION_MINOR = 0;
    const ORGA_SERVER_VERSION_PATCH = 0;

    const ORGA_SERVER_VERSION =
        Constants::ORGA_SERVER_VERSION_MAJOR.".".
        Constants::ORGA_SERVER_VERSION_MINOR.".".
        Constants::ORGA_SERVER_VERSION_PATCH;

    const ORGA_SERVER_NAME_FULL =
        Constants::ORGA_SERVER_NAME." V".
        Constants::ORGA_SERVER_VERSION;
}

?>