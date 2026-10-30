<?php
class Database
{
    private static ?PDO $pdo = null;

    // Uses the $con created in backend/pdo_connection.php
    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            require __DIR__ . '/../../pdo_connection.php';   // defines $con
            if (!isset($con) || !($con instanceof PDO)) {
                throw new RuntimeException('pdo_connection.php did not create $con.');
            }
            self::$pdo = $con;
        }
        return self::$pdo;
    }

    // Prepared statement helper. Integers are bound as integers (needed for LIMIT/OFFSET).
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        foreach ($params as $key => $value) {
            $type = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_null($value) => PDO::PARAM_NULL,
                is_bool($value) => PDO::PARAM_BOOL,
                default         => PDO::PARAM_STR,
            };
            $stmt->bindValue(is_int($key) ? $key + 1 : $key, $value, $type);
        }
        $stmt->execute();
        return $stmt;
    }

    public static function lastId(): int
    {
        return (int)self::connect()->lastInsertId();
    }
}