<?php
// Configuration pour les uploads de fichiers
return [
    // Types de fichiers autorisés
    'allowed_types' => [
        'image/jpeg',
        'image/jpg', 
        'image/png'
    ],
    
    // Extensions autorisées
    'allowed_extensions' => [
        'jpg',
        'jpeg',
        'png'
    ],
    
    // Taille maximale (en bytes)
    'max_file_size' => 5 * 1024 * 1024, // 5MB
    
    // Dossier de destination
    'upload_directory' => 'uploads/',
    
    // Préfixe pour les noms de fichiers
    'file_prefix' => 'payment_proof_',
    
    // Permissions du dossier
    'directory_permissions' => 0755,
    
    // Activation des logs
    'enable_logging' => true,
    
    // Niveau de log
    'log_level' => 'INFO', // DEBUG, INFO, WARNING, ERROR
    
    // Messages d'erreur personnalisés
    'error_messages' => [
        'file_too_large' => 'Le fichier est trop volumineux. Taille maximum: 5MB.',
        'invalid_type' => 'Type de fichier non autorisé. Seuls JPG et PNG sont acceptés.',
        'upload_failed' => 'Erreur lors de l\'upload du fichier.',
        'invalid_file' => 'Fichier invalide ou corrompu.',
        'permission_denied' => 'Permissions insuffisantes pour l\'upload.',
        'directory_error' => 'Erreur avec le dossier de destination.'
    ]
];
?>