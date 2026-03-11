<?php
// This partial is included in both admin and agent leaderboard views
// It expects $leaderboardData to be set with all necessary data

$rankings = $leaderboardData['rankings'] ?? [];
$stats = $leaderboardData['stats'] ?? [];
$currentUserRank = $leaderboardData['currentUserRank'] ?? null;
$currentUserData = $leaderboardData['currentUserData'] ?? null;
$isAdmin = $leaderboardData['isAdmin'] ?? false;
$upload = $leaderboardData['upload'] ?? [];

// Helper function to get rank badge
function getRankBadge($rank) {
    if ($rank == 1) return '🥇';
    if ($rank == 2) return '🥈';
    if ($rank == 3) return '🥉';
    if ($rank <= 10) return '⭐';
    return '';
}

// Helper function to get motivational message
function getMotivationalMessage($rank, $attainment) {
    if ($rank <= 3) return "🔥 Outstanding Performance! You're a champion!";
    if ($rank <= 10) return "⭐ Excellent work! You're in the top 10!";
    if ($rank <= 25) return "🎯 Great job! You're in the top tier!";
    if ($rank <= 50) return "💪 Nice work! Keep climbing!";
    if ($attainment >= 100) return "✨ Above 100%! You're exceeding expectations!";
    return "🚀 Every day is a new opportunity to excel!";
}

// Helper function to format percentage with color
function formatPercentage($value) {
    $color = 'text-gray-600 dark:text-gray-400';
    if ($value >= 125) $color = 'text-purple-600 dark:text-purple-400 font-bold';
    elseif ($value >= 100) $color = 'text-green-600 dark:text-green-400 font-semibold';
    elseif ($value >= 75) $color = 'text-blue-600 dark:text-blue-400';
    elseif ($value >= 50) $color = 'text-yellow-600 dark:text-yellow-400';
    else $color = 'text-red-600 dark:text-red-400';

    return '<span class="' . $color . '">' . number_format($value, 2) . '%</span>';
}
?>

<div class="p-8">
    <!-- Header -->
    <div class="mb-8 animate-slide-down">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                    🏆 Performance Leaderboard
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Rankings for <?= date('F j, Y', strtotime($upload['upload_date'])) ?>
                    <?php if ($isAdmin): ?>
                    <span class="ml-2 text-sm">(Uploaded <?= date('g:i A', strtotime($upload['uploaded_at'])) ?>)</span>
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($isAdmin): ?>
            <div class="flex gap-3">
                <a href="/sales-kpi-dashboard/admin/rankings" class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                    Manage Rankings
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Team Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-lg border border-gray-200 dark:border-slate-700">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Agents</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white"><?= $stats['total_agents'] ?? 0 ?></div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-lg border border-gray-200 dark:border-slate-700">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Team Average</div>
            <div class="text-3xl font-bold"><?= formatPercentage($stats['avg_attainment'] ?? 0) ?></div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-lg border border-gray-200 dark:border-slate-700">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Above 100%</div>
            <div class="text-3xl font-bold text-green-600 dark:text-green-400"><?= $stats['above_hundred_count'] ?? 0 ?></div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-lg border border-gray-200 dark:border-slate-700">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Top Score</div>
            <div class="text-3xl font-bold"><?= formatPercentage($stats['max_attainment'] ?? 0) ?></div>
        </div>
    </div>

    <!-- Current User Position (for agents) -->
    <?php if (!$isAdmin && $currentUserData): ?>
    <div class="mb-8 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-6 text-white shadow-xl animate-pulse-slow">
        <div class="flex justify-between items-center">
            <div>
                <div class="text-white/80 text-sm mb-1">Your Current Position</div>
                <div class="flex items-center gap-4">
                    <div class="text-5xl font-bold">#<?= $currentUserRank ?></div>
                    <div class="text-2xl"><?= getRankBadge($currentUserRank) ?></div>
                </div>
                <div class="mt-2 text-white/90"><?= getMotivationalMessage($currentUserRank, $currentUserData['total_attainment_pct']) ?></div>
            </div>
            <div class="text-right">
                <div class="text-white/80 text-sm mb-1">Your Score</div>
                <div class="text-4xl font-bold"><?= number_format($currentUserData['total_attainment_pct'], 2) ?>%</div>
                <?php if (isset($currentUserData['rank_change']) && $currentUserData['rank_change'] !== null): ?>
                <div class="mt-2">
                    <?php if ($currentUserData['rank_change'] > 0): ?>
                    <span class="text-green-300">↑ <?= abs($currentUserData['rank_change']) ?> positions</span>
                    <?php elseif ($currentUserData['rank_change'] < 0): ?>
                    <span class="text-red-300">↓ <?= abs($currentUserData['rank_change']) ?> positions</span>
                    <?php else: ?>
                    <span class="text-white/70">− No change</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Top 3 Podium -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">🏆 Top Performers</h2>
        <div class="flex flex-col md:flex-row gap-6 justify-center items-end">
            <?php
            $podiumRanks = array_slice($rankings, 0, 3);
            // Reorder for desktop display: Silver (2nd), Gold (1st), Bronze (3rd)
            $desktopOrder = [];
            if (isset($podiumRanks[1])) $desktopOrder[] = ['rank' => $podiumRanks[1], 'index' => 1, 'height' => 'h-64'];
            if (isset($podiumRanks[0])) $desktopOrder[] = ['rank' => $podiumRanks[0], 'index' => 0, 'height' => 'h-72'];
            if (isset($podiumRanks[2])) $desktopOrder[] = ['rank' => $podiumRanks[2], 'index' => 2, 'height' => 'h-60'];

            foreach ($desktopOrder as $item):
                $rank = $item['rank'];
                $index = $item['index'];
                $height = $item['height'];
                $medalClass = $index == 0 ? 'medal-gold' : ($index == 1 ? 'medal-silver' : 'medal-bronze');
                $isCurrentUser = !$isAdmin && $rank['user_id'] == $_SESSION['user_id'];
            ?>
            <div class="flex-1 max-w-xs">
                <div class="<?= $medalClass ?> <?= $height ?> rounded-xl p-6 text-white shadow-xl transform hover:scale-105 transition <?= $isCurrentUser ? 'ring-4 ring-yellow-400 animate-pulse-slow' : '' ?> flex flex-col justify-center items-center relative">
                    <div class="absolute -top-4 -right-4 w-14 h-14 rounded-full bg-white shadow-lg flex items-center justify-center text-3xl">
                        <?= getRankBadge($rank['overall_rank']) ?>
                    </div>
                    <?php if ($isCurrentUser): ?>
                    <div class="absolute top-2 left-2 bg-yellow-400 text-black px-2 py-1 rounded text-xs font-bold">YOU</div>
                    <?php endif; ?>
                    <div class="text-center">
                        <div class="text-5xl font-bold mb-2">#<?= $rank['overall_rank'] ?></div>
                        <div class="text-xl font-semibold mb-2"><?= htmlspecialchars($rank['employee_name']) ?></div>
                        <div class="text-4xl font-bold"><?= number_format($rank['total_attainment_pct'], 2) ?>%</div>
                        <?php if (isset($rank['rank_change']) && $rank['rank_change'] !== null): ?>
                        <div class="mt-3 text-sm">
                            <?php if ($rank['rank_change'] > 0): ?>
                            <span class="bg-green-500/30 px-2 py-1 rounded">↑ <?= abs($rank['rank_change']) ?></span>
                            <?php elseif ($rank['rank_change'] < 0): ?>
                            <span class="bg-red-500/30 px-2 py-1 rounded">↓ <?= abs($rank['rank_change']) ?></span>
                            <?php else: ?>
                            <span class="bg-white/20 px-2 py-1 rounded">−</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Full Rankings Table -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Complete Rankings (<?= count($rankings) ?> agents)</h2>
        </div>

        <!-- Search Bar -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
            <input type="text"
                   id="searchInput"
                   placeholder="Search by name..."
                   class="w-full max-w-md px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-slate-700 text-gray-900 dark:text-white">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Premium<br>Unlimited
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            VHI<br>Conv
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CSGA</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            VMP<br>Take
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            VZ<br>Perks
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Traffic<br>GP/Cust
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-700" id="rankingsTableBody">
                    <?php
                    $displayCount = 0;
                    foreach ($rankings as $rank):
                    $displayCount++;
                        $isCurrentUser = !$isAdmin && $rank['user_id'] == $_SESSION['user_id'];
                        $rowClass = $isCurrentUser ? 'bg-purple-50 dark:bg-purple-900/20 font-semibold' : 'hover:bg-gray-50 dark:hover:bg-slate-900/30';
                    ?>
                    <tr class="<?= $rowClass ?> transition ranking-row" data-name="<?= strtolower($rank['employee_name']) ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold text-gray-900 dark:text-white"><?= $rank['overall_rank'] ?></span>
                                <span class="text-xl"><?= getRankBadge($rank['overall_rank']) ?></span>
                                <?php if (isset($rank['rank_change']) && $rank['rank_change'] !== null): ?>
                                <span class="text-xs">
                                    <?php if ($rank['rank_change'] > 0): ?>
                                    <span class="text-green-600 dark:text-green-400">↑<?= abs($rank['rank_change']) ?></span>
                                    <?php elseif ($rank['rank_change'] < 0): ?>
                                    <span class="text-red-600 dark:text-red-400">↓<?= abs($rank['rank_change']) ?></span>
                                    <?php else: ?>
                                    <span class="text-gray-400">−</span>
                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                <?= htmlspecialchars($rank['employee_name']) ?>
                                <?php if ($isCurrentUser): ?>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200">YOU</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <?= formatPercentage($rank['total_attainment_pct']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <?= formatPercentage($rank['hpu_attainment_pct'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <?= formatPercentage($rank['vhi_conv_attainment_pct'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <?= formatPercentage($rank['csga_attainment_pct'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <?= formatPercentage($rank['vmp_take_attainment_pct'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <?= formatPercentage($rank['vz_perks_attainment_pct'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <?= formatPercentage($rank['traffic_gp_cust_attainment_pct'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <button onclick="toggleDetails(<?= $rank['overall_rank'] ?>)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                <svg id="icon-<?= $rank['overall_rank'] ?>" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <!-- Details Row (Hidden by default) -->
                    <tr id="details-<?= $rank['overall_rank'] ?>" class="hidden bg-gray-50 dark:bg-slate-900/30">
                        <td colspan="11" class="px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="space-y-2">
                                    <h4 class="font-semibold text-gray-700 dark:text-gray-300">Premium Unlimited</h4>
                                    <div class="text-gray-600 dark:text-gray-400">
                                        Actual: <?= number_format($rank['hpu_actual_pct'] ?? 0, 2) ?>% |
                                        Goal: <?= number_format($rank['hpu_goal_pct'] ?? 0, 2) ?>%
                                    </div>
                                    <div class="text-gray-600 dark:text-gray-400">
                                        VHI Conv - Actual: <?= number_format($rank['vhi_conv_actual_pct'] ?? 0, 2) ?>% |
                                        Goal: <?= number_format($rank['vhi_conv_goal_pct'] ?? 0, 2) ?>%
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <h4 class="font-semibold text-gray-700 dark:text-gray-300">Service Metrics</h4>
                                    <div class="text-gray-600 dark:text-gray-400">
                                        CSGA - Actual: <?= number_format($rank['csga_actual_pct'] ?? 0, 2) ?>% |
                                        Goal: <?= number_format($rank['csga_goal_pct'] ?? 0, 2) ?>%
                                    </div>
                                    <div class="text-gray-600 dark:text-gray-400">
                                        VMP Take - Actual: <?= number_format($rank['vmp_take_actual'] ?? 0, 2) ?> |
                                        Goal: <?= number_format($rank['vmp_take_goal'] ?? 0, 2) ?>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <h4 class="font-semibold text-gray-700 dark:text-gray-300">Revenue Metrics</h4>
                                    <div class="text-gray-600 dark:text-gray-400">
                                        VZ Perks - Actual: <?= number_format($rank['vz_perks_actual'] ?? 0, 2) ?> |
                                        Goal: <?= number_format($rank['vz_perks_goal'] ?? 0, 2) ?>
                                    </div>
                                    <div class="text-gray-600 dark:text-gray-400">
                                        Traffic GP - Actual: $<?= number_format($rank['traffic_gp_actual'] ?? 0, 2) ?> |
                                        Goal: $<?= number_format($rank['traffic_gp_goal'] ?? 0, 2) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Debug: Total rows rendered: <?= $displayCount ?> -->
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.ranking-row');

    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const detailsRow = row.nextElementSibling;

        if (name.includes(searchTerm)) {
            row.style.display = '';
            // Keep details row visibility state if main row is visible
        } else {
            row.style.display = 'none';
            // Hide details row if main row is hidden
            if (detailsRow && detailsRow.id && detailsRow.id.startsWith('details-')) {
                detailsRow.classList.add('hidden');
            }
        }
    });
});

// Toggle details function
function toggleDetails(rank) {
    const detailsRow = document.getElementById('details-' + rank);
    const icon = document.getElementById('icon-' + rank);

    if (detailsRow) {
        detailsRow.classList.toggle('hidden');

        // Rotate icon
        if (detailsRow.classList.contains('hidden')) {
            icon.style.transform = 'rotate(0deg)';
        } else {
            icon.style.transform = 'rotate(180deg)';
        }
    }
}

// Smooth scroll to user position
<?php if (!$isAdmin && $currentUserData): ?>
setTimeout(() => {
    const userRow = document.querySelector('.bg-purple-50, .dark\\:bg-purple-900\\/20');
    if (userRow) {
        userRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}, 500);
<?php endif; ?>
</script>