<?php
include 'config.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* MARK AS READ */
if(isset($_POST['mark_read_id'])){
    $id = intval($_POST['mark_read_id']);
    $conn->query("UPDATE announcements SET is_read = 1 WHERE id = $id");
    exit();
}

$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Announcements · DENR Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>

/* ── RESET ── */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

:root {
    --forest:    #0b5d3b;
    --forest-dk: #084529;
    --forest-lt: #e8f5ee;
    --leaf:      #22c55e;
    --cream:     #faf9f6;
    --paper:     #ffffff;
    --mist:      #f0f4f2;
    --stone:     #8a9a90;
    --pebble:    #c8d6cd;
    --ink:       #1a2820;
    --charcoal:  #3d4f45;
    --amber:     #d97706;
    --amber-lt:  #fef3c7;
    --amber-dk:  #92400e;
    --red:       #dc2626;
    --red-lt:    #fee2e2;
    --red-dk:    #7f1d1d;
    --green:     #16a34a;
    --green-lt:  #dcfce7;
    --green-dk:  #14532d;
    --r-sm:      10px;
    --r-md:      16px;
    --r-lg:      24px;
    --r-xl:      32px;
    --ease:      cubic-bezier(0.4,0,0.2,1);
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--ink);
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
}

/* ── NATURE BG ── */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
        radial-gradient(ellipse 70% 50% at 0% 0%, rgba(11,93,59,0.06), transparent),
        radial-gradient(ellipse 50% 60% at 100% 100%, rgba(34,197,94,0.05), transparent),
        url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%230b5d3b' fill-opacity='0.012'%3E%3Ccircle cx='40' cy='40' r='2'/%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 0;
}

/* ── PAGE HEADER ── */
.page-header {
    background: var(--forest);
    padding: 0 40px;
    position: sticky;
    top: 0;
    z-index: 500;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    right: -60px; top: -80px;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
    pointer-events: none;
}

.header-inner {
    max-width: 860px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 72px;
    gap: 20px;
    position: relative;
    z-index: 1;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.header-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: rgba(255,255,255,0.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.header-title {
    font-family: 'Fraunces', serif;
    font-size: 20px;
    font-weight: 600;
    color: white;
}

.header-sub {
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    margin-top: 1px;
    letter-spacing: 0.5px;
}

/* ── FILTER BAR ── */
.filter-bar {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: var(--r-sm);
    display: flex;
    gap: 4px;
    padding: 4px;
}

.filter-btn {
    padding: 6px 14px;
    border-radius: 7px;
    border: none;
    background: transparent;
    color: rgba(255,255,255,0.6);
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s var(--ease);
    white-space: nowrap;
}

.filter-btn.active,
.filter-btn:hover {
    background: rgba(255,255,255,0.15);
    color: white;
}

.filter-btn.active {
    background: rgba(34,197,94,0.25);
    color: #86efac;
}

/* ── BACK LINK ── */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: rgba(255,255,255,0.6);
    font-size: 12px;
    text-decoration: none;
    transition: color 0.2s;
    white-space: nowrap;
}
.back-link:hover { color: white; }

/* ── MAIN CONTAINER ── */
.container {
    max-width: 860px;
    margin: 0 auto;
    padding: 40px 40px 80px;
    position: relative;
    z-index: 1;
}

/* ── STATS ROW ── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 36px;
}

.stat-card {
    background: var(--paper);
    border: 1px solid var(--pebble);
    border-radius: var(--r-md);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform 0.2s var(--ease);
}

.stat-card:hover { transform: translateY(-2px); }

.stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-icon.total  { background: var(--forest-lt); }
.stat-icon.unread { background: var(--amber-lt); }
.stat-icon.urgent { background: var(--red-lt); }

.stat-body {}
.stat-num {
    font-family: 'Fraunces', serif;
    font-size: 26px;
    font-weight: 600;
    color: var(--ink);
    line-height: 1;
    margin-bottom: 3px;
}
.stat-label {
    font-size: 12px;
    color: var(--stone);
    font-weight: 500;
    letter-spacing: 0.3px;
}

/* ── SECTION HEADER ── */
.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.section-head h2 {
    font-family: 'Fraunces', serif;
    font-size: 22px;
    font-weight: 600;
    color: var(--forest);
}

.sort-select {
    padding: 7px 12px;
    background: var(--paper);
    border: 1px solid var(--pebble);
    border-radius: var(--r-sm);
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    color: var(--charcoal);
    cursor: pointer;
    outline: none;
    appearance: none;
    padding-right: 28px;
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%238a9a90' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
}

/* ── ANNOUNCEMENT CARDS ── */
.cards-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.ann-card {
    background: var(--paper);
    border: 1px solid var(--pebble);
    border-radius: var(--r-lg);
    padding: 26px 28px;
    position: relative;
    overflow: hidden;
    transition: all 0.25s var(--ease);
    cursor: pointer;
    animation: cardIn 0.4s var(--ease) both;
}

@keyframes cardIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: none; }
}

.ann-card:hover {
    border-color: var(--pebble);
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(11,93,59,0.08);
}

/* unread left accent */
.ann-card.unread {
    border-left: 3px solid var(--forest);
    border-radius: 0 var(--r-lg) var(--r-lg) 0;
}

/* Leaf watermark */
.ann-card::after {
    content: '🍃';
    position: absolute;
    right: 24px;
    bottom: 16px;
    font-size: 48px;
    opacity: 0.04;
    pointer-events: none;
    transform: rotate(-20deg);
    transition: opacity 0.3s;
}

.ann-card:hover::after { opacity: 0.08; }

/* ── URGENCY STRIP ── */
.urg-strip {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--r-lg) var(--r-lg) 0 0;
}
.urg-strip.light  { background: var(--green); }
.urg-strip.medium { background: var(--amber); }
.urg-strip.urgent { background: var(--red); }

.ann-card.unread .urg-strip {
    border-radius: 0 var(--r-lg) 0 0;
}

/* ── CARD TOP ── */
.card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 14px;
}

.card-badges {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.urg-badge {
    padding: 4px 11px;
    border-radius: 99px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
}

.urg-badge.light  { background: var(--green-lt);  color: var(--green-dk); }
.urg-badge.medium { background: var(--amber-lt);  color: var(--amber-dk); }
.urg-badge.urgent { background: var(--red-lt);    color: var(--red-dk); }

.new-badge {
    padding: 4px 11px;
    border-radius: 99px;
    font-size: 10px;
    font-weight: 700;
    background: var(--forest-lt);
    color: var(--forest);
    letter-spacing: 0.5px;
}

/* ── CARD TITLE ── */
.card-title-wrap {}

.card-title {
    font-family: 'Fraunces', serif;
    font-size: 18px;
    font-weight: 600;
    color: var(--ink);
    line-height: 1.3;
    margin-bottom: 4px;
    transition: color 0.2s;
}

.ann-card:hover .card-title { color: var(--forest); }

.card-preview {
    font-size: 14px;
    color: var(--stone);
    line-height: 1.65;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ── CARD FOOTER ── */
.card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 18px;
    gap: 12px;
    flex-wrap: wrap;
}

.card-meta {
    display: flex;
    gap: 20px;
    align-items: center;
    flex-wrap: wrap;
}

.meta-chip {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--stone);
    font-weight: 500;
}

.meta-chip svg {
    flex-shrink: 0;
    opacity: 0.7;
}

.card-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

/* ── BUTTONS ── */
.btn {
    padding: 8px 16px;
    border-radius: var(--r-sm);
    border: none;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s var(--ease);
    text-decoration: none;
    white-space: nowrap;
}

.btn-primary {
    background: var(--forest);
    color: white;
}
.btn-primary:hover { background: var(--forest-dk); transform: translateY(-1px); }

.btn-ghost {
    background: var(--mist);
    color: var(--charcoal);
    border: 1px solid var(--pebble);
}
.btn-ghost:hover { background: #e5ede9; }

.btn-done {
    background: var(--green-lt);
    color: var(--green-dk);
    cursor: default;
}

.btn-file {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}
.btn-file:hover { background: #dbeafe; }

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center;
    padding: 80px 20px;
}

.empty-icon {
    font-size: 56px;
    margin-bottom: 16px;
    opacity: 0.4;
}

.empty-state h3 {
    font-family: 'Fraunces', serif;
    font-size: 20px;
    font-weight: 600;
    color: var(--charcoal);
    margin-bottom: 8px;
}

.empty-state p {
    font-size: 14px;
    color: var(--stone);
}

/* ── MODAL BACKDROP ── */
.modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(10,30,20,0.55);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(6px);
}

.modal-backdrop.open {
    display: flex;
}

/* ── MODAL ── */
.modal {
    background: var(--paper);
    border-radius: var(--r-xl);
    width: 100%;
    max-width: 660px;
    max-height: 88vh;
    overflow-y: auto;
    position: relative;
    animation: modalIn 0.3s var(--ease);
    box-shadow: 0 32px 80px rgba(0,0,0,0.25);
}

@keyframes modalIn {
    from { opacity:0; transform: scale(0.95) translateY(20px); }
    to   { opacity:1; transform: none; }
}

.modal-urg-bar {
    height: 4px;
    border-radius: var(--r-xl) var(--r-xl) 0 0;
}
.modal-urg-bar.light  { background: var(--green); }
.modal-urg-bar.medium { background: var(--amber); }
.modal-urg-bar.urgent { background: var(--red); }

.modal-head {
    padding: 28px 32px 20px;
    border-bottom: 1px solid var(--mist);
    position: relative;
}

.modal-close {
    position: absolute;
    top: 20px; right: 24px;
    width: 32px; height: 32px;
    border-radius: 50%;
    border: 1px solid var(--pebble);
    background: var(--mist);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    color: var(--stone);
    transition: all 0.2s;
    line-height: 1;
    padding: 0;
    font-family: inherit;
}
.modal-close:hover {
    background: var(--red-lt);
    color: var(--red);
    border-color: #fecaca;
}

.modal-badges {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.modal-title {
    font-family: 'Fraunces', serif;
    font-size: 24px;
    font-weight: 600;
    color: var(--ink);
    line-height: 1.3;
    padding-right: 40px;
}

.modal-meta {
    display: flex;
    gap: 20px;
    margin-top: 14px;
    flex-wrap: wrap;
}

.modal-body {
    padding: 24px 32px;
}

.modal-message {
    font-size: 15px;
    line-height: 1.8;
    color: var(--charcoal);
    white-space: pre-wrap;
}

.modal-attachment {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--mist);
}

.attach-label {
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--stone);
    font-weight: 600;
    margin-bottom: 10px;
}

.modal-actions {
    padding: 20px 32px 28px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    border-top: 1px solid var(--mist);
}

/* ── IFRAME / IMG PREVIEW ── */
.file-preview-img {
    width: 100%;
    border-radius: var(--r-md);
    display: block;
}

.file-preview-iframe {
    width: 100%;
    height: 420px;
    border: none;
    border-radius: var(--r-md);
    background: var(--mist);
}

/* ── RESPONSIVE ── */
@media (max-width: 700px) {
    .page-header { padding: 0 20px; }
    .container { padding: 24px 20px 60px; }
    .stats-row { grid-template-columns: 1fr 1fr; }
    .stats-row .stat-card:last-child { grid-column: 1 / -1; }
    .header-inner { height: 60px; }
    .filter-bar { display: none; }
    .modal-head, .modal-body, .modal-actions { padding-left: 20px; padding-right: 20px; }
    .ann-card { padding: 20px; }
    .card-top { flex-direction: column-reverse; }
    .card-badges { justify-content: flex-start; }
}

@media (max-width: 400px) {
    .stats-row { grid-template-columns: 1fr; }
    .stats-row .stat-card:last-child { grid-column: auto; }
}

</style>
</head>
<body>

<!-- PAGE HEADER -->
<header class="page-header">
    <div class="header-inner">
        <div class="header-left">
            <div class="header-icon">📣</div>
            <div>
                <div class="header-title">Announcements</div>
                <div class="header-sub">DENR–CENRO Employee Portal</div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:16px;">
            <div class="filter-bar" id="filterBar">
                <button class="filter-btn active" data-filter="all" onclick="filterCards('all',this)">All</button>
                <button class="filter-btn" data-filter="light"  onclick="filterCards('light',this)">🟢 Light</button>
                <button class="filter-btn" data-filter="medium" onclick="filterCards('medium',this)">🟠 Medium</button>
                <button class="filter-btn" data-filter="urgent" onclick="filterCards('urgent',this)">🔴 Urgent</button>
                <button class="filter-btn" data-filter="unread" onclick="filterCards('unread',this)">New</button>
            </div>
            <a href="Employee_dashboard.php" class="back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Dashboard
            </a>
        </div>
    </div>
</header>

<!-- MAIN -->
<div class="container">

    <!-- STATS -->
    <?php
    $all_rows = [];
    while($r = $result->fetch_assoc()) $all_rows[] = $r;
    $total  = count($all_rows);
    $unread = count(array_filter($all_rows, fn($r) => !$r['is_read']));
    $urgent = count(array_filter($all_rows, fn($r) => $r['urgency'] === 'urgent'));
    ?>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon total">📋</div>
            <div class="stat-body">
                <div class="stat-num"><?= $total ?></div>
                <div class="stat-label">Total announcements</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon unread">🔔</div>
            <div class="stat-body">
                <div class="stat-num"><?= $unread ?></div>
                <div class="stat-label">Unread</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon urgent">⚠️</div>
            <div class="stat-body">
                <div class="stat-num"><?= $urgent ?></div>
                <div class="stat-label">Urgent items</div>
            </div>
        </div>
    </div>

    <!-- SECTION HEAD -->
    <div class="section-head">
        <h2>Latest Updates</h2>
        <select class="sort-select" onchange="sortCards(this.value)">
            <option value="newest">Newest first</option>
            <option value="oldest">Oldest first</option>
            <option value="urgency">By urgency</option>
            <option value="unread">Unread first</option>
        </select>
    </div>

    <!-- CARDS LIST -->
    <div class="cards-list" id="cardsList">

        <?php if(empty($all_rows)): ?>
        <div class="empty-state">
            <div class="empty-icon">🌿</div>
            <h3>All clear</h3>
            <p>No announcements have been published yet.</p>
        </div>
        <?php endif; ?>

        <?php foreach($all_rows as $i => $row):
            $preview = htmlspecialchars(substr($row['message'], 0, 150));
            $urgency = htmlspecialchars($row['urgency']);
            $is_read = $row['is_read'];
            $created = date('M d, Y', strtotime($row['created_at']));
            $deadline = $row['deadline'] ? date('M d, Y', strtotime($row['deadline'])) : 'No deadline';
        ?>
        <div class="ann-card <?= $is_read ? '' : 'unread' ?>"
             data-urgency="<?= $urgency ?>"
             data-read="<?= $is_read ? '1' : '0' ?>"
             data-created="<?= strtotime($row['created_at']) ?>"
             style="animation-delay:<?= $i * 60 ?>ms"
             onclick="openModal(<?= htmlspecialchars(json_encode([
                 'id'       => $row['id'],
                 'title'    => $row['title'],
                 'message'  => $row['message'],
                 'urgency'  => $row['urgency'],
                 'deadline' => $deadline,
                 'created'  => $created,
                 'file'     => $row['file_path'],
                 'is_read'  => $row['is_read'],
             ]), ENT_QUOTES) ?>)">

            <div class="urg-strip <?= $urgency ?>"></div>

            <div class="card-top">
                <div class="card-title-wrap">
                    <div class="card-title"><?= htmlspecialchars($row['title']) ?></div>
                </div>
                <div class="card-badges">
                    <span class="urg-badge <?= $urgency ?>"><?= strtoupper($urgency) ?></span>
                    <?php if(!$is_read): ?>
                        <span class="new-badge">NEW</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-preview"><?= $preview ?>…</div>

            <div class="card-footer">
                <div class="card-meta">
                    <span class="meta-chip">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <?= $deadline ?>
                    </span>
                    <span class="meta-chip">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        <?= $created ?>
                    </span>
                    <?php if(!empty($row['file_path'])): ?>
                    <span class="meta-chip">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                        Attachment
                    </span>
                    <?php endif; ?>
                </div>
                <div class="card-actions" onclick="event.stopPropagation()">
                    <?php if(!$is_read): ?>
                    <button class="btn btn-ghost" id="readBtn<?= $row['id'] ?>"
                        onclick="markRead(<?= $row['id'] ?>, this, event)">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        Mark read
                    </button>
                    <?php else: ?>
                    <button class="btn btn-done" disabled>
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        Read
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-primary">
                        Read more
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

        </div>
        <?php endforeach; ?>

    </div>
</div>

<!-- MODAL BACKDROP -->
<div class="modal-backdrop" id="modalBackdrop" onclick="handleBackdropClick(event)">
    <div class="modal" id="modal">
        <div class="modal-urg-bar" id="modalUrgBar"></div>

        <div class="modal-head">
            <button class="modal-close" onclick="closeModal()" aria-label="Close">×</button>
            <div class="modal-badges" id="modalBadges"></div>
            <div class="modal-title" id="modalTitle"></div>
            <div class="modal-meta" id="modalMeta"></div>
        </div>

        <div class="modal-body">
            <div class="modal-message" id="modalMessage"></div>
            <div class="modal-attachment" id="modalAttachment" style="display:none;">
                <div class="attach-label">Attachment</div>
                <div id="modalFileContent"></div>
            </div>
        </div>

        <div class="modal-actions" id="modalActions"></div>
    </div>
</div>

<script>

/* ── MARK READ ── */
function markRead(id, btn, e) {
    if(e) e.stopPropagation();
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "announcements.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if(this.status === 200) {
            btn.outerHTML = '<button class="btn btn-done" disabled><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Read</button>';
            var card = document.querySelector('.ann-card[data-urgency]');
            var cards = document.querySelectorAll('.ann-card');
            cards.forEach(function(c) {
                var actions = c.querySelector('#readBtn' + id);
                if(actions) {
                    c.classList.remove('unread');
                    c.dataset.read = '1';
                    var nb = c.querySelector('.new-badge');
                    if(nb) nb.remove();
                }
            });
            var unreadEl = document.getElementById('unreadCount');
            if(unreadEl) unreadEl.textContent = Math.max(0, parseInt(unreadEl.textContent) - 1);
        }
    };
    xhr.send("mark_read_id=" + id);
}

/* ── MODAL ── */
var currentData = null;

function openModal(data) {
    currentData = data;
    document.getElementById('modalUrgBar').className = 'modal-urg-bar ' + data.urgency;

    var badgesHtml = '<span class="urg-badge ' + data.urgency + '">' + data.urgency.toUpperCase() + '</span>';
    if(!data.is_read) badgesHtml += '<span class="new-badge">NEW</span>';
    document.getElementById('modalBadges').innerHTML = badgesHtml;

    document.getElementById('modalTitle').textContent = data.title;

    document.getElementById('modalMeta').innerHTML =
        '<span class="meta-chip"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> Deadline: ' + data.deadline + '</span>' +
        '<span class="meta-chip"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Posted: ' + data.created + '</span>';

    document.getElementById('modalMessage').textContent = data.message;

    var attachEl = document.getElementById('modalAttachment');
    var fileContentEl = document.getElementById('modalFileContent');
    if(data.file) {
        var ext = data.file.split('.').pop().toLowerCase();
        var html = '';
        if(['jpg','jpeg','png','gif','webp'].includes(ext)) {
            html = '<img src="' + data.file + '" class="file-preview-img">';
        } else if(ext === 'pdf') {
            html = '<iframe src="' + data.file + '" class="file-preview-iframe"></iframe>';
        } else {
            html = '<a href="' + data.file + '" target="_blank" class="btn btn-file"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Download file</a>';
        }
        fileContentEl.innerHTML = html;
        attachEl.style.display = 'block';
    } else {
        attachEl.style.display = 'none';
    }

    var actHtml = '';
    if(!data.is_read) {
        actHtml += '<button class="btn btn-primary" onclick="markRead(' + data.id + ', this, null); currentData.is_read=1; document.getElementById(\'modalBadges\').querySelector(\'.new-badge\') && document.getElementById(\'modalBadges\').querySelector(\'.new-badge\').remove(); this.outerHTML=\'<button class=\\\"btn btn-done\\\" disabled><svg width=\\\"13\\\" height=\\\"13\\\" fill=\\\"none\\\" stroke=\\\"currentColor\\\" stroke-width=\\\"2\\\" viewBox=\\\"0 0 24 24\\\"><path d=\\\"M20 6L9 17l-5-5\\\"/></svg> Marked as read</button>\'"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Mark as read</button>';
    } else {
        actHtml += '<button class="btn btn-done" disabled><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Already read</button>';
    }
    if(data.file) {
        actHtml += '<a href="' + data.file + '" target="_blank" class="btn btn-file"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg> Open attachment</a>';
    }
    actHtml += '<button class="btn btn-ghost" onclick="closeModal()">Close</button>';
    document.getElementById('modalActions').innerHTML = actHtml;

    document.getElementById('modalBackdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('modalBackdrop').classList.remove('open');
    document.body.style.overflow = '';
}

function handleBackdropClick(e) {
    if(e.target === document.getElementById('modalBackdrop')) closeModal();
}

document.addEventListener('keydown', function(e) {
    if(e.key === 'Escape') closeModal();
});

/* ── FILTER ── */
function filterCards(type, btn) {
    document.querySelectorAll('.filter-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.ann-card').forEach(function(card) {
        var show = true;
        if(type === 'unread') show = card.dataset.read === '0';
        else if(type !== 'all') show = card.dataset.urgency === type;
        card.style.display = show ? '' : 'none';
    });
}

/* ── SORT ── */
function sortCards(val) {
    var list = document.getElementById('cardsList');
    var cards = Array.from(list.querySelectorAll('.ann-card'));
    var urgOrder = { urgent:0, medium:1, light:2 };
    cards.sort(function(a, b) {
        if(val === 'newest') return parseInt(b.dataset.created) - parseInt(a.dataset.created);
        if(val === 'oldest') return parseInt(a.dataset.created) - parseInt(b.dataset.created);
        if(val === 'urgency') return (urgOrder[a.dataset.urgency]||99) - (urgOrder[b.dataset.urgency]||99);
        if(val === 'unread') return parseInt(a.dataset.read) - parseInt(b.dataset.read);
        return 0;
    });
    cards.forEach(function(c) { list.appendChild(c); });
}

</script>
</body>
</html>