<?php
session_start();

// Recuperar la informació que s'està passant pel document login.js
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

//Carregar els usuaris des del json
$archivo = '../users/users.json';

$json = file_get_contents($archivo);
$usuarios = json_decode($json, true);

// Verificació de que la petició s'ha fet amb el mètode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $resposta=["correcte"=>false];
    // Recollir i sanejar les dades
    $nombre = trim($data['userName'] ?? '');
    $password = trim($data['password'] ?? '');

    $nombre=filter_var($nombre, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password=filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Bucle per a recorrer l'array del document json
    $trobat = array_filter($usuarios["usuaris"], function($user) use ($nombre) {
        return $user["nom"] === $nombre;
    });

    // Si s'ha trobat el nom de l'usuari indicat
    if(!empty($trobat)){
        $usuariTrobat=reset($trobat); //agafa el primer element d'una array
        if($password == $usuariTrobat["contrasenya"]){
            session_regenerate_id(true); //Quan fas el login et genera una altra sessió internament
            $_SESSION['usuari'] = $usuariTrobat["nom"];
            $_SESSION['admin'] = $usuariTrobat["admin"];
            $resposta["correcte"]=true;
            echo json_encode($resposta);
            exit();
        } else{
            echo json_encode($resposta);
        }
    } else{
        echo json_encode($resposta);
    }
}

?>