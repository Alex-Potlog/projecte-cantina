<?php
    session_start();

    $resposta=["correcte"=>false];

    if (!empty($_SESSION['usuari'])) {
        $resposta["correcte"]=true;
        $resposta['usuari']=$_SESSION['usuari'];
    }

    echo json_encode($resposta);

?>