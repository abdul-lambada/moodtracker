<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

class UserRepository
{
    public static function findByBundy(string $noBundy): ?array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare('SELECT * FROM Users WHERE no_bundy = :no_bundy LIMIT 1');
        $stmt->execute([':no_bundy' => $noBundy]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function all(): array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->query('SELECT id_user, nama_karyawan, no_bundy FROM Users ORDER BY nama_karyawan');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare('SELECT * FROM Users WHERE id_user = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function countAll(): int
    {
        $pdo = Connection::instance();
        $stmt = $pdo->query('SELECT COUNT(*) AS total FROM Users');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($row['total'] ?? 0);
    }

    public static function listWithPosition(): array
    {
        $pdo = Connection::instance();
        $sql = 'SELECT u.*, p.nama_posisi FROM Users u INNER JOIN Posisi p ON p.id_posisi = u.id_posisi ORDER BY u.nama_karyawan';
        $stmt = $pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function positions(): array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->query('SELECT id_posisi, nama_posisi FROM Posisi ORDER BY nama_posisi');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare(
            'INSERT INTO Users (nama_karyawan, no_bundy, password_hash, id_posisi, role) 
             VALUES (:nama_karyawan, :no_bundy, :password_hash, :id_posisi, :role)'
        );
        $stmt->execute([
            ':nama_karyawan' => $data['nama_karyawan'],
            ':no_bundy' => $data['no_bundy'],
            ':password_hash' => $data['password_hash'],
            ':id_posisi' => $data['id_posisi'],
            ':role' => $data['role'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Connection::instance();

        $fields = [
            'nama_karyawan' => ':nama_karyawan',
            'no_bundy' => ':no_bundy',
            'id_posisi' => ':id_posisi',
            'role' => ':role',
        ];

        $params = [
            ':nama_karyawan' => $data['nama_karyawan'],
            ':no_bundy' => $data['no_bundy'],
            ':id_posisi' => $data['id_posisi'],
            ':role' => $data['role'],
            ':id' => $id,
        ];

        if (!empty($data['password_hash'])) {
            $fields['password_hash'] = ':password_hash';
            $params[':password_hash'] = $data['password_hash'];
        }

        $setClauses = [];
        foreach ($fields as $column => $placeholder) {
            $setClauses[] = $column . ' = ' . $placeholder;
        }

        $sql = 'UPDATE Users SET ' . implode(', ', $setClauses) . ' WHERE id_user = :id';
        $stmt = $pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare('DELETE FROM Users WHERE id_user = :id');

        return $stmt->execute([':id' => $id]);
    }

    public static function findByBundyExcept(string $noBundy, int $excludeId): ?array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare('SELECT * FROM Users WHERE no_bundy = :no_bundy AND id_user <> :exclude LIMIT 1');
        $stmt->execute([
            ':no_bundy' => $noBundy,
            ':exclude' => $excludeId,
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function paginateWithPosition(string $search, int $page, int $perPage = 10): array
    {
        $pdo = Connection::instance();

        $page = max($page, 1);
        $offset = ($page - 1) * $perPage;

        $search = trim($search);
        $params = [];
        $where = '';

        if ($search !== '') {
            $where = ' WHERE (u.nama_karyawan LIKE :search OR u.no_bundy LIKE :search OR p.nama_posisi LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $baseQuery = ' FROM Users u INNER JOIN Posisi p ON p.id_posisi = u.id_posisi';

        $countStmt = $pdo->prepare('SELECT COUNT(*) AS total' . $baseQuery . $where);
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $dataStmt = $pdo->prepare(
            'SELECT u.*, p.nama_posisi' . $baseQuery . $where . ' ORDER BY u.nama_karyawan LIMIT :limit OFFSET :offset'
        );

        foreach ($params as $key => $value) {
            $dataStmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStmt->execute();

        return [
            'data' => $dataStmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
        ];
    }
}
