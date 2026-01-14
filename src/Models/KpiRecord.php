<?php

class KpiRecord {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO kpi_records ($columns) VALUES ($placeholders)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }
    
    public function findByUploadId($uploadId) {
        $stmt = $this->pdo->prepare("SELECT * FROM kpi_records WHERE upload_id = :upload_id");
        $stmt->execute(['upload_id' => $uploadId]);
        return $stmt->fetchAll();
    }

    public function findByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM kpi_records WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getGlobalStats() {
        $sql = "SELECT 
            COUNT(*) as total_records,
            SUM(gross_profit) as total_gross_profit,
            SUM(net_gp) as total_net_gp,
            SUM(total_gp_spiff_amt) as total_commission,
            SUM(final_payout) as total_payout,
            SUM(chargeback) as total_chargeback
        FROM kpi_records";
        return $this->pdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
    }

    public function getMonthlyStats() {
        // Join with uploads table to get the correct 'for_month' date
        $sql = "SELECT 
            u.for_month,
            SUM(k.net_gp) as total_net_gp,
            SUM(k.gross_profit) as total_gross_profit,
            SUM(k.total_gp_spiff_amt) as total_commission
        FROM kpi_records k
        JOIN uploads u ON k.upload_id = u.id
        WHERE u.status = 'active'
        GROUP BY u.for_month
        ORDER BY u.for_month ASC
        LIMIT 12"; // Last 12 months
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}
