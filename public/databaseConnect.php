<?php
    try
    {
        $db = new PDO('mysql:host=localhost:3306;dbname=surfcams', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo "connexion sucessfull";
    }
    catch (PDOException $e)
    {
        echo $e->getMessage();
    }

    require 'connexion.php';

    $user = new User($db);
    $user->cookie_login();
?>