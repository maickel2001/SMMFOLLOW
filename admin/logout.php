<?php
require_once '../includes/functions.php';

// Destruction de la session admin
session_start();
session_destroy();

// Redirection vers la page de connexion
redirect('login.php');
?>