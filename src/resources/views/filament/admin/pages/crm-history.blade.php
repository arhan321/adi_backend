<x-filament-panels::page>
    @php
        $transactions = $this->getTransactions();

        $totalTransactions = \App\Models\PointTransaction::query()->count();
        $todayTransactions = \App\Models\PointTransaction::query()
            ->whereDate('transaction_at', today())
            ->count();

        $totalEarned = \App\Models\PointTransaction::query()
            ->where('points_change', '>', 0)
            ->sum('points_change');

        $totalRedeemed = abs((int) \App\Models\PointTransaction::query()
            ->where('points_change', '<', 0)
            ->sum('points_change'));

        $lastTransaction = \App\Models\PointTransaction::query()
            ->latest('transaction_at')
            ->first();

        $topMembers = \App\Models\Member::query()
            ->orderByDesc('total_points')
            ->limit(3)
            ->get();
    @endphp

    <style>
        /* ==========================================================
           KOPI BANGET CRM - LUXURY HISTORY PAGE
           File: resources/views/filament/admin/pages/crm-history.blade.php
           CSS digabung langsung di file Blade.
           ========================================================== */

        :root {
            --kb-red: #ff1f2d;
            --kb-red-2: #ff4b55;
            --kb-red-dark: #c80f1b;
            --kb-black: #0b1020;
            --kb-black-2: #111827;
            --kb-slate: #334155;
            --kb-muted: #64748b;
            --kb-soft: #f8fafc;
            --kb-line: rgba(148, 163, 184, .24);
            --kb-card: rgba(255, 255, 255, .88);
            --kb-shadow: 0 26px 90px rgba(15, 23, 42, .12);
            --kb-red-shadow: 0 24px 50px rgba(255, 31, 45, .30);
        }

        .fi-main {
            background:
                radial-gradient(circle at top right, rgba(255, 31, 45, .10), transparent 34rem),
                radial-gradient(circle at 12% 24%, rgba(15, 23, 42, .06), transparent 32rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 46%, #f1f5f9 100%) !important;
        }

        .kb-history-page {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            padding-bottom: 2rem;
        }

        .kb-history-page * {
            box-sizing: border-box;
        }

        /* HERO */
        .kb-history-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(320px, .75fr);
            gap: 1.25rem;
            min-height: 300px;
            padding: clamp(1.3rem, 2vw, 2.2rem);
            border-radius: 34px;
            color: #ffffff;
            background:
                radial-gradient(circle at 15% 0%, rgba(255,255,255,.20), transparent 22rem),
                radial-gradient(circle at 92% 10%, rgba(255,31,45,.56), transparent 24rem),
                linear-gradient(135deg, #0b1020 0%, #111827 48%, #1f2937 72%, #e11d2e 138%);
            box-shadow:
                0 28px 90px rgba(15, 23, 42, .22),
                inset 0 1px 0 rgba(255,255,255,.10);
        }

        .kb-history-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 38px 38px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,1), rgba(0,0,0,.18));
            pointer-events: none;
        }

        .kb-history-hero::after {
            content: "";
            position: absolute;
            width: 540px;
            height: 540px;
            right: -220px;
            bottom: -270px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255,255,255,.20), rgba(255,31,45,.32), transparent 68%);
            pointer-events: none;
        }

        .kb-hero-content,
        .kb-hero-side {
            position: relative;
            z-index: 2;
        }

        .kb-hero-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .kb-kicker {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            padding: .56rem .78rem;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 999px;
            background: rgba(255,255,255,.10);
            color: rgba(255,255,255,.76);
            font-size: .72rem;
            font-weight: 950;
            letter-spacing: .14em;
            text-transform: uppercase;
            backdrop-filter: blur(16px);
        }

        .kb-kicker-dot {
            width: .55rem;
            height: .55rem;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 6px rgba(34, 197, 94, .16);
        }

        .kb-title {
            max-width: 820px;
            margin: 1rem 0 0;
            font-size: clamp(2.35rem, 4vw, 4.4rem);
            line-height: .98;
            font-weight: 1000;
            letter-spacing: -.075em;
        }

        .kb-title span {
            display: block;
            margin-top: .25rem;
            color: rgba(255,255,255,.62);
        }

        .kb-subtitle {
            max-width: 760px;
            margin: 1rem 0 0;
            color: rgba(255,255,255,.78);
            font-size: 1rem;
            line-height: 1.75;
        }

        .kb-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .8rem;
            margin-top: 1.35rem;
        }

        .kb-hero-side {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: .85rem;
        }

        .kb-hero-card {
            padding: 1.05rem;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 24px;
            background: rgba(255,255,255,.12);
            box-shadow: 0 18px 45px rgba(0,0,0,.16);
            backdrop-filter: blur(18px);
        }

        .kb-hero-card.light {
            background: rgba(255,255,255,.90);
            color: var(--kb-black);
        }

        .kb-hero-card-label {
            color: inherit;
            opacity: .62;
            font-size: .70rem;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .kb-hero-card-value {
            margin-top: .25rem;
            font-size: 1.25rem;
            line-height: 1.25;
            font-weight: 1000;
            letter-spacing: -.04em;
        }

        .kb-hero-card-note {
            margin-top: .24rem;
            color: inherit;
            opacity: .58;
            font-size: .78rem;
            line-height: 1.45;
        }

        /* BUTTON */
        .kb-btn {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .52rem;
            padding: 0 1.1rem;
            border: 0;
            border-radius: 16px;
            outline: none;
            cursor: pointer;
            text-decoration: none !important;
            font-size: .92rem;
            font-weight: 950;
            transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
            white-space: nowrap;
        }

        .kb-btn:hover {
            transform: translateY(-1px);
        }

        .kb-btn-primary {
            color: #ffffff !important;
            background:
                radial-gradient(circle at 18% 0%, rgba(255,255,255,.28), transparent 28%),
                linear-gradient(135deg, var(--kb-red), var(--kb-red-2));
            box-shadow: var(--kb-red-shadow);
        }

        .kb-btn-dark {
            color: #ffffff !important;
            background: linear-gradient(135deg, #0b1020, #1e293b);
            box-shadow: 0 18px 34px rgba(15,23,42,.20);
        }

        .kb-btn-glass {
            color: #ffffff !important;
            border: 1px solid rgba(255,255,255,.22);
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(12px);
        }

        .kb-btn-light {
            color: var(--kb-slate) !important;
            border: 1px solid rgba(148, 163, 184, .30);
            background: #ffffff;
            box-shadow: 0 12px 28px rgba(15,23,42,.05);
        }

        /* STATS */
        .kb-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .kb-stat-card {
            position: relative;
            overflow: hidden;
            min-height: 112px;
            display: flex;
            align-items: center;
            gap: .95rem;
            padding: 1.15rem;
            border: 1px solid var(--kb-line);
            border-radius: 26px;
            background: rgba(255,255,255,.86);
            box-shadow: 0 20px 60px rgba(15,23,42,.075);
            backdrop-filter: blur(14px);
        }

        .kb-stat-card::after {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            right: -90px;
            top: -90px;
            border-radius: 999px;
            background: rgba(255,31,45,.08);
        }

        .kb-stat-icon {
            position: relative;
            z-index: 1;
            width: 54px;
            height: 54px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 20px;
            background:
                linear-gradient(135deg, rgba(255,31,45,.14), rgba(255,31,45,.04)),
                #ffffff;
            box-shadow: inset 0 0 0 1px rgba(255,31,45,.10);
            font-size: 1.15rem;
        }

        .kb-stat-body {
            position: relative;
            z-index: 1;
            min-width: 0;
        }

        .kb-label {
            color: #94a3b8;
            font-size: .70rem;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .kb-stat-value {
            margin-top: .22rem;
            color: var(--kb-black);
            font-size: 1rem;
            font-weight: 1000;
            letter-spacing: -.035em;
        }

        /* FILTER PANEL */
        .kb-panel {
            overflow: hidden;
            border: 1px solid var(--kb-line);
            border-radius: 32px;
            background: rgba(255,255,255,.90);
            box-shadow: var(--kb-shadow);
            backdrop-filter: blur(16px);
        }

        .kb-filter-panel {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1rem;
            align-items: end;
            padding: 1.1rem;
            background:
                radial-gradient(circle at top right, rgba(255,31,45,.06), transparent 18rem),
                linear-gradient(180deg, rgba(248,250,252,.96), rgba(255,255,255,.86));
            border-bottom: 1px solid var(--kb-line);
        }

        .kb-filter-grid {
            display: grid;
            grid-template-columns: minmax(240px, 1fr) 180px 180px;
            gap: .85rem;
            align-items: end;
        }

        .kb-field label {
            display: block;
            margin-bottom: .38rem;
            color: var(--kb-slate);
            font-size: .76rem;
            font-weight: 950;
        }

        .kb-input-wrap {
            position: relative;
        }

        .kb-input-icon {
            position: absolute;
            top: 50%;
            left: .95rem;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
            pointer-events: none;
        }

        .kb-input {
            width: 100%;
            min-height: 50px;
            padding: 0 1rem 0 2.75rem;
            border: 1px solid rgba(148,163,184,.34);
            border-radius: 17px;
            outline: none;
            background: rgba(255,255,255,.94);
            color: var(--kb-black);
            transition: border .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .kb-input[type="date"] {
            padding-left: 1rem;
        }

        .kb-input:focus {
            border-color: rgba(255,31,45,.56);
            box-shadow: 0 0 0 4px rgba(255,31,45,.09);
            transform: translateY(-1px);
        }

        .kb-input::placeholder {
            color: #94a3b8;
        }

        /* TABLE */
        .kb-table-wrap {
            overflow-x: auto;
        }

        .kb-table {
            width: 100%;
            min-width: 980px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .kb-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: 1rem .95rem;
            color: #94a3b8;
            background: #ffffff;
            border-bottom: 1px solid var(--kb-line);
            font-size: .70rem;
            font-weight: 1000;
            letter-spacing: .12em;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .kb-table tbody td {
            padding: 1rem .95rem;
            border-bottom: 1px solid rgba(148,163,184,.14);
            color: var(--kb-slate);
            vertical-align: middle;
            font-size: .9rem;
        }

        .kb-table tbody tr {
            transition: background .18s ease, transform .18s ease;
        }

        .kb-table tbody tr:hover {
            background: #f8fafc;
        }

        .kb-member-cell {
            display: flex;
            align-items: center;
            gap: .75rem;
            min-width: 210px;
        }

        .kb-avatar {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 15px;
            color: #ffffff;
            background:
                radial-gradient(circle at 30% 10%, rgba(255,255,255,.34), transparent 24%),
                linear-gradient(135deg, #0b1020, #475569);
            font-weight: 1000;
            box-shadow: 0 12px 26px rgba(15,23,42,.14);
        }

        .kb-member-cell strong {
            display: block;
            color: var(--kb-black);
            font-weight: 1000;
        }

        .kb-member-cell span {
            display: block;
            margin-top: .12rem;
            color: var(--kb-muted);
            font-size: .78rem;
        }

        .kb-activity-name {
            color: var(--kb-black);
            font-weight: 900;
        }

        .kb-type-pill {
            display: inline-flex;
            align-items: center;
            gap: .36rem;
            padding: .42rem .62rem;
            border-radius: 999px;
            background: #f1f5f9;
            color: var(--kb-slate);
            font-size: .76rem;
            font-weight: 1000;
            text-transform: capitalize;
        }

        .kb-type-pill.earn {
            background: rgba(34,197,94,.12);
            color: #15803d;
        }

        .kb-type-pill.redeem {
            background: rgba(255,31,45,.12);
            color: var(--kb-red-dark);
        }

        .kb-point-chip {
            display: inline-flex;
            justify-content: center;
            min-width: 72px;
            padding: .42rem .64rem;
            border-radius: 999px;
            background: #f1f5f9;
            color: var(--kb-slate);
            font-size: .78rem;
            font-weight: 1000;
        }

        .kb-point-chip.plus {
            background: rgba(34,197,94,.12);
            color: #15803d;
        }

        .kb-point-chip.minus {
            background: rgba(255,31,45,.12);
            color: var(--kb-red-dark);
        }

        .kb-kasir {
            color: var(--kb-black);
            font-weight: 900;
        }

        .kb-table-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.1rem;
            color: var(--kb-muted);
            background: #ffffff;
            font-size: .86rem;
        }

        .kb-table-footer strong {
            color: var(--kb-black);
        }

        /* EMPTY */
        .kb-empty {
            min-height: 380px;
            display: grid;
            place-items: center;
            align-content: center;
            padding: 2rem;
            text-align: center;
        }

        .kb-empty-orbit {
            position: relative;
            width: 132px;
            height: 132px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background:
                linear-gradient(#fff, #fff) padding-box,
                conic-gradient(from 160deg, rgba(255,31,45,.10), rgba(15,23,42,.18), rgba(255,31,45,.48), rgba(255,31,45,.10)) border-box;
            border: 8px solid transparent;
            box-shadow: 0 26px 60px rgba(15,23,42,.10);
        }

        .kb-empty-icon {
            width: 84px;
            height: 84px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: linear-gradient(135deg, #f1f5f9, #fff);
            color: var(--kb-black);
            font-size: 2rem;
        }

        .kb-empty h2 {
            margin: 1.05rem 0 .4rem;
            color: var(--kb-black);
            font-size: 1.4rem;
            font-weight: 1000;
            letter-spacing: -.04em;
        }

        .kb-empty p {
            max-width: 520px;
            margin: 0 auto;
            color: var(--kb-muted);
            line-height: 1.72;
        }

        /* TOP MEMBER */
        .kb-top-members {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .9rem;
        }

        .kb-top-card {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr) auto;
            align-items: center;
            gap: .8rem;
            padding: .95rem;
            border: 1px solid var(--kb-line);
            border-radius: 22px;
            background: rgba(255,255,255,.86);
            box-shadow: 0 18px 48px rgba(15,23,42,.065);
        }

        .kb-top-card strong {
            display: block;
            color: var(--kb-black);
            font-size: .9rem;
            font-weight: 1000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kb-top-card span {
            display: block;
            margin-top: .1rem;
            color: var(--kb-muted);
            font-size: .78rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kb-top-points {
            padding: .36rem .55rem;
            border-radius: 999px;
            color: var(--kb-red-dark);
            background: rgba(255,31,45,.10);
            font-size: .78rem;
            font-weight: 1000;
            white-space: nowrap;
        }

        @media (max-width: 1280px) {
            .kb-history-hero {
                grid-template-columns: 1fr;
            }

            .kb-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .kb-filter-panel {
                grid-template-columns: 1fr;
            }

            .kb-filter-grid {
                grid-template-columns: 1fr;
            }

            .kb-top-members {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .kb-history-hero,
            .kb-panel {
                border-radius: 26px;
            }

            .kb-title {
                font-size: 2.35rem;
            }

            .kb-stats {
                grid-template-columns: 1fr;
            }

            .kb-table-footer {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>

    <div class="kb-history-page">
        {{-- HERO --}}
        <section class="kb-history-hero">
            <div class="kb-hero-content">
                <div class="kb-kicker">
                    <span class="kb-kicker-dot"></span>
                    CRM Activity Ledger
                </div>

                <h1 class="kb-title">
                    Riwayat Aktivitas
                    <span>loyalty point & redeem reward.</span>
                </h1>

                <p class="kb-subtitle">
                    Pantau seluruh aktivitas member Kopi Banget, mulai dari penambahan poin,
                    redeem reward, transaksi kasir, sampai histori kunjungan pelanggan secara lebih rapi.
                </p>

                <div class="kb-hero-actions">
                    <button type="button" wire:click="exportCsv" class="kb-btn kb-btn-primary">
                        ⬇ Export CSV
                    </button>

                    <a href="{{ \App\Filament\Admin\Pages\CrmDashboard::getUrl() }}" class="kb-btn kb-btn-glass">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

            <div class="kb-hero-side">
                <div class="kb-hero-card">
                    <div class="kb-hero-card-label">Latest Activity</div>
                    <div class="kb-hero-card-value">
                        {{ $lastTransaction?->member?->name ?? 'Belum ada transaksi' }}
                    </div>
                    <div class="kb-hero-card-note">
                        {{ $lastTransaction?->activity_name ?? 'Aktivitas terbaru akan tampil setelah transaksi poin dibuat.' }}
                    </div>
                </div>

                <div class="kb-hero-card light">
                    <div class="kb-hero-card-label">Total Ledger</div>
                    <div class="kb-hero-card-value">{{ number_format($totalTransactions) }} Aktivitas</div>
                    <div class="kb-hero-card-note">
                        Semua transaksi poin dan redeem tercatat sebagai audit trail.
                    </div>
                </div>
            </div>
        </section>

        {{-- STATS --}}
        <section class="kb-stats">
            <div class="kb-stat-card">
                <div class="kb-stat-icon">📜</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Total Aktivitas</div>
                    <div class="kb-stat-value">{{ number_format($totalTransactions) }} Transaksi</div>
                </div>
            </div>

            <div class="kb-stat-card">
                <div class="kb-stat-icon">⚡</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Hari Ini</div>
                    <div class="kb-stat-value">{{ number_format($todayTransactions) }} Transaksi</div>
                </div>
            </div>

            <div class="kb-stat-card">
                <div class="kb-stat-icon">➕</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Poin Ditambahkan</div>
                    <div class="kb-stat-value">{{ number_format($totalEarned) }} Poin</div>
                </div>
            </div>

            <div class="kb-stat-card">
                <div class="kb-stat-icon">🎁</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Poin Diredeem</div>
                    <div class="kb-stat-value">{{ number_format($totalRedeemed) }} Poin</div>
                </div>
            </div>
        </section>

        {{-- TOP MEMBERS --}}
        @if ($topMembers->count() > 0)
            <section class="kb-top-members">
                @foreach ($topMembers as $topMember)
                    <div class="kb-top-card">
                        <div class="kb-avatar">
                            {{ strtoupper(substr($topMember->name, 0, 1)) }}
                        </div>

                        <div>
                            <strong>{{ $topMember->name }}</strong>
                            <span>{{ $topMember->phone }}</span>
                        </div>

                        <div class="kb-top-points">
                            {{ number_format($topMember->total_points) }} Poin
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- FILTER + TABLE --}}
        <section class="kb-panel">
            <div class="kb-filter-panel">
                <div class="kb-filter-grid">
                    <div class="kb-field">
                        <label>Cari Aktivitas</label>
                        <div class="kb-input-wrap">
                            <span class="kb-input-icon">⌕</span>
                            <input
                                type="text"
                                wire:model.live.debounce.500ms="keyword"
                                class="kb-input"
                                placeholder="Cari nama, nomor WA, atau aktivitas"
                            >
                        </div>
                    </div>

                    <div class="kb-field">
                        <label>Tanggal Mulai</label>
                        <input
                            type="date"
                            wire:model.live="startDate"
                            class="kb-input"
                        >
                    </div>

                    <div class="kb-field">
                        <label>Tanggal Akhir</label>
                        <input
                            type="date"
                            wire:model.live="endDate"
                            class="kb-input"
                        >
                    </div>
                </div>

                <button type="button" wire:click="exportCsv" class="kb-btn kb-btn-dark">
                    ⬇ Export CSV
                </button>
            </div>

            @if ($transactions->count() > 0)
                <div class="kb-table-wrap">
                    <table class="kb-table">
                        <thead>
                            <tr>
                                <th>Date / Time</th>
                                <th>Member</th>
                                <th>Activity</th>
                                <th>Type</th>
                                <th>Points Change</th>
                                <th>Kasir</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($transactions as $transaction)
                                @php
                                    $isPlus = $transaction->points_change > 0;
                                    $isMinus = $transaction->points_change < 0;
                                    $memberName = $transaction->member?->name ?? 'Member';
                                    $memberPhone = $transaction->member?->phone ?? '-';
                                    $initial = strtoupper(substr($memberName, 0, 1));
                                @endphp

                                <tr>
                                    <td>
                                        <strong>{{ $transaction->transaction_at?->format('d M Y') ?? $transaction->created_at?->format('d M Y') }}</strong>
                                        <div style="color:#64748b; font-size:.78rem; margin-top:.12rem;">
                                            {{ $transaction->transaction_at?->format('H:i') ?? $transaction->created_at?->format('H:i') }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="kb-member-cell">
                                            <div class="kb-avatar">{{ $initial }}</div>
                                            <div>
                                                <strong>{{ $memberName }}</strong>
                                                <span>{{ $memberPhone }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="kb-activity-name">
                                            {{ $transaction->activity_name ?? '-' }}
                                        </div>
                                    </td>

                                    <td>
                                        <span @class([
                                            'kb-type-pill',
                                            'earn' => $transaction->type === 'earn',
                                            'redeem' => $transaction->type === 'redeem',
                                        ])>
                                            {{ $transaction->type === 'earn' ? '＋' : ($transaction->type === 'redeem' ? '🎁' : '•') }}
                                            {{ $transaction->type }}
                                        </span>
                                    </td>

                                    <td>
                                        <span @class([
                                            'kb-point-chip',
                                            'plus' => $isPlus,
                                            'minus' => $isMinus,
                                        ])>
                                            {{ $isPlus ? '+' : '' }}{{ number_format($transaction->points_change) }} Poin
                                        </span>
                                    </td>

                                    <td>
                                        <div class="kb-kasir">
                                            {{ $transaction->user?->name ?? 'System' }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="kb-table-footer">
                    <span>
                        Showing <strong>{{ $transactions->count() }}</strong> latest entries
                    </span>

                    <span>
                        Generated by <strong>Kopi Banget CRM</strong>
                    </span>
                </div>
            @else
                <div class="kb-empty">
                    <div class="kb-empty-orbit">
                        <div class="kb-empty-icon">📜</div>
                    </div>

                    <h2>Belum ada data riwayat</h2>
                    <p>
                        Aktivitas tambah poin, redeem reward, dan transaksi member akan muncul
                        di halaman ini setelah kasir mulai menggunakan dashboard CRM.
                    </p>
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
