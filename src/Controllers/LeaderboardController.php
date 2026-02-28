<?php

require_once __DIR__ . '/../Models/RankingUpload.php';
require_once __DIR__ . '/../Models/RankingRecord.php';

class LeaderboardController {

    public function __construct() {
        session_start();

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: /sales-kpi-dashboard/login");
            exit;
        }
    }

    public function adminView() {
        // Verify admin role
        if ($_SESSION['user_role'] !== 'admin') {
            header("Location: /sales-kpi-dashboard/agent/leaderboard");
            exit;
        }

        $this->displayLeaderboard(true);
    }

    public function agentView() {
        $this->displayLeaderboard(false);
    }

    private function displayLeaderboard($isAdmin = false) {
        $uploadModel = new RankingUpload();
        $recordModel = new RankingRecord();

        // Get the latest upload or specific date if requested
        $requestedDate = $_GET['date'] ?? null;

        if ($requestedDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate)) {
            $currentUpload = $uploadModel->findByDate($requestedDate);
        } else {
            $currentUpload = $uploadModel->findLatest();
        }

        if (!$currentUpload) {
            // No data available
            $viewFile = $isAdmin ? 'admin/leaderboard.php' : 'agent/leaderboard.php';
            require __DIR__ . '/../Views/' . $viewFile;
            return;
        }

        // Get all rankings for this upload
        $rankings = $recordModel->findByUploadId($currentUpload['id']);

        // Get current user's ranking
        $currentUserRank = null;
        $currentUserData = null;
        if (!$isAdmin || isset($_GET['preview_as_agent'])) {
            foreach ($rankings as $rank) {
                if ($rank['user_id'] == $_SESSION['user_id']) {
                    $currentUserRank = $rank['overall_rank'];
                    $currentUserData = $rank;
                    break;
                }
            }
        }

        // Get leaderboard statistics
        $stats = $recordModel->getLeaderboardStats($currentUpload['id']);

        // Get perfect scorers (125% on any metric)
        $perfectScorers = $recordModel->getPerfectScorers($currentUpload['id']);

        // Get previous upload for comparison (if exists)
        $allUploads = $uploadModel->findAll();
        $previousUpload = null;
        if (count($allUploads) > 1) {
            $previousUpload = $allUploads[1]; // Second most recent
        }

        // Get rank changes for all users if previous upload exists
        $rankChanges = [];
        if ($previousUpload) {
            $previousRankings = $recordModel->findByUploadId($previousUpload['id']);
            $previousRankMap = [];
            foreach ($previousRankings as $prev) {
                if ($prev['user_id']) {
                    $previousRankMap[$prev['user_id']] = $prev['overall_rank'];
                }
            }

            foreach ($rankings as &$rank) {
                if ($rank['user_id'] && isset($previousRankMap[$rank['user_id']])) {
                    $rank['previous_rank'] = $previousRankMap[$rank['user_id']];
                    $rank['rank_change'] = $previousRankMap[$rank['user_id']] - $rank['overall_rank'];
                } else {
                    $rank['previous_rank'] = null;
                    $rank['rank_change'] = null;
                }
            }
        }

        // Prepare data for view
        $leaderboardData = [
            'upload' => $currentUpload,
            'rankings' => $rankings,
            'stats' => $stats,
            'currentUserRank' => $currentUserRank,
            'currentUserData' => $currentUserData,
            'perfectScorers' => $perfectScorers,
            'isAdmin' => $isAdmin
        ];

        // Load appropriate view
        $viewFile = $isAdmin ? 'admin/leaderboard.php' : 'agent/leaderboard.php';
        require __DIR__ . '/../Views/' . $viewFile;
    }

    public function search() {
        if (!isset($_GET['upload_id']) || !isset($_GET['q'])) {
            echo json_encode([]);
            exit;
        }

        $uploadId = intval($_GET['upload_id']);
        $searchTerm = $_GET['q'];

        $recordModel = new RankingRecord();
        $results = $recordModel->searchByName($uploadId, $searchTerm);

        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
}