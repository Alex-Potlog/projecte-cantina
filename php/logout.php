<?php
    # Initialize the session
    session_start();
    require_once 'Logger.php';

    // Registrar logout abans de destruir la sessió
    if (isset($_SESSION['usuari'])) {
        Logger::logout($_SESSION['usuari']);
    }

    # Unset all session variables
    $_SESSION = array();

    # Destroy the session
    session_destroy();
?>