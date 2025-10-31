<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

class PositionRepository
{
    public static function all(): array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->query('SELECT id_posisi, nama_posisi FROM Posisi ORDER BY nama_posisi');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare('SELECT id_posisi, nama_posisi FROM Posisi WHERE id_posisi = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $position = $stmt->fetch(PDO::FETCH_ASSOC);

        return $position ?: null;
    }
}
