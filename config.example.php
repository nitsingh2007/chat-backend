<?php
/**
 * config.example.php
 *
 * Template for the real config.php.
 *
 * INSTRUCTIONS:
 *   1. Copy this file to config.php in the same folder.
 *   2. Replace every placeholder below with your real values.
 *   3. NEVER commit config.php to Git — it is listed in .gitignore.
 *   4. This example file IS safe to commit; it contains no real secrets.
 */

// ============================================
// Oracle Database
// ============================================
// DB_HOST format depends on your Oracle setup:
//   - Easy Connect:  host:port/service_name   e.g. 192.168.1.10:1521/XEPDB1
//   - TNS name:      the alias from tnsnames.ora   e.g. XEPDB1
define('DB_HOST', 'your_oracle_host:port/service_name');
define('DB_USER', 'your_oracle_username');
define('DB_PASS', 'your_oracle_password');

// ============================================
// Email (PHPMailer / SMTP)
// ============================================
// EMAIL_KEY is whatever your script currently uses as the SMTP password
// or app-specific passkey. Keep the same constant name your code already
// expects; rename it here if yours is different.
define('EMAIL_KEY', 'your_email_or_smtp_passkey');
?>