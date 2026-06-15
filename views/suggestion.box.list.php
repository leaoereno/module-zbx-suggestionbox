<?php declare(strict_types = 1);
/**
 * View: suggestion.box.list.php
 * Página principal do Suggestion Box.
 */

$suggestions  = $data['suggestions']  ?? [];
$allTags      = $data['allTags']      ?? [];
$search       = htmlspecialchars($data['search']    ?? '', ENT_QUOTES);
$activeTag    = htmlspecialchars($data['activeTag'] ?? '', ENT_QUOTES);
$sort         = $data['sort']         ?? 'votes';
$currentUser  = $data['currentUser']  ?? [];
$isSuperAdmin = $data['isSuperAdmin'] ?? false;
$currentUserId = (int)($currentUser['userid'] ?? 0);
?>
<style>
/* ========================================================
   SUGGESTION BOX — Design System
   ======================================================== */
:root {
    --sb-primary:      #d40000;
    --sb-primary-dark: #a80000;
    --sb-primary-soft: rgba(212,0,0,0.08);
    --sb-accent:       #ff6b35;
    --sb-success:      #22c55e;
    --sb-bg:           #f4f5f7;
    --sb-card:         #ffffff;
    --sb-border:       #e2e4e9;
    --sb-text:         #1a1d23;
    --sb-muted:        #6b7280;
    --sb-tag-bg:       #eef2ff;
    --sb-tag-color:    #4f46e5;
    --sb-voted-bg:     #fef3c7;
    --sb-voted-color:  #d97706;
    --sb-radius:       12px;
    --sb-radius-sm:    6px;
    --sb-shadow:       0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.05);
    --sb-shadow-hover: 0 4px 12px rgba(0,0,0,.12), 0 8px 24px rgba(0,0,0,.08);
    --sb-transition:   0.2s ease;
}

#suggestion-box-wrap * { box-sizing: border-box; }

#suggestion-box-wrap {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: var(--sb-text);
    background: var(--sb-bg);
    min-height: 100vh;
    padding: 24px;
}

/* ---- HEADER ---- */
.sb-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}
.sb-header-left h1 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 4px;
    color: var(--sb-text);
    display: flex;
    align-items: center;
    gap: 10px;
}
.sb-header-left h1 span.sb-icon {
    background: var(--sb-primary);
    color: #fff;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.sb-header-left p {
    margin: 0;
    font-size: 13px;
    color: var(--sb-muted);
}

/* ---- NOVO BOTÃO ---- */
.sb-btn-new {
    background: var(--sb-primary);
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: var(--sb-radius-sm);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background var(--sb-transition), transform var(--sb-transition);
    text-decoration: none;
    white-space: nowrap;
}
.sb-btn-new:hover {
    background: var(--sb-primary-dark);
    transform: translateY(-1px);
}

/* ---- CONTROLES (busca + filtros) ---- */
.sb-controls {
    background: var(--sb-card);
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius);
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
}
.sb-search-wrap {
    position: relative;
    flex: 1;
    min-width: 220px;
}
.sb-search-wrap svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sb-muted);
    width: 16px;
    height: 16px;
    pointer-events: none;
}
.sb-search {
    width: 100%;
    padding: 9px 12px 9px 44px !important;
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius-sm);
    font-size: 14px;
    color: var(--sb-text);
    background: #fff;
    outline: none;
    transition: border-color var(--sb-transition);
}
.sb-search:focus { border-color: var(--sb-primary); }

.sb-sort-wrap { display: flex; align-items: center; gap: 8px; }
.sb-sort-wrap label { font-size: 13px; color: var(--sb-muted); white-space: nowrap; }
.sb-sort-select {
    padding: 8px 12px !important;
    line-height: normal !important;
    height: auto !important;
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius-sm);
    font-size: 13px;
    color: var(--sb-text);
    background: #fff;
    cursor: pointer;
    outline: none;
    text-align: center !important;
    text-align-last: center !important;
}
.sb-sort-select:focus { border-color: var(--sb-primary); }

/* ---- TAG PILLS ---- */
.sb-tags-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
    align-items: center;
}
.sb-tags-filter-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--sb-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-right: 4px;
}
.sb-tag-pill {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all var(--sb-transition);
    background: var(--sb-tag-bg);
    color: var(--sb-tag-color);
    user-select: none;
}
.sb-tag-pill:hover { opacity: 0.8; }
.sb-tag-pill.active {
    background: var(--sb-tag-color);
    color: #fff;
    border-color: var(--sb-tag-color);
}
.sb-tag-pill.clear {
    background: transparent;
    color: var(--sb-muted);
    border: 1px solid var(--sb-border);
}
.sb-tag-pill.clear:hover { border-color: var(--sb-primary); color: var(--sb-primary); }

/* ---- GRID DE CARDS ---- */
.sb-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}

.sb-empty {
    grid-column: 1/-1;
    text-align: center !important;
    padding: 64px 20px;
    color: var(--sb-muted);
}
.sb-empty svg { width: 56px; height: 56px; margin-bottom: 16px; opacity: .3; }
.sb-empty p { font-size: 15px; margin: 0; }

/* ---- CARD ---- */
.sb-card {
    background: var(--sb-card);
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius);
    padding: 0;
    box-shadow: var(--sb-shadow);
    transition: box-shadow var(--sb-transition), transform var(--sb-transition);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.sb-card:hover {
    box-shadow: var(--sb-shadow-hover);
    transform: translateY(-2px);
}

.sb-card-top {
    padding: 18px 18px 14px;
    flex: 1;
}

.sb-card-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}
.sb-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--sb-primary) 0%, var(--sb-accent) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}
.sb-card-author { font-size: 12px; color: var(--sb-muted); }
.sb-card-date { font-size: 11px; color: var(--sb-muted); margin-left: auto; }

.sb-card-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--sb-text);
    margin: 0 0 8px;
    line-height: 1.4;
}
.sb-card-desc {
    font-size: 13px;
    color: var(--sb-muted);
    line-height: 1.6;
    margin: 0 0 12px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: all 0.3s ease;
}
.sb-card.expandable .sb-card-top { cursor: pointer; }
.sb-card-expand-hint {
    font-size: 11px;
    color: var(--sb-primary);
    font-weight: 600;
    margin-top: 4px;
    display: none;
    user-select: none;
}
.sb-card.is-clamped .sb-card-expand-hint { display: block; }

/* Modal de visualização do card */
.sb-view-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.sb-view-modal-overlay.open { display: flex; }
.sb-view-modal {
    background: var(--sb-card);
    border-radius: var(--sb-radius);
    width: 100%;
    max-width: 620px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    animation: sb-slide-in .2s ease;
    height: auto;
}
.sb-view-modal-header {
    background: var(--sb-primary);
    color: #fff;
    padding: 16px 20px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    position: sticky;
    top: 0;
    z-index: 1;
}
.sb-view-modal-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.4;
    flex: 1;
}
.sb-view-modal-body {
    padding: 22px;
}
.sb-view-modal-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--sb-border);
}
.sb-view-modal-desc {
    font-size: 14px;
    color: var(--sb-text);
    line-height: 1.7;
    white-space: pre-wrap;
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
    margin-bottom: 16px;
    overflow: visible;
}
.sb-view-modal-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 16px;
}
.sb-view-modal-footer {
    padding: 14px 22px;
    background: #f9fafb;
    border-top: 1px solid var(--sb-border);
    display: flex;
    align-items: center;
    gap: 12px;
}

.sb-card-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}
.sb-card-tag {
    padding: 2px 9px;
    border-radius: 20px;
    background: var(--sb-tag-bg);
    color: var(--sb-tag-color);
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    transition: background var(--sb-transition);
}
.sb-card-tag:hover { background: var(--sb-tag-color); color: #fff; }

/* ---- CARD FOOTER (voto + ações) ---- */
.sb-card-footer {
    border-top: 1px solid var(--sb-border);
    padding: 12px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fafbfc;
}

.sb-vote-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 14px;
    border-radius: var(--sb-radius-sm);
    border: 1.5px solid var(--sb-border);
    background: #fff;
    color: var(--sb-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--sb-transition);
    flex-shrink: 0;
}
.sb-vote-btn:hover {
    border-color: var(--sb-primary);
    color: var(--sb-primary);
    background: var(--sb-primary-soft);
}
.sb-vote-btn.voted {
    border-color: var(--sb-voted-color);
    background: var(--sb-voted-bg);
    color: var(--sb-voted-color);
}
.sb-vote-btn svg { width: 15px; height: 15px; }

.sb-vote-count {
    font-size: 20px;
    font-weight: 800;
    color: var(--sb-primary);
    min-width: 32px;
    text-align: center !important;
    line-height: 1;
}
.sb-vote-label {
    font-size: 11px;
    color: var(--sb-muted);
    line-height: 1.2;
    text-align: left;
}

.sb-card-actions { margin-left: auto; }
.sb-btn-delete {
    background: none;
    border: 1.5px solid transparent;
    border-radius: var(--sb-radius-sm);
    padding: 6px 8px;
    cursor: pointer;
    color: #dc2626;
    display: inline-flex;
    align-items: center;
    transition: all var(--sb-transition);
}
.sb-btn-delete:hover { border-color: #dc2626; background: #fef2f2; }
.sb-btn-delete svg { width: 15px; height: 15px; }

/* ---- MODAL NOVA SUGESTÃO ---- */
.sb-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.sb-modal-overlay.open { display: flex; }

.sb-modal {
    background: var(--sb-card);
    border-radius: var(--sb-radius);
    width: 100%;
    max-width: 540px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    animation: sb-slide-in .2s ease;
    overflow: hidden;
}
@keyframes sb-slide-in {
    from { opacity: 0; transform: translateY(-20px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.sb-modal-header {
    background: var(--sb-primary);
    color: #fff;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.sb-modal-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
}
.sb-modal-close {
    background: rgba(255,255,255,.2);
    border: none;
    color: #fff;
    cursor: pointer;
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 18px;
    line-height: 1;
    transition: background var(--sb-transition);
}
.sb-modal-close:hover { background: rgba(255,255,255,.35); }

.sb-modal-body { padding: 22px; }

.sb-field { margin-bottom: 16px; }
.sb-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--sb-text);
    margin-bottom: 6px;
}
.sb-field label span.req { color: var(--sb-primary); }
.sb-input, .sb-textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius-sm);
    font-size: 14px;
    color: var(--sb-text);
    font-family: inherit;
    outline: none;
    transition: border-color var(--sb-transition);
    resize: vertical;
}
.sb-input:focus, .sb-textarea:focus { border-color: var(--sb-primary); }
.sb-textarea { min-height: 100px; }
.sb-field-hint { font-size: 11px; color: var(--sb-muted); margin-top: 4px; }

.sb-modal-footer {
    padding: 16px 22px;
    background: #f9fafb;
    border-top: 1px solid var(--sb-border);
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}
.sb-btn-cancel {
    padding: 9px 18px !important;
    border: 1px solid var(--sb-border) !important;
    border-radius: var(--sb-radius-sm) !important;
    background: #fff !important;
    font-size: 14px !important;
    cursor: pointer !important;
    color: var(--sb-text) !important;
    font-weight: 500 !important;
    display: inline-flex !important;
    align-items: center !important;
    height: auto !important;
    line-height: normal !important;
    margin: 0 !important;
    width: auto !important;
}
.sb-btn-cancel:hover { background: #f0f0f0 !important; }
.sb-btn-submit {
    padding: 9px 22px;
    background: var(--sb-primary);
    color: #fff;
    border: none;
    border-radius: var(--sb-radius-sm);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background var(--sb-transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sb-btn-submit:hover { background: var(--sb-primary-dark); }
.sb-btn-submit:disabled { opacity: .6; cursor: not-allowed; }

/* ---- TOAST ---- */
.sb-toast {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 99999;
    padding: 12px 20px;
    border-radius: var(--sb-radius-sm);
    font-size: 14px;
    font-weight: 500;
    color: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
    opacity: 0;
    transform: translateY(12px);
    transition: all .3s ease;
    pointer-events: none;
    max-width: 320px;
}
.sb-toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
.sb-toast.success { background: var(--sb-success); }
.sb-toast.error   { background: #dc2626; }

/* ---- STATS BAR ---- */
.sb-stats {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.sb-stat {
    background: var(--sb-card);
    border: 1px solid var(--sb-border);
    border-radius: var(--sb-radius-sm);
    padding: 12px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: var(--sb-muted);
}
.sb-stat strong { font-size: 20px; font-weight: 800; color: var(--sb-primary); }

@media (max-width: 600px) {
    #suggestion-box-wrap { padding: 12px; }
    .sb-grid { grid-template-columns: 1fr; }
    .sb-header { flex-direction: column; align-items: flex-start; }
}
</style>

<div id="suggestion-box-wrap">

    <!-- HEADER -->
    <div class="sb-header">
        <div class="sb-header-left">
            <h1>
                <span class="sb-icon">💡</span>
                Suggestion Box
            </h1>
            <p>Compartilhe ideias, vote nas melhores e ajude a definir o que será desenvolvido.</p>
        </div>
        <button class="sb-btn-new" onclick="sbOpenModal()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nova Sugestão
        </button>
    </div>

    <!-- STATS -->
    <div class="sb-stats">
        <div class="sb-stat">
            <strong><?= count($suggestions) ?></strong>
            <?= count($suggestions) === 1 ? 'sugestão' : 'sugestões' ?>
        </div>
        <?php
        $totalVotes = array_sum(array_column($suggestions, 'vote_count'));
        ?>
        <div class="sb-stat">
            <strong><?= $totalVotes ?></strong>
            <?= $totalVotes === 1 ? 'voto registrado' : 'votos registrados' ?>
        </div>
        <?php if ($isSuperAdmin): ?>
        <div class="sb-stat">
            <a href="zabbix.php?action=suggestion.box.report" style="color:var(--sb-primary);font-weight:600;text-decoration:none;">
                📊 Ver Relatório Completo →
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- CONTROLES -->
    <div class="sb-controls">
        <div class="sb-search-wrap">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input
                type="text"
                id="sb-search-input"
                class="sb-search"
                placeholder="Buscar por título ou descrição..."
                value="<?= $search ?>"
            >
        </div>
        <div class="sb-sort-wrap">
            <label for="sb-sort-select">Ordenar:</label>
            <select id="sb-sort-select" class="sb-sort-select">
                <option value="votes"  <?= $sort === 'votes'  ? 'selected' : '' ?>>Mais votados</option>
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mais recentes</option>
            </select>
        </div>
    </div>

    <!-- TAG FILTER -->
    <?php if ($allTags): ?>
    <div class="sb-tags-filter">
        <span class="sb-tags-filter-label">Tags:</span>
        <?php if ($activeTag): ?>
            <span class="sb-tag-pill clear" onclick="sbFilterTag('')">✕ Limpar filtro</span>
        <?php endif; ?>
        <?php foreach ($allTags as $t): ?>
            <span class="sb-tag-pill <?= $activeTag === $t ? 'active' : '' ?>"
                  onclick="sbFilterTag(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">
                #<?= htmlspecialchars($t) ?>
            </span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- GRID DE CARDS -->
    <div class="sb-grid" id="sb-grid">

        <?php if (empty($suggestions)): ?>
        <div class="sb-empty">
            <svg fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                <path d="M9.663 17h4.673M12 3v1m6.364 1.636-.707.707M21 12h-1M4 12H3m3.343-5.657-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <p>Nenhuma sugestão encontrada. Seja o primeiro a contribuir!</p>
        </div>
        <?php else: ?>
        <?php foreach ($suggestions as $sug): ?>
        <?php
            $sid    = (int)$sug['suggestionid'];
            $author = htmlspecialchars(trim($sug['name'] . ' ' . $sug['surname']) ?: $sug['username']);
            $initials = strtoupper(mb_substr($sug['name'] ?? $sug['username'], 0, 1) . mb_substr($sug['surname'] ?? '', 0, 1));
            $isOwner = (int)$sug['userid'] === $currentUserId;
            $canDelete = $isSuperAdmin;
            $voteCount = (int)$sug['vote_count'];
            $userVoted = (bool)$sug['user_voted'];
            $date = date('d/m/Y', strtotime($sug['created_at']));
        ?>
        <div class="sb-card expandable" id="sb-card-<?= $sid ?>" onclick="sbViewCard(<?= $sid ?>, event)">
            <div class="sb-card-top">
                <div class="sb-card-meta">
                    <div class="sb-avatar"><?= htmlspecialchars($initials ?: '?') ?></div>
                    <span class="sb-card-author"><?= $author ?></span>
                    <span class="sb-card-date"><?= $date ?></span>
                </div>
                <div class="sb-card-title"><?= htmlspecialchars($sug['title']) ?></div>
                <?php if (!empty($sug['description'])): ?>
                <div class="sb-card-desc" id="sb-desc-<?= $sid ?>"><?= htmlspecialchars($sug['description']) ?></div>
                <?php endif; ?>
                <?php if (!empty($sug['tags'])): ?>
                <div class="sb-card-tags">
                    <?php foreach ($sug['tags'] as $tag): ?>
                    <span class="sb-card-tag" onclick="sbFilterTag(<?= htmlspecialchars(json_encode($tag), ENT_QUOTES) ?>)">
                        #<?= htmlspecialchars($tag) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="sb-card-footer">
                <button
                    class="sb-vote-btn <?= $userVoted ? 'voted' : '' ?>"
                    id="sb-vote-btn-<?= $sid ?>"
                    onclick="sbVote(<?= $sid ?>)"
                    title="<?= $userVoted ? 'Remover voto' : 'Votar nesta ideia' ?>"
                >
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/>
                        <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                    </svg>
                    <?= $userVoted ? 'Votou' : 'Votar' ?>
                </button>
                <div>
                    <div class="sb-vote-count" id="sb-vc-<?= $sid ?>"><?= $voteCount ?></div>
                    <div class="sb-vote-label"><?= $voteCount === 1 ? 'voto' : 'votos' ?></div>
                </div>
                <?php if ($canDelete): ?>
                <div class="sb-card-actions">
                    <button class="sb-btn-delete" onclick="sbDelete(<?= $sid ?>)" title="Excluir sugestão">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                        </svg>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div><!-- #suggestion-box-wrap -->

<!-- MODAL VISUALIZAÇÃO DE CARD -->
<div class="sb-view-modal-overlay" id="sb-view-modal">
    <div class="sb-view-modal" role="dialog" aria-modal="true">
        <div class="sb-view-modal-header">
            <h2 id="sb-view-title">Título</h2>
            <button class="sb-modal-close" onclick="sbCloseViewModal()" aria-label="Fechar">✕</button>
        </div>
        <div class="sb-view-modal-body">
            <div class="sb-view-modal-meta">
                <div class="sb-avatar" id="sb-view-avatar">?</div>
                <div>
                    <div style="font-size:13px;font-weight:600;" id="sb-view-author"></div>
                    <div style="font-size:11px;color:var(--sb-muted);" id="sb-view-date"></div>
                </div>
            </div>
            <div class="sb-view-modal-desc" id="sb-view-desc"></div>
            <div class="sb-view-modal-tags" id="sb-view-tags"></div>
        </div>
        <div class="sb-view-modal-footer">
            <span style="font-size:13px;color:var(--sb-muted);">Votos:</span>
            <strong style="font-size:18px;color:var(--sb-primary);" id="sb-view-votes">0</strong>
            <button class="sb-btn-cancel" onclick="sbCloseViewModal()" style="margin-left:auto;">Fechar</button>
        </div>
    </div>
</div>

<!-- MODAL VISUALIZAÇÃO DE CARD -->
<div class="sb-view-modal-overlay" id="sb-view-modal">
    <div class="sb-view-modal" role="dialog" aria-modal="true">
        <div class="sb-view-modal-header">
            <h2 id="sb-view-title">Título</h2>
            <button class="sb-modal-close" onclick="sbCloseViewModal()" aria-label="Fechar">✕</button>
        </div>
        <div class="sb-view-modal-body">
            <div class="sb-view-modal-meta">
                <div class="sb-avatar" id="sb-view-avatar">?</div>
                <div>
                    <div style="font-size:13px;font-weight:600;" id="sb-view-author"></div>
                    <div style="font-size:11px;color:var(--sb-muted);" id="sb-view-date"></div>
                </div>
            </div>
            <div class="sb-view-modal-desc" id="sb-view-desc"></div>
            <div class="sb-view-modal-tags" id="sb-view-tags"></div>
        </div>
        <div class="sb-view-modal-footer">
            <span style="font-size:13px;color:var(--sb-muted);">Votos:</span>
            <strong style="font-size:18px;color:var(--sb-primary);" id="sb-view-votes">0</strong>
            <button class="sb-btn-cancel" onclick="sbCloseViewModal()" style="margin-left:auto;">Fechar</button>
        </div>
    </div>
</div>

<!-- MODAL NOVA SUGESTÃO -->
<div class="sb-modal-overlay" id="sb-modal">
    <div class="sb-modal" role="dialog" aria-modal="true" aria-labelledby="sb-modal-title">
        <div class="sb-modal-header">
            <h2 id="sb-modal-title">💡 Nova Sugestão</h2>
            <button class="sb-modal-close" onclick="sbCloseModal()" aria-label="Fechar">✕</button>
        </div>
        <div class="sb-modal-body">
            <div class="sb-field">
                <label for="sb-title">Título <span class="req">*</span></label>
                <input type="text" id="sb-title" class="sb-input" placeholder="Ex: Adicionar filtro por data no relatório de alertas" maxlength="200">
            </div>
            <div class="sb-field">
                <label for="sb-desc">Descrição</label>
                <textarea id="sb-desc" class="sb-textarea" placeholder="Descreva sua ideia em detalhes: o problema que resolve, como poderia funcionar, etc."></textarea>
            </div>
            <div class="sb-field">
                <label for="sb-tags">Tags</label>
                <input type="text" id="sb-tags" class="sb-input" placeholder="dashboard, relatório, filtro" maxlength="300">
                <div class="sb-field-hint">Separe as tags por vírgula. Ex: dashboard, alertas, performance</div>
            </div>
        </div>
        <div class="sb-modal-footer">
            <button class="sb-btn-cancel" onclick="sbCloseModal()">Cancelar</button>
            <button class="sb-btn-submit" id="sb-submit-btn" onclick="sbSave()">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                Publicar Sugestão
            </button>
        </div>
    </div>
</div>

<!-- TOAST -->
<div class="sb-toast" id="sb-toast"></div>

<script>
(function() {
    'use strict';

    var currentSearch = <?= json_encode($data['search'] ?? '') ?>;
    var currentTag    = <?= json_encode($data['activeTag'] ?? '') ?>;
    var currentSort   = <?= json_encode($data['sort'] ?? 'votes') ?>;

    // ---- Utilitários ----
    function sbToast(msg, type) {
        var el = document.getElementById('sb-toast');
        el.textContent = msg;
        el.className = 'sb-toast ' + (type || 'success');
        el.classList.add('show');
        setTimeout(function() { el.classList.remove('show'); }, 3200);
    }

    function sbReload(params) {
        var base = 'zabbix.php?action=suggestion.box.list';
        var q = [];
        var s = (params && params.search !== undefined) ? params.search : currentSearch;
        var t = (params && params.tag    !== undefined) ? params.tag    : currentTag;
        var o = (params && params.sort   !== undefined) ? params.sort   : currentSort;
        if (s) q.push('search=' + encodeURIComponent(s));
        if (t) q.push('tag='    + encodeURIComponent(t));
        if (o && o !== 'votes') q.push('sort=' + encodeURIComponent(o));
        window.location.href = base + (q.length ? '&' + q.join('&') : '');
    }

    // ---- Modal ----
    window.sbOpenModal = function() {
        document.getElementById('sb-modal').classList.add('open');
        document.getElementById('sb-title').focus();
    };
    window.sbCloseModal = function() {
        document.getElementById('sb-modal').classList.remove('open');
        document.getElementById('sb-title').value = '';
        document.getElementById('sb-desc').value  = '';
        document.getElementById('sb-tags').value  = '';
        document.getElementById('sb-submit-btn').disabled = false;
    };

    // Fechar ao clicar no overlay
    document.getElementById('sb-modal').addEventListener('click', function(e) {
        if (e.target === this) sbCloseModal();
    });

    // ---- Salvar sugestão ----
    window.sbSave = function() {
        var title = document.getElementById('sb-title').value.trim();
        var desc  = document.getElementById('sb-desc').value.trim();
        var tags  = document.getElementById('sb-tags').value.trim();

        if (!title) {
            sbToast('O título é obrigatório.', 'error');
            document.getElementById('sb-title').focus();
            return;
        }

        var btn = document.getElementById('sb-submit-btn');
        btn.disabled = true;
        btn.textContent = 'Publicando...';

        var body = 'title='       + encodeURIComponent(title) +
                   '&description=' + encodeURIComponent(desc) +
                   '&tags='        + encodeURIComponent(tags);

        fetch('zabbix.php?action=suggestion.box.save', {
            method:  'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body:    body
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                sbToast('Sugestão publicada com sucesso! 🎉', 'success');
                sbCloseModal();
                setTimeout(function() { sbReload(); }, 900);
            } else {
                sbToast(data.error || 'Erro ao salvar.', 'error');
                btn.disabled = false;
                btn.textContent = 'Publicar Sugestão';
            }
        })
        .catch(function() {
            sbToast('Erro de conexão.', 'error');
            btn.disabled = false;
            btn.textContent = 'Publicar Sugestão';
        });
    };

    // ---- Votar ----
    window.sbVote = function(sid) {
        fetch('zabbix.php?action=suggestion.box.vote', {
            method:  'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body:    'suggestionid=' + sid
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var btn = document.getElementById('sb-vote-btn-' + sid);
                var cnt = document.getElementById('sb-vc-'      + sid);
                if (data.voted) {
                    btn.classList.add('voted');
                    btn.innerHTML = '<svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg> Votou';
                    btn.title = 'Remover voto';
                    sbToast('Voto registrado! 👍', 'success');
                } else {
                    btn.classList.remove('voted');
                    btn.innerHTML = '<svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg> Votar';
                    btn.title = 'Votar nesta ideia';
                    sbToast('Voto removido.', 'success');
                }
                cnt.textContent = data.voteCount;
                // Atualiza stats bar de votos totais
                var allCounts = document.querySelectorAll('[id^="sb-vc-"]');
                var total = 0;
                allCounts.forEach(function(el) { total += parseInt(el.textContent) || 0; });
                var statVotes = document.querySelectorAll('.sb-stat strong');
                if (statVotes[1]) statVotes[1].textContent = total;
            } else {
                sbToast(data.error || 'Erro ao votar.', 'error');
            }
        })
        .catch(function() { sbToast('Erro de conexão.', 'error'); });
    };

    // ---- Excluir ----
    window.sbDelete = function(sid) {
        if (!confirm('Tem certeza que deseja excluir esta sugestão? Esta ação não pode ser desfeita.')) return;

        fetch('zabbix.php?action=suggestion.box.delete', {
            method:  'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body:    'suggestionid=' + sid
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var card = document.getElementById('sb-card-' + sid);
                if (card) {
                    card.style.transition = 'all .3s ease';
                    card.style.opacity    = '0';
                    card.style.transform  = 'scale(.95)';
                    setTimeout(function() { card.remove(); }, 300);
                }
                sbToast('Sugestão excluída.', 'success');
            } else {
                sbToast(data.error || 'Erro ao excluir.', 'error');
            }
        })
        .catch(function() { sbToast('Erro de conexão.', 'error'); });
    };

    // ---- Filtro por tag ----
    window.sbFilterTag = function(tag) {
        sbReload({ tag: tag });
    };

    // ---- Search com debounce ----
    var searchTimer;
    document.getElementById('sb-search-input').addEventListener('input', function() {
        var val = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            sbReload({ search: val });
        }, 500);
    });

    // ---- Ordenação ----
    document.getElementById('sb-sort-select').addEventListener('change', function() {
        sbReload({ sort: this.value });
    });

    // ---- Expandir card ----
    // Dados dos cards para o modal de visualização
    var sbCardsData = <?= json_encode(array_map(function($s) { return [
        'id'        => (int)$s['suggestionid'],
        'title'     => $s['title'],
        'desc'      => $s['description'] ?? '',
        'author'    => trim(($s['name'] ?? '') . ' ' . ($s['surname'] ?? '')) ?: ($s['username'] ?? ''),
        'date'      => date('d/m/Y', strtotime($s['created_at'])),
        'tags'      => $s['tags'] ?? [],
        'votes'     => (int)$s['vote_count'],
    ]; }, $suggestions)) ?>;

    window.sbViewCard = function(sid, e) {
        if (e.target.closest('.sb-vote-btn, .sb-btn-delete, .sb-card-footer, a, button')) return;

        var card = sbCardsData.find(function(c) { return c.id === sid; });
        if (!card) return;

        document.getElementById('sb-view-title').textContent = card.title;
        document.getElementById('sb-view-desc').textContent = card.desc || 'Sem descrição.';
        document.getElementById('sb-view-author').textContent = card.author;
        document.getElementById('sb-view-date').textContent = card.date;
        document.getElementById('sb-view-votes').textContent = document.getElementById('sb-vc-' + sid) ? document.getElementById('sb-vc-' + sid).textContent : card.votes;

        var initials = card.author.split(' ').map(function(w){return w[0]||'';}).slice(0,2).join('').toUpperCase();
        document.getElementById('sb-view-avatar').textContent = initials || '?';

        var tagsEl = document.getElementById('sb-view-tags');
        tagsEl.innerHTML = '';
        (card.tags || []).forEach(function(t) {
            var span = document.createElement('span');
            span.className = 'sb-card-tag';
            span.textContent = '#' + t;
            span.onclick = function() { sbCloseViewModal(); sbFilterTag(t); };
            tagsEl.appendChild(span);
        });

        document.getElementById('sb-view-modal').classList.add('open');
    };

    window.sbCloseViewModal = function() {
        document.getElementById('sb-view-modal').classList.remove('open');
    };

    document.getElementById('sb-view-modal').addEventListener('click', function(e) {
        if (e.target === this) sbCloseViewModal();
    });


    // ---- Enter no title do modal ----
    document.getElementById('sb-title').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('sb-desc').focus(); }
    });

})();
</script>