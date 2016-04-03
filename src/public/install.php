<?php

require_once("../config.php");

$config = getConfig();
$host = $config['db']['host'];
$user = $config['db']['user'];
$pass = $config['db']['pass'];
$name = $config['db']['dbname'];

$schemaPath = "../schema/schema_v1.sql";

$doWipeout = false;
if (isset($_GET["wipeout"]))
{
    $doWipeout = true;
}

// Read Schema
$file = fopen($schemaPath, "r") or die("Unable to open file!");
$schema_v1 = fread($file, filesize($schemaPath));
fclose($file);

try
{
    // Connect to DB
    $db = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);

    // Wipeout old data
    if ($doWipeout)
    {
        $db->exec("DROP TABLE s_user;");
        $db->exec("DROP TABLE s_system;");

        $db->exec("DROP TABLE r_ruleset;");
        $db->exec("DROP TABLE r_entity;");
        $db->exec("DROP TABLE r_entity_field;");
        $db->exec("DROP TABLE r_entity_instance;");
        $db->exec("DROP TABLE r_entity_instance_value;");

        $db->exec("DROP TABLE g_game;");
        $db->exec("DROP TABLE g_player;");
    }

    $db->query($schema_v1);
    echo "Installation completed\n";
}
catch (PDOException $ex)
{
      echo "An Error occured!\n";
      die($ex);
}

?>
