<?php
require_once '../includes/functions.php';

// Détruire la session utilisateur
session_destroy();

// Rediriger vers la page d'accueil
redirect('../index.php');
?>