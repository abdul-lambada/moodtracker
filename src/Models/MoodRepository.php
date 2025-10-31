<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

class MoodRepository
{
    public static function create(array $data): int
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare(
            'INSERT INTO Catatan_Harian (id_user, mood, catatan_mood, output_harian, tanggal_catatan) 
             VALUES (:id_user, :mood, :catatan_mood, :output_harian, :tanggal_catatan)'
        );

        $stmt->execute([
            ':id_user' => $data['id_user'],
            ':mood' => $data['mood'],
            ':catatan_mood' => $data['catatan_mood'],
            ':output_harian' => $data['output_harian'],
            ':tanggal_catatan' => $data['tanggal_catatan'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function latest(int $limit = 10): array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare(
            'SELECT c.*, u.nama_karyawan, u.no_bundy 
             FROM Catatan_Harian c 
             INNER JOIN Users u ON u.id_user = c.id_user 
             ORDER BY c.tanggal_catatan DESC 
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function filterPaginated(?int $userId, ?string $date, int $page, int $perPage = 5): array
    {
        $pdo = Connection::instance();

        $page = max($page, 1);
        $perPage = max($perPage, 1);
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $params = [];

        if ($userId !== null) {
            $conditions[] = 'c.id_user = :userId';
            $params[':userId'] = $userId;
        }

        if ($date !== null) {
            $conditions[] = 'DATE(c.tanggal_catatan) = :date';
            $params[':date'] = $date;
        }

        $where = '';
        if (count($conditions) > 0) {
            $where = ' WHERE ' . implode(' AND ', $conditions);
        }

        $countStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM Catatan_Harian c' . $where
        );
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $query = 'SELECT c.*, u.nama_karyawan, u.no_bundy 
                  FROM Catatan_Harian c 
                  INNER JOIN Users u ON u.id_user = c.id_user' . $where . ' 
                  ORDER BY c.tanggal_catatan DESC 
                  LIMIT :limit OFFSET :offset';

        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => max((int) ceil($total / $perPage), 1),
        ];
    }

    public static function filter(?int $userId = null, ?string $date = null): array
    {
        $pdo = Connection::instance();

        $query = 'SELECT c.*, u.nama_karyawan, u.no_bundy 
                  FROM Catatan_Harian c 
                  INNER JOIN Users u ON u.id_user = c.id_user';
        $conditions = [];
        $params = [];

        if ($userId !== null) {
            $conditions[] = 'c.id_user = :userId';
            $params[':userId'] = $userId;
        }

        if ($date !== null) {
            $conditions[] = 'DATE(c.tanggal_catatan) = :date';
            $params[':date'] = $date;
        }

        if (count($conditions) > 0) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY c.tanggal_catatan DESC';

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll(): int
    {
        $pdo = Connection::instance();
        $stmt = $pdo->query('SELECT COUNT(*) AS total FROM Catatan_Harian');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($row['total'] ?? 0);
    }

    public static function latestMood(): ?array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->query(
            'SELECT c.mood, c.tanggal_catatan 
             FROM Catatan_Harian c 
             ORDER BY c.tanggal_catatan DESC 
             LIMIT 1'
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function statsByMood(): array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->query(
            'SELECT mood, COUNT(*) AS total 
             FROM Catatan_Harian 
             GROUP BY mood 
             ORDER BY total DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function topMood(): ?array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->query(
            'SELECT mood, COUNT(*) AS total 
             FROM Catatan_Harian 
             GROUP BY mood 
             ORDER BY total DESC, mood ASC 
             LIMIT 1'
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function dailyTrend(int $days = 7): array
    {
        $days = max($days, 1);

        $endDate = new \DateTimeImmutable('today');
        $startDate = $endDate->modify('-' . ($days - 1) . ' days');

        $pdo = Connection::instance();
        $stmt = $pdo->prepare(
            'SELECT DATE(tanggal_catatan) AS tanggal, COUNT(*) AS total 
             FROM Catatan_Harian 
             WHERE DATE(tanggal_catatan) BETWEEN :startDate AND :endDate 
             GROUP BY DATE(tanggal_catatan) 
             ORDER BY DATE(tanggal_catatan)'
        );
        $stmt->bindValue(':startDate', $startDate->format('Y-m-d'));
        $stmt->bindValue(':endDate', $endDate->format('Y-m-d'));
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $mapped = [];
        foreach ($rows as $row) {
            $mapped[$row['tanggal']] = (int) $row['total'];
        }

        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->modify('+1 day'));

        $result = [];
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $result[] = [
                'tanggal' => $key,
                'total' => $mapped[$key] ?? 0,
            ];
        }

        return $result;
    }

    public static function hasEntryForDate(int $userId, string $date): bool
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare(
            'SELECT 1
             FROM Catatan_Harian
             WHERE id_user = :userId AND DATE(tanggal_catatan) = :date
             LIMIT 1'
        );
        $stmt->execute([
            ':userId' => $userId,
            ':date' => $date,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public static function allWithUser(): array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->query(
            'SELECT c.*, u.nama_karyawan, u.no_bundy 
             FROM Catatan_Harian c 
             INNER JOIN Users u ON u.id_user = c.id_user 
             ORDER BY c.tanggal_catatan DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function paginateWithUser(string $search, int $page, int $perPage = 10): array
    {
        $pdo = Connection::instance();

        $page = max($page, 1);
        $offset = ($page - 1) * $perPage;

        $search = trim($search);
        $params = [];
        $where = '';

        if ($search !== '') {
            $where = ' WHERE (c.mood LIKE :search OR c.catatan_mood LIKE :search OR c.output_harian LIKE :search OR u.nama_karyawan LIKE :search OR u.no_bundy LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $baseQuery = ' FROM Catatan_Harian c INNER JOIN Users u ON u.id_user = c.id_user';

        $countStmt = $pdo->prepare('SELECT COUNT(*) AS total' . $baseQuery . $where);
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $dataStmt = $pdo->prepare(
            'SELECT c.*, u.nama_karyawan, u.no_bundy' . $baseQuery . $where . ' ORDER BY c.tanggal_catatan DESC LIMIT :limit OFFSET :offset'
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

    public static function find(int $id): ?array
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare(
            'SELECT c.*, u.nama_karyawan, u.no_bundy 
             FROM Catatan_Harian c 
             INNER JOIN Users u ON u.id_user = c.id_user 
             WHERE c.id_catatan = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare(
            'UPDATE Catatan_Harian 
             SET id_user = :id_user, mood = :mood, catatan_mood = :catatan_mood, output_harian = :output_harian, tanggal_catatan = :tanggal_catatan 
             WHERE id_catatan = :id'
        );

        return $stmt->execute([
            ':id_user' => $data['id_user'],
            ':mood' => $data['mood'],
            ':catatan_mood' => $data['catatan_mood'],
            ':output_harian' => $data['output_harian'],
            ':tanggal_catatan' => $data['tanggal_catatan'],
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Connection::instance();
        $stmt = $pdo->prepare('DELETE FROM Catatan_Harian WHERE id_catatan = :id');

        return $stmt->execute([':id' => $id]);
    }

    public static function report(array $filters = [], ?int $page = null, ?int $perPage = null): array
    {
        $pdo = Connection::instance();

        $conditions = [];
        $params = [];

        $start = $filters['start'] ?? null;
        if ($start) {
            $conditions[] = 'c.tanggal_catatan >= :start';
            $params[':start'] = self::normalizeDateTime($start, false);
        }

        $end = $filters['end'] ?? null;
        if ($end) {
            $conditions[] = 'c.tanggal_catatan <= :end';
            $params[':end'] = self::normalizeDateTime($end, true);
        }

        $userId = $filters['user'] ?? null;
        if ($userId !== null && (int) $userId > 0) {
            $conditions[] = 'c.id_user = :userId';
            $params[':userId'] = (int) $userId;
        }

        $mood = $filters['mood'] ?? null;
        if ($mood !== null && $mood !== '') {
            $conditions[] = 'c.mood = :mood';
            $params[':mood'] = $mood;
        }

        $baseQuery = ' FROM Catatan_Harian c INNER JOIN Users u ON u.id_user = c.id_user';
        if (count($conditions) > 0) {
            $baseQuery .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql = 'SELECT c.*, u.nama_karyawan, u.no_bundy' . $baseQuery . ' ORDER BY c.tanggal_catatan DESC';

        $usePagination = $page !== null && $perPage !== null && $perPage > 0;
        if ($usePagination) {
            $offset = max($page - 1, 0) * $perPage;
            $sql .= ' LIMIT :limit OFFSET :offset';
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        if ($usePagination) {
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->prepare('SELECT COUNT(*) AS total' . $baseQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRow = $countStmt->fetch(PDO::FETCH_ASSOC);
        $total = (int) ($totalRow['total'] ?? 0);

        return [
            'data' => $rows,
            'total' => $total,
        ];
    }

    private static function normalizeDateTime(string $value, bool $endOfDay = false): string
    {
        $value = trim($value);
        if ($value === '') {
            return $endOfDay ? '9999-12-31 23:59:59' : '0000-01-01 00:00:00';
        }

        if (strpos($value, 'T') !== false) {
            $value = str_replace('T', ' ', $value);
        }

        if (strlen($value) === 10) {
            $value .= $endOfDay ? ' 23:59:59' : ' 00:00:00';
        }

        return $value;
    }
}
