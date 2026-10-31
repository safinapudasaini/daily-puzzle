<?php
/**
 * Banana Quiz Game - Database Connection Manager
 * CIS046-3 Software For Enterprise
 * 
 * Demonstrates:
 * - Secure PDO Database Abstraction
 * - UTF-8 character encoding
 * - Prepared statement emulation disabled for true server-side prepares
 * 
 * Attribution: Built with AI pair-programming assistance (Senior Full-Stack Architecture).
 */

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    // Database configuration defaults (suitable for standard XAMPP environment)
    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = 3306;
    private const DB_NAME = 'banana_game';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    /**
     * Private constructor to prevent direct instantiation (Singleton pattern).
     */
    private function __construct() {}

    /**
     * Get the PDO database connection instance.
     *
     * @return PDO
     * @throws RuntimeException if connection fails
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                self::DB_HOST,
                self::DB_PORT,
                self::DB_NAME,
                self::DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Enforce native prepared statements
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . self::DB_CHARSET,
            ];

            try {
                self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            } catch (PDOException $e) {
                // In production or student testing, log details and provide clear message
                error_log('[Database Connection Error] ' . $e->getMessage());
                throw new RuntimeException(
                    'Database connection failed. Please ensure MySQL is running in XAMPP and the database "banana_game" is imported.'
                );
            }
        }

        return self::$instance;
    }
}
