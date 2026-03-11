<?php

require_once __DIR__ . '/../../config/database.php';

class RankingRecord {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO ranking_records (
                ranking_upload_id, user_id, employee_name, overall_rank, total_attainment_pct,
                hpu_actual_pct, hpu_goal_pct, hpu_attainment_pct,
                vhi_conv_actual_pct, vhi_conv_goal_pct, vhi_conv_attainment_pct,
                csga_actual_pct, csga_goal_pct, csga_attainment_pct,
                vmp_take_actual, vmp_take_goal, vmp_take_attainment_pct,
                vz_perks_actual, vz_perks_goal, vz_perks_attainment_pct,
                traffic_gp_actual, traffic_gp_goal, traffic_gp_cust_attainment_pct
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['ranking_upload_id'],
            $data['user_id'],
            $data['employee_name'],
            $data['overall_rank'],
            $data['total_attainment_pct'],
            $data['hpu_actual_pct'],
            $data['hpu_goal_pct'],
            $data['hpu_attainment_pct'],
            $data['vhi_conv_actual_pct'],
            $data['vhi_conv_goal_pct'],
            $data['vhi_conv_attainment_pct'],
            $data['csga_actual_pct'],
            $data['csga_goal_pct'],
            $data['csga_attainment_pct'],
            $data['vmp_take_actual'],
            $data['vmp_take_goal'],
            $data['vmp_take_attainment_pct'],
            $data['vz_perks_actual'],
            $data['vz_perks_goal'],
            $data['vz_perks_attainment_pct'],
            $data['traffic_gp_actual'],
            $data['traffic_gp_goal'],
            $data['traffic_gp_cust_attainment_pct']
        ]);
    }

    public function findByUploadId($uploadId) {
        $stmt = $this->pdo->prepare("
            SELECT rr.*, u.name as user_name
            FROM ranking_records rr
            LEFT JOIN users u ON rr.user_id = u.id
            WHERE rr.ranking_upload_id = ?
            ORDER BY rr.overall_rank ASC
        ");
        $stmt->execute([$uploadId]);
        return $stmt->fetchAll();
    }

    public function findByUserId($userId) {
        $stmt = $this->pdo->prepare("
            SELECT rr.*, ru.upload_date
            FROM ranking_records rr
            JOIN ranking_uploads ru ON rr.ranking_upload_id = ru.id
            WHERE rr.user_id = ?
            ORDER BY ru.upload_date DESC
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findUserLatestRank($userId) {
        $stmt = $this->pdo->prepare("
            SELECT rr.*, ru.upload_date
            FROM ranking_records rr
            JOIN ranking_uploads ru ON rr.ranking_upload_id = ru.id
            WHERE rr.user_id = ?
            ORDER BY ru.upload_date DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getLeaderboardStats($uploadId) {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) as total_agents,
                AVG(total_attainment_pct) as avg_attainment,
                COUNT(CASE WHEN total_attainment_pct >= 100 THEN 1 END) as above_hundred_count,
                MAX(total_attainment_pct) as max_attainment,
                MIN(total_attainment_pct) as min_attainment
            FROM ranking_records
            WHERE ranking_upload_id = ?
        ");
        $stmt->execute([$uploadId]);
        return $stmt->fetch();
    }

    public function findTopPerformers($uploadId, $limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT rr.*, u.name as user_name
            FROM ranking_records rr
            LEFT JOIN users u ON rr.user_id = u.id
            WHERE rr.ranking_upload_id = :uploadId
            ORDER BY rr.overall_rank ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':uploadId', $uploadId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function searchByName($uploadId, $searchTerm) {
        $stmt = $this->pdo->prepare("
            SELECT rr.*, u.name as user_name
            FROM ranking_records rr
            LEFT JOIN users u ON rr.user_id = u.id
            WHERE rr.ranking_upload_id = ?
            AND (rr.employee_name LIKE ? OR u.name LIKE ?)
            ORDER BY rr.overall_rank ASC
        ");
        $searchPattern = '%' . $searchTerm . '%';
        $stmt->execute([$uploadId, $searchPattern, $searchPattern]);
        return $stmt->fetchAll();
    }

    public function getUserRankComparison($userId, $currentUploadId, $previousUploadId = null) {
        $currentRank = $this->getUserRankInUpload($userId, $currentUploadId);

        if ($previousUploadId) {
            $previousRank = $this->getUserRankInUpload($userId, $previousUploadId);
            if ($currentRank && $previousRank) {
                return [
                    'current_rank' => $currentRank['overall_rank'],
                    'previous_rank' => $previousRank['overall_rank'],
                    'change' => $previousRank['overall_rank'] - $currentRank['overall_rank']
                ];
            }
        }

        return [
            'current_rank' => $currentRank ? $currentRank['overall_rank'] : null,
            'previous_rank' => null,
            'change' => null
        ];
    }

    private function getUserRankInUpload($userId, $uploadId) {
        $stmt = $this->pdo->prepare("
            SELECT overall_rank
            FROM ranking_records
            WHERE user_id = ? AND ranking_upload_id = ?
        ");
        $stmt->execute([$userId, $uploadId]);
        return $stmt->fetch();
    }

    public function getPerfectScorers($uploadId) {
        $stmt = $this->pdo->prepare("
            SELECT rr.*, u.name as user_name
            FROM ranking_records rr
            LEFT JOIN users u ON rr.user_id = u.id
            WHERE rr.ranking_upload_id = ?
            AND (
                hpu_attainment_pct >= 125 OR
                vhi_conv_attainment_pct >= 125 OR
                csga_attainment_pct >= 125 OR
                vmp_take_attainment_pct >= 125 OR
                vz_perks_attainment_pct >= 125 OR
                traffic_gp_cust_attainment_pct >= 125
            )
            ORDER BY rr.overall_rank ASC
        ");
        $stmt->execute([$uploadId]);
        return $stmt->fetchAll();
    }
}