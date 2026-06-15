<?php
/**
 * View: suggestion.box.report.php
 * Relatório de sugestões mais votadas — apenas Super Admin.
 */

$suggestions       = $data['suggestions']       ?? [];
$limit             = (int)($data['limit']             ?? 20);
$total_suggestions = (int)($data['total_suggestions'] ?? 0);
$total_votes       = (int)($data['total_votes']       ?? 0);
?>

<style>
:root {
    --rp-primary:  #d40000;
    --rp-bg:       #f4f5f7;
    --rp-card:     #ffffff;
    --rp-border:   #e2e4e9;
    --rp-text:     #1a1d23;
    --rp-muted:    #6b7280;
    --rp-gold:     #f59e0b;
    --rp-silver:   #9ca3af;
    --rp-bronze:   #b45309;
    --rp-tag-bg:   #eef2ff;
    --rp-tag-col:  #4f46e5;
    --rp-radius:   12px;
    --rp-radius-sm:6px;
    --rp-shadow:   0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.05);
}
#sb-report-wrap * { box-sizing: border-box; }
#sb-report-wrap {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: var(--rp-text);
    background: var(--rp-bg);
    min-height: 100vh;
    padding: 24px;
}

/* HEADER */
.rp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}
.rp-header h1 {
    font-size: 22px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.rp-header p {
    margin: 4px 0 0;
    font-size: 13px;
    color: var(--rp-muted);
}
.rp-badge-admin {
    background: #fef3c7;
    color: #d97706;
    border: 1px solid #fcd34d;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 600;
}

/* STATS */
.rp-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.rp-stat-card {
    background: var(--rp-card);
    border: 1px solid var(--rp-border);
    border-radius: var(--rp-radius-sm);
    padding: 16px 20px;
    box-shadow: var(--rp-shadow);
}
.rp-stat-card .rp-stat-val {
    font-size: 28px;
    font-weight: 800;
    color: var(--rp-primary);
    line-height: 1;
    margin-bottom: 4px;
}
.rp-stat-card .rp-stat-lbl {
    font-size: 12px;
    color: var(--rp-muted);
}

/* FILTRO LIMIT */
.rp-filter-bar {
    background: var(--rp-card);
    border: 1px solid var(--rp-border);
    border-radius: var(--rp-radius-sm);
    padding: 12px 18px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: var(--rp-muted);
    flex-wrap: wrap;
}
.rp-filter-bar label { font-weight: 600; color: var(--rp-text); }
.rp-limit-select {
    padding: 6px 10px;
    border: 1px solid var(--rp-border);
    border-radius: var(--rp-radius-sm);
    font-size: 13px;
    background: #fff;
    cursor: pointer;
    outline: none;
}
.rp-btn-export {
    margin-left: auto;
    padding: 7px 16px;
    background: var(--rp-primary);
    color: #fff;
    border: none;
    border-radius: var(--rp-radius-sm);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: background .2s;
}
.rp-btn-export:hover { background: #a80000; }

/* TABELA */
.rp-table-wrap {
    background: var(--rp-card);
    border: 1px solid var(--rp-border);
    border-radius: var(--rp-radius);
    box-shadow: var(--rp-shadow);
    overflow: hidden;
}
.rp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.rp-table thead tr {
    background: #f8f9fb;
    border-bottom: 2px solid var(--rp-border);
}
.rp-table th {
    padding: 12px 16px;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: var(--rp-muted);
    white-space: nowrap;
}
.rp-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--rp-border);
    vertical-align: middle;
}
.rp-table tbody tr:last-child td { border-bottom: none; }
.rp-table tbody tr:hover td { background: #fafbfc; }

/* Rank badge */
.rp-rank {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
}
.rp-rank.gold   { background: #fef3c7; color: var(--rp-gold); }
.rp-rank.silver { background: #f3f4f6; color: var(--rp-silver); }
.rp-rank.bronze { background: #fef3c7; color: var(--rp-bronze); }
.rp-rank.other  { background: #f3f4f6; color: var(--rp-muted); }

/* Vote bar */
.rp-vote-wrap { display: flex; align-items: center; gap: 10px; }
.rp-vote-num {
    font-size: 18px;
    font-weight: 800;
    color: var(--rp-primary);
    min-width: 36px;
    text-align: right;
}
.rp-bar-bg {
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
    flex: 1;
    min-width: 60px;
    overflow: hidden;
}
.rp-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--rp-primary) 0%, #ff6b35 100%);
    border-radius: 3px;
    transition: width .6s ease;
}

/* Tags */
.rp-tags { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
.rp-tag {
    padding: 2px 8px;
    border-radius: 20px;
    background: var(--rp-tag-bg);
    color: var(--rp-tag-col);
    font-size: 11px;
    font-weight: 500;
}

.rp-title { font-weight: 600; font-size: 14px; color: var(--rp-text); }
.rp-author { font-size: 12px; color: var(--rp-muted); margin-top: 2px; }
.rp-date   { font-size: 12px; color: var(--rp-muted); white-space: nowrap; }

.rp-empty {
    padding: 48px 24px;
    text-align: center;
    color: var(--rp-muted);
    font-size: 15px;
}

@media (max-width: 700px) {
    #sb-report-wrap { padding: 12px; }
    .rp-table th:nth-child(4), .rp-table td:nth-child(4) { display: none; }
}
</style>

<div id="sb-report-wrap">

    <!-- HEADER -->
    <div class="rp-header">
        <div>
            <h1>
                📊 Relatório de Sugestões
            </h1>
            <p>Visão consolidada das ideias mais votadas pela equipe.</p>
        </div>
        <span class="rp-badge-admin">⚡ Super Admin</span>
    </div>

    <!-- STATS -->
    <div class="rp-stats">
        <div class="rp-stat-card">
            <div class="rp-stat-val"><?= $total_suggestions ?></div>
            <div class="rp-stat-lbl">Sugestões totais</div>
        </div>
        <div class="rp-stat-card">
            <div class="rp-stat-val"><?= $total_votes ?></div>
            <div class="rp-stat-lbl">Votos registrados</div>
        </div>
        <div class="rp-stat-card">
            <div class="rp-stat-val"><?= $total_suggestions > 0 ? round($total_votes / $total_suggestions, 1) : 0 ?></div>
            <div class="rp-stat-lbl">Média de votos</div>
        </div>
        <div class="rp-stat-card">
            <div class="rp-stat-val"><?= count($suggestions) ?></div>
            <div class="rp-stat-lbl">Exibindo agora</div>
        </div>
    </div>

    <!-- FILTRO -->
    <div class="rp-filter-bar">
        <label>Exibir top:</label>
        <select class="rp-limit-select" id="rp-limit-sel">
            <?php foreach ([10, 20, 50, 100] as $lv): ?>
            <option value="<?= $lv ?>" <?= $limit === $lv ? 'selected' : '' ?>><?= $lv ?> sugestões</option>
            <?php endforeach; ?>
        </select>
        <span style="color:var(--rp-muted)">ordenadas por votos</span>
        <button class="rp-btn-export" onclick="rpExportCSV()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Exportar CSV
        </button>
    </div>

    <!-- TABELA -->
    <div class="rp-table-wrap">
        <?php if (empty($suggestions)): ?>
        <div class="rp-empty">Nenhuma sugestão cadastrada ainda.</div>
        <?php else: ?>
        <table class="rp-table" id="rp-table">
            <thead>
                <tr>
                    <th style="width:52px">#</th>
                    <th>Sugestão</th>
                    <th style="width:180px">Votos</th>
                    <th style="width:140px">Autor</th>
                    <th style="width:100px">Data</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $maxVotes = (int)($suggestions[0]['vote_count'] ?? 1);
            if ($maxVotes < 1) $maxVotes = 1;
            foreach ($suggestions as $i => $sug):
                $rank = $i + 1;
                $rankClass = $rank === 1 ? 'gold' : ($rank === 2 ? 'silver' : ($rank === 3 ? 'bronze' : 'other'));
                $rankIcon  = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : $rank));
                $voteCount = (int)$sug['vote_count'];
                $barPct    = round($voteCount / $maxVotes * 100);
                $author    = htmlspecialchars(trim($sug['name'] . ' ' . $sug['surname']) ?: $sug['username']);
                $date      = date('d/m/Y', strtotime($sug['created_at']));
            ?>
            <tr>
                <td><span class="rp-rank <?= $rankClass ?>"><?= $rankIcon ?></span></td>
                <td>
                    <div class="rp-title"><?= htmlspecialchars($sug['title']) ?></div>
                    <?php if (!empty($sug['description'])): ?>
                    <div class="rp-author" style="margin-top:4px;font-size:12px;color:#6b7280;max-width:420px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">
                        <?= htmlspecialchars($sug['description']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($sug['tags'])): ?>
                    <div class="rp-tags">
                        <?php foreach ($sug['tags'] as $tag): ?>
                        <span class="rp-tag">#<?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="rp-vote-wrap">
                        <div class="rp-vote-num"><?= $voteCount ?></div>
                        <div class="rp-bar-bg">
                            <div class="rp-bar-fill" style="width:<?= $barPct ?>%"></div>
                        </div>
                    </div>
                </td>
                <td class="rp-author"><?= $author ?></td>
                <td class="rp-date"><?= $date ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<script>
(function() {
    'use strict';

    // ---- Mudar limite ----
    document.getElementById('rp-limit-sel').addEventListener('change', function() {
        window.location.href = 'suggestion.box.report?limit=' + encodeURIComponent(this.value);
    });

    // ---- Exportar CSV ----
    window.rpExportCSV = function() {
        var rows = [['#', 'Título', 'Descrição', 'Tags', 'Votos', 'Autor', 'Data']];

        var data = <?= json_encode(array_map(function($sug) {
            return [
                $sug['title'],
                $sug['description'] ?? '',
                implode('; ', $sug['tags'] ?? []),
                (int)$sug['vote_count'],
                trim(($sug['name'] ?? '') . ' ' . ($sug['surname'] ?? '')) ?: ($sug['username'] ?? ''),
                date('d/m/Y', strtotime($sug['created_at'])),
            ];
        }, $suggestions)) ?>;

        data.forEach(function(row, i) {
            rows.push([i + 1].concat(row));
        });

        var csv = rows.map(function(r) {
            return r.map(function(v) {
                return '"' + String(v).replace(/"/g, '""') + '"';
            }).join(',');
        }).join('\r\n');

        var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'suggestion-box-report-' + new Date().toISOString().slice(0,10) + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };
})();
</script>
