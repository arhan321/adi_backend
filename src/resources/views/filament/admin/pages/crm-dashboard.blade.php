<x-filament-panels::page>
    @php
        $member = $this->getSelectedMember();
        $setting = $this->getSetting();
        $stats = $this->getStats();

        $recentTransactions = \App\Models\PointTransaction::query()
            ->with(['member', 'user'])
            ->latest('transaction_at')
            ->limit(5)
            ->get();

        $directoryMembers = \App\Models\Member::query()
            ->latest()
            ->get();

        $redeemRequiredPoints = (int) ($setting->redeem_required_points ?? 3);
        $rewardName = $setting->reward_name ?? '1 Kopi Gratis';
        $canRedeem = $member && $member->total_points >= $redeemRequiredPoints;
        $progressPercent = $member
            ? min(100, (int) (($member->total_points / max($redeemRequiredPoints, 1)) * 100))
            : 0;

        $memberInitial = $member ? strtoupper(substr($member->name, 0, 1)) : 'K';
    @endphp

    <style>
        /* ==========================================================
           KOPI BANGET CRM DASHBOARD - INLINE LUXURY UI
           File: resources/views/filament/admin/pages/crm-dashboard.blade.php
           Update: Member belum dipilih/detail member dipindah ke atas,
                   daftar semua member dipindah ke bawah,
                   tombol Edit Member ditambahkan,
                   pencarian member dibenahi: nama/nomor + anti salah pilih,
                   hasil pencarian ganda ditampilkan nama + nomor.
           ========================================================== */

        :root {
            --kb-red: #ff1f2d;
            --kb-red-2: #ff4b55;
            --kb-red-dark: #c80f1b;
            --kb-black: #0b1020;
            --kb-black-2: #111827;
            --kb-navy: #141927;
            --kb-slate: #334155;
            --kb-muted: #64748b;
            --kb-soft: #f8fafc;
            --kb-line: rgba(148, 163, 184, .22);
            --kb-card: rgba(255, 255, 255, .82);
            --kb-card-solid: #ffffff;
            --kb-shadow-soft: 0 24px 80px rgba(15, 23, 42, .10);
            --kb-shadow-red: 0 24px 48px rgba(255, 31, 45, .30);
            --kb-radius-xl: 32px;
            --kb-radius-lg: 24px;
            --kb-radius-md: 18px;
        }

        .fi-main {
            background:
                radial-gradient(circle at top right, rgba(255, 31, 45, .10), transparent 34rem),
                radial-gradient(circle at 12% 22%, rgba(15, 23, 42, .06), transparent 32rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 42%, #f1f5f9 100%) !important;
        }

        .kb-luxury-page {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            width: 100%;
            padding-bottom: 2rem;
        }

        .kb-luxury-page * {
            box-sizing: border-box;
        }

        /* HERO */
        .kb-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(300px, .65fr);
            gap: 1.4rem;
            min-height: 320px;
            padding: clamp(1.3rem, 2vw, 2.2rem);
            border-radius: 34px;
            color: #ffffff;
            background:
                radial-gradient(circle at 15% 0%, rgba(255,255,255,.20), transparent 20rem),
                radial-gradient(circle at 90% 10%, rgba(255,31,45,.52), transparent 21rem),
                linear-gradient(135deg, #0b1020 0%, #111827 48%, #1f2937 70%, #e11d2e 135%);
            box-shadow:
                0 28px 90px rgba(15, 23, 42, .22),
                inset 0 1px 0 rgba(255,255,255,.10);
        }

        .kb-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 38px 38px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,1), rgba(0,0,0,.22));
            pointer-events: none;
        }

        .kb-hero::after {
            content: "";
            position: absolute;
            width: 520px;
            height: 520px;
            right: -180px;
            bottom: -260px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255,255,255,.20), rgba(255,31,45,.30), transparent 68%);
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
            padding: .55rem .75rem;
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
            max-width: 800px;
            margin: 1rem 0 0;
            font-size: clamp(2.4rem, 4vw, 4.6rem);
            line-height: .98;
            font-weight: 1000;
            letter-spacing: -.075em;
        }

        .kb-title span {
            display: block;
            margin-top: .2rem;
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

        .kb-hero-metric {
            position: relative;
            overflow: hidden;
            padding: 1.15rem;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 24px;
            background: rgba(255,255,255,.13);
            box-shadow: 0 18px 45px rgba(0,0,0,.16);
            backdrop-filter: blur(18px);
        }

        .kb-hero-metric--light {
            background: rgba(255,255,255,.90);
            color: var(--kb-black);
        }

        .kb-hero-metric-label {
            color: inherit;
            opacity: .62;
            font-size: .70rem;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .kb-hero-metric-value {
            margin-top: .25rem;
            font-size: 1.35rem;
            line-height: 1.2;
            font-weight: 1000;
            letter-spacing: -.04em;
        }

        .kb-hero-metric-note {
            margin-top: .25rem;
            color: inherit;
            opacity: .58;
            font-size: .8rem;
        }

        /* BUTTONS */
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
            background: linear-gradient(135deg, var(--kb-red), var(--kb-red-2));
            box-shadow: var(--kb-shadow-red);
        }

        .kb-btn-dark {
            color: #ffffff !important;
            background: linear-gradient(135deg, #0b1020, #1e293b);
            box-shadow: 0 18px 34px rgba(15,23,42,.20);
        }

        .kb-btn-outline {
            color: var(--kb-black) !important;
            border: 1px solid rgba(148, 163, 184, .30);
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 14px 30px rgba(15,23,42,.08);
        }

        .kb-btn-outline:hover {
            border-color: rgba(255, 31, 45, .34);
            color: var(--kb-red) !important;
        }

        .kb-btn-glass {
            color: #ffffff !important;
            border: 1px solid rgba(255,255,255,.22);
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(12px);
        }

        .kb-btn-redeem {
            color: #ffffff !important;
            min-height: 56px;
            background:
                radial-gradient(circle at 15% 0%, rgba(255,255,255,.28), transparent 26%),
                linear-gradient(135deg, var(--kb-red), var(--kb-red-2));
            box-shadow: var(--kb-shadow-red);
        }

        .kb-btn-disabled,
        .kb-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
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

        /* MAIN GRID */
        .kb-main-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 380px;
            gap: 1.1rem;
            align-items: start;
        }

        .kb-panel {
            overflow: hidden;
            border: 1px solid var(--kb-line);
            border-radius: var(--kb-radius-xl);
            background: rgba(255,255,255,.90);
            box-shadow: var(--kb-shadow-soft);
            backdrop-filter: blur(14px);
        }

        .kb-search-zone {
            padding: 1.15rem;
            border-bottom: 1px solid var(--kb-line);
            background:
                linear-gradient(180deg, rgba(248,250,252,.96), rgba(255,255,255,.82)),
                radial-gradient(circle at top right, rgba(255,31,45,.05), transparent 18rem);
        }

        .kb-search-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .8rem;
        }

        .kb-search-wrap {
            position: relative;
        }

        .kb-search-symbol {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.2rem;
            pointer-events: none;
        }

        .kb-input,
        .kb-search-input {
            width: 100%;
            border: 1px solid rgba(148,163,184,.34);
            border-radius: 17px;
            outline: none;
            background: rgba(255,255,255,.94);
            color: var(--kb-black);
            transition: border .18s ease, box-shadow .18s ease;
        }

        .kb-search-input {
            min-height: 56px;
            padding: 0 1rem 0 2.9rem;
            font-size: .98rem;
        }

        .kb-input {
            min-height: 48px;
            padding: 0 .9rem;
        }

        .kb-input:focus,
        .kb-search-input:focus {
            border-color: rgba(255,31,45,.56);
            box-shadow: 0 0 0 4px rgba(255,31,45,.09);
        }

        .kb-search-hint {
            margin-top: .75rem;
            padding: .85rem 1rem;
            border: 1px solid rgba(148, 163, 184, .22);
            border-radius: 16px;
            background: rgba(255, 255, 255, .82);
            color: var(--kb-muted);
            font-size: .88rem;
            line-height: 1.55;
        }

        .kb-search-hint strong {
            color: var(--kb-black);
            font-weight: 950;
        }

        .kb-search-results {
            margin-top: .85rem;
            display: grid;
            gap: .7rem;
        }

        .kb-search-results-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
            color: var(--kb-muted);
            font-size: .86rem;
            font-weight: 800;
        }

        .kb-search-results-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 28px;
            padding: 0 .55rem;
            border-radius: 999px;
            background: rgba(255, 31, 45, .10);
            color: var(--kb-red);
            font-weight: 1000;
        }

        .kb-search-result-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .kb-search-result-card {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: .85rem;
            padding: .9rem;
            border: 1px solid rgba(148, 163, 184, .24);
            border-radius: 20px;
            background:
                radial-gradient(circle at top right, rgba(255, 31, 45, .08), transparent 42%),
                rgba(255, 255, 255, .94);
            box-shadow: 0 16px 34px rgba(15, 23, 42, .055);
        }

        .kb-search-result-avatar {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: linear-gradient(135deg, #111827, #334155);
            color: #fff;
            font-size: 1rem;
            font-weight: 1000;
            box-shadow: 0 14px 28px rgba(15, 23, 42, .14);
        }

        .kb-search-result-info {
            min-width: 0;
        }

        .kb-search-result-info h4 {
            margin: 0;
            color: var(--kb-black);
            font-size: .98rem;
            font-weight: 1000;
            letter-spacing: -.025em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kb-search-result-phone {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-top: .22rem;
            color: var(--kb-slate);
            font-size: .84rem;
            font-weight: 800;
        }

        .kb-search-result-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .38rem;
        }

        .kb-search-result-meta span {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 0 .55rem;
            border: 1px solid rgba(148, 163, 184, .20);
            border-radius: 999px;
            background: rgba(248, 250, 252, .78);
            color: #64748b;
            font-size: .70rem;
            font-weight: 900;
        }

        .kb-search-result-actions {
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .kb-search-result-actions .kb-btn {
            min-height: 38px;
            padding: 0 .85rem;
            border-radius: 13px;
            font-size: .78rem;
            white-space: nowrap;
        }

        .kb-search-result-actions .kb-btn-outline {
            background: #fff;
        }

        /* EMPTY */
        .kb-empty {
            min-height: 420px;
            display: grid;
            place-items: center;
            align-content: center;
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid var(--kb-line);
            background:
                radial-gradient(circle at top, rgba(255,31,45,.08), transparent 22rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .kb-empty-orbit {
            position: relative;
            width: 138px;
            height: 138px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background:
                linear-gradient(#fff, #fff) padding-box,
                conic-gradient(from 160deg, rgba(255,31,45,.08), rgba(15,23,42,.18), rgba(255,31,45,.52), rgba(255,31,45,.08)) border-box;
            border: 8px solid transparent;
            box-shadow: 0 26px 60px rgba(15,23,42,.10);
        }

        .kb-empty-orbit::after {
            content: "";
            position: absolute;
            right: 15px;
            bottom: 17px;
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 4px solid #fff;
            background: #22c55e;
        }

        .kb-empty-avatar {
            width: 88px;
            height: 88px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: linear-gradient(135deg, #f1f5f9, #fff);
            color: var(--kb-black);
            font-size: 2.1rem;
        }

        .kb-empty h2 {
            margin: 1.1rem 0 .4rem;
            color: var(--kb-black);
            font-size: 1.45rem;
            font-weight: 1000;
            letter-spacing: -.04em;
        }

        .kb-empty p {
            max-width: 560px;
            margin: 0 auto 1.25rem;
            color: var(--kb-muted);
            line-height: 1.72;
        }

        /* MEMBER SELECTED */
        .kb-member-layout {
            display: grid;
            grid-template-columns: 340px minmax(0, 1fr);
            min-height: 560px;
            border-bottom: 1px solid var(--kb-line);
        }

        .kb-profile-card {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1.45rem;
            border-right: 1px solid var(--kb-line);
            background:
                radial-gradient(circle at top, rgba(255,31,45,.08), transparent 19rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .kb-profile-avatar-wrap {
            position: relative;
            margin-top: .25rem;
        }

        .kb-profile-avatar {
            width: 116px;
            height: 116px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            color: #fff;
            font-size: 2.35rem;
            font-weight: 1000;
            background:
                radial-gradient(circle at 30% 10%, rgba(255,255,255,.36), transparent 24%),
                linear-gradient(135deg, #0b1020, #475569);
            box-shadow:
                0 26px 58px rgba(15,23,42,.22),
                0 0 0 8px #fff,
                0 0 0 11px rgba(255,31,45,.13);
        }

        .kb-profile-dot {
            position: absolute;
            right: 6px;
            bottom: 10px;
            width: 20px;
            height: 20px;
            border: 4px solid #fff;
            border-radius: 999px;
            background: #22c55e;
        }

        .kb-profile-name {
            margin: 1.25rem 0 .2rem;
            color: var(--kb-black);
            font-size: 1.5rem;
            font-weight: 1000;
            letter-spacing: -.05em;
            text-align: center;
        }

        .kb-profile-code {
            color: var(--kb-muted);
            font-size: .84rem;
        }

        .kb-active-pill {
            margin-top: .8rem;
            padding: .48rem .82rem;
            border-radius: 999px;
            color: #15803d;
            background: rgba(34,197,94,.12);
            font-size: .72rem;
            font-weight: 1000;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .kb-profile-edit {
            width: 100%;
            min-height: 44px;
            margin-top: .9rem;
            border-radius: 14px;
            font-size: .84rem;
        }

        .kb-profile-line {
            width: 100%;
            height: 1px;
            margin: 1.5rem 0;
            background: var(--kb-line);
        }

        .kb-profile-meta {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: .9rem;
        }

        .kb-profile-meta div {
            display: flex;
            flex-direction: column;
            gap: .18rem;
        }

        .kb-profile-meta span {
            color: #94a3b8;
            font-size: .70rem;
            font-weight: 950;
            letter-spacing: .10em;
            text-transform: uppercase;
        }

        .kb-profile-meta strong {
            color: var(--kb-slate);
            font-size: .9rem;
            line-height: 1.35;
        }

        .kb-loyalty {
            padding: clamp(1.3rem, 2vw, 2rem);
            background:
                radial-gradient(circle at 92% 0%, rgba(255,31,45,.06), transparent 18rem),
                #fff;
        }

        .kb-loyalty-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .kb-point-number {
            display: flex;
            align-items: baseline;
            gap: .75rem;
            margin-top: .25rem;
            color: var(--kb-red);
            font-size: clamp(3.5rem, 7vw, 6rem);
            line-height: .95;
            font-weight: 1000;
            letter-spacing: -.09em;
        }

        .kb-point-number span {
            color: #a1a1aa;
            font-size: 1.2rem;
            font-weight: 950;
            letter-spacing: -.04em;
        }

        .kb-reward-badge {
            padding: .68rem .9rem;
            border-radius: 18px;
            color: var(--kb-red-dark);
            background: rgba(255,31,45,.08);
            font-size: .84rem;
            font-weight: 950;
        }

        .kb-progress-card {
            margin-top: 1.25rem;
            padding: 1rem;
            border: 1px solid var(--kb-line);
            border-radius: 24px;
            background: #f8fafc;
        }

        .kb-progress-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            color: var(--kb-slate);
            font-size: .9rem;
            font-weight: 950;
        }

        .kb-progress-track {
            height: 14px;
            overflow: hidden;
            margin-top: .8rem;
            border-radius: 999px;
            background: #e2e8f0;
        }

        .kb-progress-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--kb-red), #fb7185);
            box-shadow: 0 8px 20px rgba(255,31,45,.28);
        }

        .kb-progress-note {
            margin-top: .65rem;
            color: var(--kb-muted);
            font-size: .86rem;
            line-height: 1.55;
        }

        .kb-action-box {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.25rem;
        }

        .kb-point-form {
            display: grid;
            grid-template-columns: 140px minmax(0, 1fr) auto;
            align-items: end;
            gap: .8rem;
        }

        .kb-field label {
            display: block;
            margin-bottom: .36rem;
            color: var(--kb-slate);
            font-size: .76rem;
            font-weight: 950;
        }

        /* MEMBER DIRECTORY */
        .kb-member-directory {
            padding: 1.15rem;
            background:
                radial-gradient(circle at top right, rgba(255,31,45,.06), transparent 22rem),
                linear-gradient(180deg, rgba(255,255,255,.96), rgba(248,250,252,.96));
        }

        .kb-directory-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .kb-directory-head h2 {
            margin: .2rem 0 0;
            color: var(--kb-black);
            font-size: 1.35rem;
            font-weight: 1000;
            letter-spacing: -.05em;
        }

        .kb-directory-head p {
            max-width: 600px;
            margin: 0;
            color: var(--kb-muted);
            font-size: .9rem;
            line-height: 1.6;
        }

        .kb-member-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .9rem;
        }

        .kb-member-card-mini {
            position: relative;
            overflow: hidden;
            padding: 1rem;
            border: 1px solid rgba(148, 163, 184, .25);
            border-radius: 24px;
            background:
                radial-gradient(circle at top right, rgba(255, 31, 45, .10), transparent 38%),
                #fff;
            box-shadow: 0 15px 34px rgba(15, 23, 42, .055);
            transition: .22s ease;
        }

        .kb-member-card-mini:hover,
        .kb-member-card-mini.is-selected {
            transform: translateY(-2px);
            border-color: rgba(255, 31, 45, .32);
            box-shadow: 0 22px 48px rgba(15, 23, 42, .09);
        }

        .kb-member-mini-top {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: .85rem;
        }

        .kb-member-mini-avatar {
            width: 44px;
            height: 44px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: linear-gradient(135deg, #111827, #334155);
            color: #fff;
            font-weight: 1000;
        }

        .kb-member-mini-top h3 {
            margin: 0;
            color: var(--kb-black);
            font-size: 1rem;
            font-weight: 1000;
        }

        .kb-member-mini-top span {
            display: block;
            color: var(--kb-muted);
            font-size: .82rem;
            word-break: break-all;
        }

        .kb-member-mini-info {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .55rem;
        }

        .kb-member-mini-info div {
            padding: .7rem;
            border: 1px solid rgba(148, 163, 184, .20);
            border-radius: 16px;
            background: rgba(255, 255, 255, .78);
        }

        .kb-member-mini-info small {
            display: block;
            color: #94a3b8;
            font-size: .66rem;
            font-weight: 950;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .kb-member-mini-info strong {
            display: block;
            margin-top: .15rem;
            color: #334155;
            font-size: .82rem;
            font-weight: 1000;
            word-break: break-word;
        }

        .kb-mini-progress {
            overflow: hidden;
            height: 10px;
            margin-top: .9rem;
            border-radius: 999px;
            background: #e2e8f0;
        }

        .kb-mini-progress span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--kb-red), #fb7185);
        }

        .kb-member-mini-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .55rem;
            margin-top: .85rem;
        }

        .kb-member-mini-actions .kb-btn {
            width: 100%;
            min-height: 42px;
            justify-content: center;
            border-radius: 14px;
            font-size: .82rem;
        }

        .kb-member-empty-list {
            grid-column: 1 / -1;
            min-height: 180px;
            display: grid;
            place-items: center;
            text-align: center;
            color: var(--kb-muted);
        }

        .kb-member-empty-list div {
            font-size: 2rem;
        }

        .kb-member-empty-list strong {
            margin-top: .35rem;
            color: var(--kb-black);
            font-weight: 1000;
        }

        /* ACTIVITY */
        .kb-side-panel {
            padding: 1.2rem;
        }

        .kb-side-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .kb-side-head h3 {
            margin: .2rem 0 0;
            color: var(--kb-black);
            font-size: 1.22rem;
            font-weight: 1000;
            letter-spacing: -.045em;
        }

        .kb-side-link {
            color: var(--kb-red);
            text-decoration: none !important;
            font-size: .82rem;
            font-weight: 950;
        }

        .kb-activity-list {
            display: flex;
            flex-direction: column;
            gap: .72rem;
        }

        .kb-activity-item {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: .72rem;
            padding: .86rem;
            border: 1px solid rgba(148,163,184,.18);
            border-radius: 19px;
            background: #fff;
        }

        .kb-activity-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #94a3b8;
        }

        .kb-activity-dot.plus {
            background: #22c55e;
            box-shadow: 0 0 0 5px rgba(34,197,94,.12);
        }

        .kb-activity-dot.minus {
            background: var(--kb-red);
            box-shadow: 0 0 0 5px rgba(255,31,45,.12);
        }

        .kb-activity-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .kb-activity-text strong {
            color: var(--kb-black);
            font-size: .88rem;
            font-weight: 1000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kb-activity-text span,
        .kb-activity-text small {
            color: var(--kb-muted);
            font-size: .76rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kb-chip {
            padding: .32rem .55rem;
            border-radius: 999px;
            background: #f1f5f9;
            color: var(--kb-slate);
            font-size: .76rem;
            font-weight: 1000;
        }

        .kb-chip.plus {
            background: rgba(34,197,94,.12);
            color: #15803d;
        }

        .kb-chip.minus {
            background: rgba(255,31,45,.12);
            color: var(--kb-red-dark);
        }

        .kb-mini-empty {
            min-height: 260px;
            display: grid;
            align-content: center;
            justify-items: center;
            gap: .3rem;
            color: var(--kb-muted);
            text-align: center;
        }

        .kb-mini-empty div {
            font-size: 2rem;
        }

        .kb-mini-empty strong {
            color: var(--kb-black);
        }

        /* RESPONSIVE */
        @media (max-width: 1280px) {
            .kb-main-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1120px) {
            .kb-hero {
                grid-template-columns: 1fr;
            }

            .kb-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .kb-member-layout {
                grid-template-columns: 1fr;
            }

            .kb-profile-card {
                border-right: 0;
                border-bottom: 1px solid var(--kb-line);
            }

            .kb-directory-head {
                flex-direction: column;
                align-items: stretch;
            }

            .kb-member-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .kb-hero {
                padding: 1.2rem;
                border-radius: 26px;
            }

            .kb-title {
                font-size: 2.35rem;
            }

            .kb-stats {
                grid-template-columns: 1fr;
            }

            .kb-search-form,
            .kb-point-form {
                grid-template-columns: 1fr;
            }

            .kb-loyalty-top {
                flex-direction: column;
            }

            .kb-member-grid,
            .kb-member-mini-info,
            .kb-member-mini-actions,
            .kb-search-result-grid {
                grid-template-columns: 1fr;
            }

            .kb-search-result-card {
                grid-template-columns: auto minmax(0, 1fr);
            }

            .kb-search-result-actions {
                grid-column: 1 / -1;
                width: 100%;
            }

            .kb-search-result-actions .kb-btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>

    <div class="kb-luxury-page">
        {{-- HERO --}}
        <section class="kb-hero">
            <div class="kb-hero-content">
                <div class="kb-kicker">
                    <span class="kb-kicker-dot"></span>
                    CRM Loyalty Dashboard
                </div>

                <h1 class="kb-title">
                    Kopi Banget CRM
                    <span>Customer Loyalty Center</span>
                </h1>

                <p class="kb-subtitle">
                    Dashboard kasir premium untuk mengelola member, loyalty point, redeem reward,
                    dan notifikasi WhatsApp Gateway secara cepat, rapi, dan terpusat.
                </p>

                <div class="kb-hero-actions">
                    <button type="button" wire:click="goToAddMember" class="kb-btn kb-btn-primary">
                        <span>＋</span>
                        Tambah Member
                    </button>

                    <a href="{{ \App\Filament\Admin\Pages\CrmHistory::getUrl() }}" class="kb-btn kb-btn-glass">
                        Lihat History
                    </a>
                </div>
            </div>

            <div class="kb-hero-side">
                <div class="kb-hero-metric">
                    <div class="kb-hero-metric-label">Automated Retention</div>
                    <div class="kb-hero-metric-value">{{ $stats['retention_days'] ?? 14 }} Days</div>
                    <div class="kb-hero-metric-note">Reminder WhatsApp untuk pelanggan pasif</div>
                </div>

                <div class="kb-hero-metric kb-hero-metric--light">
                    <div class="kb-hero-metric-label">Master Promo</div>
                    <div class="kb-hero-metric-value">{{ $stats['redeem_rule'] ?? '3 Poin = 1 Kopi Gratis' }}</div>
                    <div class="kb-hero-metric-note">Aturan reward yang sedang aktif</div>
                </div>
            </div>
        </section>

        {{-- STATS --}}
        <section class="kb-stats">
            <div class="kb-stat-card">
                <div class="kb-stat-icon">🔁</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Automated Retention</div>
                    <div class="kb-stat-value">Retention: {{ $stats['retention_days'] ?? 14 }} Days</div>
                </div>
            </div>

            <div class="kb-stat-card">
                <div class="kb-stat-icon">🎁</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Master Promo</div>
                    <div class="kb-stat-value">{{ $stats['redeem_rule'] ?? '3 Poin = 1 Kopi Gratis' }}</div>
                </div>
            </div>

            <div class="kb-stat-card">
                <div class="kb-stat-icon">👥</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Total Member</div>
                    <div class="kb-stat-value">{{ number_format($stats['total_member'] ?? 0) }} Members</div>
                </div>
            </div>

            <div class="kb-stat-card">
                <div class="kb-stat-icon">⚡</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Aktivitas Hari Ini</div>
                    <div class="kb-stat-value">{{ number_format($stats['today_activity'] ?? 0) }} Transaksi</div>
                </div>
            </div>
        </section>

        {{-- MAIN --}}
        <section class="kb-main-grid">
            <main class="kb-panel">
                <div class="kb-search-zone">
                    <form wire:submit.prevent="searchMember" class="kb-search-form">
                        <div class="kb-search-wrap">
                            <span class="kb-search-symbol">⌕</span>
                            <input
                                type="text"
                                wire:model.defer="searchPhone"
                                class="kb-search-input"
                                placeholder="Cari nama lengkap atau nomor WhatsApp, contoh: adi zacky / 6281234567890"
                            >
                        </div>

                        <button type="submit" class="kb-btn kb-btn-dark">
                            Cari Member
                        </button>
                    </form>

                    @if ($this->searchFeedback)
                        <div class="kb-search-hint">
                            {{ $this->searchFeedback }}
                        </div>
                    @endif

                    @if (! empty($this->memberSearchResults))
                        <div class="kb-search-results">
                            <div class="kb-search-results-head">
                                <span>Hasil member yang cocok. Pilih berdasarkan nama dan nomor WhatsApp.</span>
                                <span class="kb-search-results-count">{{ count($this->memberSearchResults) }}</span>
                            </div>

                            <div class="kb-search-result-grid">
                                @foreach ($this->memberSearchResults as $searchResultMember)
                                    <article class="kb-search-result-card">
                                        <div class="kb-search-result-avatar">
                                            {{ $searchResultMember['initial'] ?? 'M' }}
                                        </div>

                                        <div class="kb-search-result-info">
                                            <h4>{{ $searchResultMember['name'] }}</h4>
                                            <div class="kb-search-result-phone">
                                                📞 {{ $searchResultMember['phone'] }}
                                            </div>
                                            <div class="kb-search-result-meta">
                                                <span>{{ $searchResultMember['member_code'] ?? 'KB-MEMBER' }}</span>
                                                <span>{{ number_format((int) ($searchResultMember['total_points'] ?? 0)) }} Poin</span>
                                                <span>{{ $searchResultMember['last_visit_at'] ?? 'Belum ada kunjungan' }}</span>
                                            </div>
                                        </div>

                                        <div class="kb-search-result-actions">
                                            <button
                                                type="button"
                                                class="kb-btn kb-btn-dark"
                                                wire:click="selectMemberFromSearchResult({{ (int) $searchResultMember['id'] }})"
                                            >
                                                Pilih
                                            </button>

                                            <a
                                                href="{{ \App\Filament\Admin\Pages\CrmEditMember::getUrl(['member' => $searchResultMember['id']]) }}"
                                                class="kb-btn kb-btn-outline"
                                            >
                                                Edit
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- BAGIAN INI SEKARANG DI ATAS: DETAIL MEMBER / MEMBER BELUM DIPILIH --}}
                @if ($member)
                    <div class="kb-member-layout">
                        <aside class="kb-profile-card">
                            <div class="kb-profile-avatar-wrap">
                                <div class="kb-profile-avatar">
                                    {{ $memberInitial }}
                                </div>
                                <span class="kb-profile-dot"></span>
                            </div>

                            <h2 class="kb-profile-name">{{ $member->name }}</h2>
                            <div class="kb-profile-code">{{ $member->member_code ?? 'KB-MEMBER' }}</div>

                            <div class="kb-active-pill">Active Member</div>

                            <a
                                href="{{ \App\Filament\Admin\Pages\CrmEditMember::getUrl(['member' => $member->id]) }}"
                                class="kb-btn kb-btn-outline kb-profile-edit"
                            >
                                ✏️ Edit Member
                            </a>

                            <div class="kb-profile-line"></div>

                            <div class="kb-profile-meta">
                                <div>
                                    <span>Nomor WhatsApp</span>
                                    <strong>{{ $member->phone }}</strong>
                                </div>

                                <div>
                                    <span>Last Visit</span>
                                    <strong>
                                        {{ $member->last_visit_at ? $member->last_visit_at->format('d M Y, H:i') : 'Belum ada kunjungan' }}
                                    </strong>
                                </div>

                                <div>
                                    <span>Member Since</span>
                                    <strong>{{ $member->created_at?->format('d M Y') ?? '-' }}</strong>
                                </div>
                            </div>
                        </aside>

                        <section class="kb-loyalty">
                            <div class="kb-loyalty-top">
                                <div>
                                    <div class="kb-label">Current Loyalty Balance</div>
                                    <div class="kb-point-number">
                                        {{ number_format($member->total_points) }}
                                        <span>Total Poin</span>
                                    </div>
                                </div>

                                <div class="kb-reward-badge">
                                    🎁 {{ $rewardName }}
                                </div>
                            </div>

                            <div class="kb-progress-card">
                                <div class="kb-progress-head">
                                    <span>Progress Redeem</span>
                                    <strong>{{ $member->total_points }}/{{ $redeemRequiredPoints }} Poin</strong>
                                </div>

                                <div class="kb-progress-track">
                                    <div class="kb-progress-fill" style="width: {{ $progressPercent }}%"></div>
                                </div>

                                <div class="kb-progress-note">
                                    @if ($canRedeem)
                                        Poin sudah cukup. Reward dapat ditukarkan sekarang.
                                    @else
                                        Butuh {{ max(0, $redeemRequiredPoints - $member->total_points) }} poin lagi untuk redeem.
                                    @endif
                                </div>
                            </div>

                            <div class="kb-action-box">
                                <form wire:submit.prevent="addPoints" class="kb-point-form">
                                    <div class="kb-field">
                                        <label>Tambah Poin</label>
                                        <input
                                            type="number"
                                            min="1"
                                            wire:model.defer="pointsInput"
                                            class="kb-input"
                                        >
                                    </div>

                                    <div class="kb-field">
                                        <label>Aktivitas</label>
                                        <input
                                            type="text"
                                            wire:model.defer="activityName"
                                            class="kb-input"
                                            placeholder="Contoh: Pembelian Kopi Susu"
                                        >
                                    </div>

                                    <button type="submit" class="kb-btn kb-btn-dark">
                                        Simpan Poin
                                    </button>
                                </form>

                                <button
                                    type="button"
                                    wire:click="redeem"
                                    @class([
                                        'kb-btn',
                                        'kb-btn-redeem',
                                        'kb-btn-disabled' => ! $canRedeem,
                                    ])
                                    @disabled(! $canRedeem)
                                >
                                    🎁 Redeem Reward
                                </button>
                            </div>
                        </section>
                    </div>
                @else
                    <div class="kb-empty">
                        <div class="kb-empty-orbit">
                            <div class="kb-empty-avatar">👤</div>
                        </div>

                        <h2>Member belum dipilih</h2>
                        <p>
                            Daftar semua member sudah tampil di bawah. Kamu bisa mencari berdasarkan
                            <strong>nama lengkap</strong> atau <strong>nomor WhatsApp</strong>. Jika ada beberapa member
                            yang cocok, sistem akan menampilkan nama dan nomor WhatsApp agar kamu bisa memilih data yang benar.
                        </p>

                        <button type="button" wire:click="goToAddMember" class="kb-btn kb-btn-primary">
                            ＋ Tambah Member Baru
                        </button>
                    </div>
                @endif

                {{-- BAGIAN INI SEKARANG DI BAWAH: SEMUA MEMBER --}}
                <section class="kb-member-directory">
                    <div class="kb-directory-head">
                        <div>
                            <div class="kb-label">Member Directory</div>
                            <h2>Semua Member</h2>
                        </div>

                        <p>
                            Semua member ditampilkan di sini. Klik <strong>Pakai Nomor</strong> untuk memakai nomor
                            sebagai pencarian paling akurat, tekan <strong>Pilih</strong> untuk membuka profil,
                            atau tekan <strong>Edit</strong> untuk mengubah data member.
                        </p>
                    </div>

                    <div class="kb-member-grid">
                        @forelse ($directoryMembers as $directoryMember)
                            @php
                                $memberProgress = min(100, (int) (($directoryMember->total_points / max($redeemRequiredPoints, 1)) * 100));
                                $directoryInitial = strtoupper(substr($directoryMember->name, 0, 1));
                                $isSelectedMember = $member && $member->id === $directoryMember->id;
                            @endphp

                            <article class="kb-member-card-mini {{ $isSelectedMember ? 'is-selected' : '' }}">
                                <div class="kb-member-mini-top">
                                    <div class="kb-member-mini-avatar">
                                        {{ $directoryInitial }}
                                    </div>

                                    <div>
                                        <h3>{{ $directoryMember->name }}</h3>
                                        <span>{{ $directoryMember->phone }}</span>
                                    </div>
                                </div>

                                <div class="kb-member-mini-info">
                                    <div>
                                        <small>Total Poin</small>
                                        <strong>{{ number_format($directoryMember->total_points) }} Poin</strong>
                                    </div>

                                    <div>
                                        <small>Status</small>
                                        <strong>{{ $memberProgress }}% Progress</strong>
                                    </div>

                                    <div>
                                        <small>Kode Member</small>
                                        <strong>{{ $directoryMember->member_code ?? 'KB-MEMBER' }}</strong>
                                    </div>

                                    <div>
                                        <small>Last Visit</small>
                                        <strong>
                                            {{ $directoryMember->last_visit_at ? $directoryMember->last_visit_at->diffForHumans() : 'Belum ada' }}
                                        </strong>
                                    </div>
                                </div>

                                <div class="kb-mini-progress">
                                    <span style="width: {{ $memberProgress }}%"></span>
                                </div>

                                <div class="kb-member-mini-actions">
                                    <button
                                        type="button"
                                        class="kb-btn kb-btn-dark"
                                        wire:click="$set('searchPhone', @js($directoryMember->phone))"
                                    >
                                        Pakai Nomor
                                    </button>

                                    <button
                                        type="button"
                                        class="kb-btn kb-btn-primary"
                                        wire:click="$set('selectedMemberId', {{ $directoryMember->id }})"
                                    >
                                        Pilih
                                    </button>

                                    <a
                                        href="{{ \App\Filament\Admin\Pages\CrmEditMember::getUrl(['member' => $directoryMember->id]) }}"
                                        class="kb-btn kb-btn-outline"
                                    >
                                        Edit
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="kb-member-empty-list">
                                <div>☕</div>
                                <strong>Belum ada member</strong>
                                <span>Silakan tambahkan member baru terlebih dahulu.</span>
                            </div>
                        @endforelse
                    </div>
                </section>
            </main>

            <aside class="kb-panel kb-side-panel">
                <div class="kb-side-head">
                    <div>
                        <div class="kb-label">Live Activity</div>
                        <h3>Riwayat Terbaru</h3>
                    </div>

                    <a href="{{ \App\Filament\Admin\Pages\CrmHistory::getUrl() }}" class="kb-side-link">
                        Detail
                    </a>
                </div>

                <div class="kb-activity-list">
                    @forelse ($recentTransactions as $transaction)
                        <div class="kb-activity-item">
                            <div @class([
                                'kb-activity-dot',
                                'plus' => $transaction->points_change > 0,
                                'minus' => $transaction->points_change < 0,
                            ])></div>

                            <div class="kb-activity-text">
                                <strong>{{ $transaction->member?->name ?? 'Member' }}</strong>
                                <span>{{ $transaction->activity_name ?? ucfirst($transaction->type) }}</span>
                                <small>{{ $transaction->transaction_at?->diffForHumans() ?? $transaction->created_at?->diffForHumans() }}</small>
                            </div>

                            <div @class([
                                'kb-chip',
                                'plus' => $transaction->points_change > 0,
                                'minus' => $transaction->points_change < 0,
                            ])>
                                {{ $transaction->points_change > 0 ? '+' : '' }}{{ $transaction->points_change }}
                            </div>
                        </div>
                    @empty
                        <div class="kb-mini-empty">
                            <div>☕</div>
                            <strong>Belum ada aktivitas</strong>
                            <span>Transaksi poin akan tampil di sini.</span>
                        </div>
                    @endforelse
                </div>
            </aside>
        </section>
    </div>
</x-filament-panels::page>
