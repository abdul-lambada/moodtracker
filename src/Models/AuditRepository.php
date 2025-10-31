<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

class AuditRepository
{
    public static function latest(int $limit = 10): array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare(
            'SELECT a.*, u.nama_karyawan 
             FROM Audit_Log a 
             LEFT JOIN Users u ON u.id_user = a.id_user 
             ORDER BY a.created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function record(?int $userId, string $action, string $entity, ?int $entityId, ?string $description = null): void
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare(
            'INSERT INTO Audit_Log (id_user, action, entity, entity_id, description) 
             VALUES (:id_user, :action, :entity, :entity_id, :description)'
        );
        $stmt->execute([
            ':id_user' => $userId,
            ':action' => $action,
            ':entity' => $entity,
            ':entity_id' => $entityId,
            ':description' => $description,
        ]);
    }
}
