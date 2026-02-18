<?php
require_once __DIR__ . '/../config/database.php';

class Dashboard {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getStats() {
        
        $classCount = $this->db->query("SELECT COUNT(*) as total FROM ict_categories")->fetch_assoc()['total'];
        $invCount = $this->db->query("SELECT SUM(quantity) as total FROM ict_inventory")->fetch_assoc()['total'] ?? 0;
        $userCount = $this->db->query("SELECT COUNT(*) as total FROM ict_users")->fetch_assoc()['total'] ?? 0;
        $activeSessions = $this->db->query("SELECT COUNT(*) as total FROM ict_logs WHERE time_out IS NULL")->fetch_assoc()['total'] ?? 0;

        $avgDaily = $this->db->query("SELECT AVG(daily_total) as avg FROM (
            SELECT COUNT(DISTINCT user_id) as daily_total FROM ict_logs GROUP BY DATE(time_in)
        ) as daily_sub")->fetch_assoc()['avg'] ?? 0;

        $avgWeekly = $this->db->query("SELECT AVG(weekly_total) as avg FROM (
            SELECT COUNT(DISTINCT user_id) as weekly_total FROM ict_logs GROUP BY YEARWEEK(time_in)
        ) as weekly_sub")->fetch_assoc()['avg'] ?? 0;

        $chartSql = "SELECT DATE(time_in) as day, COUNT(*) as count 
                     FROM ict_logs 
                     WHERE time_in >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                     GROUP BY DATE(time_in) ORDER BY day ASC";
        $result = $this->db->query($chartSql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $byDay = [];
        foreach ($rows as $r) {
            $byDay[$r['day']] = (int) $r['count'];
        }
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $chartData[] = [
                'day'   => $d,
                'label' => date('M j', strtotime($d)),
                'count' => isset($byDay[$d]) ? $byDay[$d] : 0
            ];
        }

        return [
            'total_classes' => $classCount,
            'total_items'   => $invCount,
            'total_users'   => $userCount,
            'active_logs'   => $activeSessions,
            'avg_daily'     => round($avgDaily, 1),
            'avg_weekly'    => round($avgWeekly, 1),
            'chart_data'    => $chartData
        ];
    }
}