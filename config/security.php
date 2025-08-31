<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration de sécurité Quizify
    |--------------------------------------------------------------------------
    |
    | Configuration centralisée des paramètres de sécurité pour l'application
    | Basée sur les recommandations OWASP Top 3 implémentées
    |
    */

    /*
    |--------------------------------------------------------------------------
    | SSL/TLS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour forcer HTTPS et sécuriser les communications
    |
    */
    'force_https' => env('FORCE_HTTPS', false),
    'hsts_max_age' => env('HSTS_MAX_AGE', 31536000), // 1 an
    'hsts_include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
    'hsts_preload' => env('HSTS_PRELOAD', true),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Protection contre les attaques par déni de service et brute force
    |
    */
    'rate_limiting' => [
        'api' => [
            'max_attempts' => env('RATE_LIMIT_API', 60),
            'decay_minutes' => env('RATE_LIMIT_DECAY', 1),
        ],
        'auth' => [
            'max_attempts' => env('RATE_LIMIT_AUTH', 5),
            'decay_minutes' => env('RATE_LIMIT_AUTH_DECAY', 15),
        ],
        'quiz_submission' => [
            'max_attempts' => env('RATE_LIMIT_QUIZ', 10),
            'decay_minutes' => env('RATE_LIMIT_QUIZ_DECAY', 5),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Configuration sécurisée des sessions
    |
    */
    'session' => [
        'secure_cookie' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => env('SESSION_HTTP_ONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'strict'),
        'lifetime' => env('SESSION_LIFETIME', 120), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Directives CSP pour prévenir les attaques XSS
    |
    */
    'csp' => [
        'default_src' => env('CSP_DEFAULT_SRC', "'self'"),
        'script_src' => env('CSP_SCRIPT_SRC', "'self' 'unsafe-inline'"),
        'style_src' => env('CSP_STYLE_SRC', "'self' 'unsafe-inline'"),
        'img_src' => env('CSP_IMG_SRC', "'self' data: https:"),
        'font_src' => env('CSP_FONT_SRC', "'self'"),
        'connect_src' => env('CSP_CONNECT_SRC', "'self'"),
        'media_src' => env('CSP_MEDIA_SRC', "'none'"),
        'object_src' => env('CSP_OBJECT_SRC', "'none'"),
        'child_src' => env('CSP_CHILD_SRC', "'none'"),
        'frame_ancestors' => env('CSP_FRAME_ANCESTORS', "'none'"),
        'form_action' => env('CSP_FORM_ACTION', "'self'"),
        'base_uri' => env('CSP_BASE_URI', "'self'"),
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation & Sanitization
    |--------------------------------------------------------------------------
    |
    | Règles de validation et sanitisation des entrées
    |
    */
    'input_sanitization' => [
        'max_string_length' => env('MAX_STRING_LENGTH', 1000),
        'max_array_depth' => env('MAX_ARRAY_DEPTH', 10),
        'allowed_html_tags' => ['p', 'br', 'strong', 'em', 'u', 'ol', 'ul', 'li'],
        'strip_tags_on_sensitive_fields' => true,
        'encode_html_entities' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Configuration sécurisée pour les uploads de fichiers
    |
    */
    'file_upload' => [
        'max_file_size' => env('MAX_FILE_SIZE', 2048), // KB
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'text/plain',
        ],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'],
        'scan_for_malware' => env('SCAN_MALWARE', false),
        'quarantine_suspicious_files' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Security
    |--------------------------------------------------------------------------
    |
    | Configuration de sécurité base de données
    |
    */
    'database' => [
        'use_prepared_statements' => true,
        'escape_output' => true,
        'log_queries' => env('LOG_DB_QUERIES', false),
        'enable_query_cache' => env('DB_QUERY_CACHE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Security
    |--------------------------------------------------------------------------
    |
    | Configuration sécurisée de l'authentification
    |
    */
    'authentication' => [
        'token_expiration' => env('TOKEN_EXPIRATION', 1440), // minutes (24h)
        'refresh_token_expiration' => env('REFRESH_TOKEN_EXPIRATION', 10080), // minutes (7 jours)
        'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('LOCKOUT_DURATION', 900), // secondes (15 min)
        'require_email_verification' => env('REQUIRE_EMAIL_VERIFICATION', true),
        'password_min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'password_require_special_chars' => env('PASSWORD_REQUIRE_SPECIAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging & Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration des logs de sécurité
    |
    */
    'logging' => [
        'log_security_events' => env('LOG_SECURITY_EVENTS', true),
        'log_failed_authentications' => env('LOG_FAILED_AUTH', true),
        'log_privilege_escalations' => env('LOG_PRIVILEGE_ESC', true),
        'log_suspicious_activities' => env('LOG_SUSPICIOUS', true),
        'alert_on_security_breach' => env('ALERT_SECURITY_BREACH', true),
        'security_log_channel' => env('SECURITY_LOG_CHANNEL', 'security'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security Headers
    |--------------------------------------------------------------------------
    |
    | Headers de sécurité pour les réponses API
    |
    */
    'api_headers' => [
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'DENY',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'camera=(), microphone=(), geolocation=()',
        'remove_server_header' => true,
        'remove_x_powered_by' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    |
    | Configuration du chiffrement des données sensibles
    |
    */
    'encryption' => [
        'algorithm' => env('ENCRYPTION_ALGORITHM', 'AES-256-CBC'),
        'encrypt_sensitive_data' => env('ENCRYPT_SENSITIVE_DATA', true),
        'key_rotation_interval' => env('KEY_ROTATION_INTERVAL', 2592000), // 30 jours
        'hash_algorithm' => env('HASH_ALGORITHM', 'sha256'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    |
    | Configuration sécurisée de la gestion d'erreurs
    |
    */
    'error_handling' => [
        'hide_error_details_in_production' => env('HIDE_ERROR_DETAILS', true),
        'log_all_errors' => env('LOG_ALL_ERRORS', true),
        'generic_error_message' => 'Une erreur est survenue. Veuillez réessayer.',
        'include_trace_in_logs' => env('INCLUDE_TRACE_LOGS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Scans & Audits
    |--------------------------------------------------------------------------
    |
    | Configuration des outils d'analyse de sécurité
    |
    */
    'security_scans' => [
        'enable_dependency_check' => env('ENABLE_DEPENDENCY_CHECK', true),
        'enable_code_analysis' => env('ENABLE_CODE_ANALYSIS', true),
        'scan_frequency' => env('SCAN_FREQUENCY', 'daily'),
        'alert_on_vulnerabilities' => env('ALERT_VULNERABILITIES', true),
    ],
];
