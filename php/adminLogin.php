<?php
    session_start();

    $resposta=["correcte"=>false, "isLoggedIn"=>false];

    if (!empty($_SESSION['usuari'])) {
        $resposta["isLoggedIn"]=true;
        $resposta["correcte"]=$_SESSION['admin'];
    }

    echo json_encode($resposta);

?>