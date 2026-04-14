<?php
// Redirection vers le FrontOffice de l'application.
// Ouvrir toujours via un serveur HTTP (localhost), pas en double-cliquant sur le fichier.
header('Location: app/views/front/accueil.php');
exit;
