<x-filament-panels::page>
    <style>
        /* ==========================================================
           KOPI BANGET CRM - LUXURY SETTINGS PAGE
           File: resources/views/filament/admin/pages/crm-settings-page.blade.php
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

        .kb-settings-page {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            padding-bottom: 2rem;
        }

        .kb-settings-page * {
            box-sizing: border-box;
        }

        /* HERO */
        .kb-settings-hero {
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

        .kb-settings-hero::before {
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

        .kb-settings-hero::after {
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
            min-height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .52rem;
            padding: 0 1.1rem;
            border: 0;
            border-radius: 17px;
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

        .kb-stat-note {
            margin-top: .1rem;
            color: var(--kb-muted);
            font-size: .78rem;
        }

        /* FORM GRID */
        .kb-settings-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 420px;
            gap: 1.15rem;
            align-items: start;
        }

        .kb-panel {
            overflow: hidden;
            border: 1px solid var(--kb-line);
            border-radius: 32px;
            background: rgba(255,255,255,.90);
            box-shadow: var(--kb-shadow);
            backdrop-filter: blur(16px);
        }

        .kb-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem;
            border-bottom: 1px solid var(--kb-line);
            background:
                radial-gradient(circle at top right, rgba(255,31,45,.06), transparent 18rem),
                linear-gradient(180deg, rgba(248,250,252,.96), rgba(255,255,255,.86));
        }

        .kb-panel-icon {
            width: 54px;
            height: 54px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 20px;
            color: var(--kb-red);
            background: rgba(255,31,45,.10);
            font-size: 1.2rem;
        }

        .kb-panel-title-wrap {
            display: flex;
            align-items: center;
            gap: .85rem;
        }

        .kb-panel-title {
            margin: 0;
            color: var(--kb-black);
            font-size: 1.25rem;
            font-weight: 1000;
            letter-spacing: -.04em;
        }

        .kb-panel-subtitle {
            margin-top: .18rem;
            color: var(--kb-muted);
            font-size: .88rem;
            line-height: 1.55;
        }

        .kb-panel-body {
            padding: 1.25rem;
        }

        .kb-section-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .kb-field {
            display: flex;
            flex-direction: column;
            gap: .42rem;
        }

        .kb-field.full {
            grid-column: 1 / -1;
        }

        .kb-field label {
            color: var(--kb-black);
            font-size: .84rem;
            font-weight: 950;
        }

        .kb-field-help {
            color: var(--kb-muted);
            font-size: .78rem;
            line-height: 1.45;
        }

        .kb-input-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: .7rem;
        }

        .kb-input,
        .kb-textarea {
            width: 100%;
            border: 1px solid rgba(148,163,184,.34);
            border-radius: 18px;
            outline: none;
            background: rgba(255,255,255,.94);
            color: var(--kb-black);
            font-size: .95rem;
            transition: border .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .kb-input {
            min-height: 52px;
            padding: 0 1rem;
        }

        .kb-textarea {
            min-height: 132px;
            padding: .95rem 1rem;
            resize: vertical;
            line-height: 1.6;
        }

        .kb-input:focus,
        .kb-textarea:focus {
            border-color: rgba(255,31,45,.56);
            box-shadow: 0 0 0 4px rgba(255,31,45,.09);
            transform: translateY(-1px);
        }

        .kb-unit {
            min-height: 52px;
            display: inline-flex;
            align-items: center;
            padding: 0 1rem;
            border-radius: 18px;
            color: var(--kb-muted);
            background: #f8fafc;
            border: 1px solid rgba(148,163,184,.20);
            font-weight: 850;
        }

        .kb-error {
            color: var(--kb-red-dark);
            font-size: .78rem;
            font-weight: 850;
        }

        /* SWITCH */
        .kb-switch-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid rgba(148,163,184,.22);
            border-radius: 22px;
            background: #ffffff;
        }

        .kb-switch-title {
            color: var(--kb-black);
            font-size: .92rem;
            font-weight: 1000;
        }

        .kb-switch-desc {
            margin-top: .16rem;
            color: var(--kb-muted);
            font-size: .78rem;
            line-height: 1.45;
        }

        .kb-toggle {
            position: relative;
            width: 58px;
            height: 34px;
            flex: 0 0 auto;
        }

        .kb-toggle input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .kb-toggle-slider {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: #cbd5e1;
            cursor: pointer;
            transition: .2s ease;
            box-shadow: inset 0 2px 6px rgba(15,23,42,.12);
        }

        .kb-toggle-slider::after {
            content: "";
            position: absolute;
            top: 4px;
            left: 4px;
            width: 26px;
            height: 26px;
            border-radius: 999px;
            background: #ffffff;
            box-shadow: 0 6px 14px rgba(15,23,42,.20);
            transition: .2s ease;
        }

        .kb-toggle input:checked + .kb-toggle-slider {
            background: linear-gradient(135deg, var(--kb-red), var(--kb-red-2));
        }

        .kb-toggle input:checked + .kb-toggle-slider::after {
            transform: translateX(24px);
        }

        /* TEMPLATES */
        .kb-template-grid {
            display: grid;
            gap: 1rem;
        }

        .kb-template-card {
            overflow: hidden;
            border: 1px solid rgba(148,163,184,.22);
            border-radius: 24px;
            background: #ffffff;
        }

        .kb-template-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem;
            background: #f8fafc;
            border-bottom: 1px solid rgba(148,163,184,.18);
        }

        .kb-template-head strong {
            color: var(--kb-black);
            font-size: .95rem;
            font-weight: 1000;
        }

        .kb-template-head span {
            color: var(--kb-muted);
            font-size: .76rem;
            font-weight: 850;
        }

        .kb-template-card textarea {
            border: 0;
            border-radius: 0;
            min-height: 140px;
            box-shadow: none !important;
            transform: none !important;
        }

        .kb-token-list {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: .7rem;
        }

        .kb-token {
            padding: .34rem .55rem;
            border-radius: 999px;
            color: var(--kb-red-dark);
            background: rgba(255,31,45,.10);
            font-size: .75rem;
            font-weight: 900;
        }

        /* SIDE */
        .kb-side-stack {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .kb-preview-card {
            overflow: hidden;
            border: 1px solid var(--kb-line);
            border-radius: 32px;
            background: rgba(255,255,255,.90);
            box-shadow: var(--kb-shadow);
            backdrop-filter: blur(16px);
        }

        .kb-phone-frame {
            margin: 1.25rem;
            overflow: hidden;
            border-radius: 30px;
            border: 9px solid #0b1020;
            background: #e5ddd5;
            box-shadow: 0 24px 70px rgba(15,23,42,.22);
        }

        .kb-phone-top {
            padding: .85rem;
            display: flex;
            align-items: center;
            gap: .65rem;
            color: #ffffff;
            background: #075e54;
        }

        .kb-wa-avatar {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: rgba(255,255,255,.18);
        }

        .kb-phone-top strong {
            display: block;
            font-size: .86rem;
            font-weight: 950;
        }

        .kb-phone-top span {
            display: block;
            margin-top: .1rem;
            font-size: .70rem;
            opacity: .72;
        }

        .kb-chat-body {
            min-height: 310px;
            padding: 1rem;
            background:
                radial-gradient(circle at 20% 10%, rgba(255,255,255,.45), transparent 10rem),
                #e5ddd5;
        }

        .kb-chat-bubble {
            max-width: 88%;
            margin-left: auto;
            padding: .75rem .85rem;
            border-radius: 18px 18px 4px 18px;
            color: #111827;
            background: #dcf8c6;
            box-shadow: 0 6px 16px rgba(15,23,42,.08);
            font-size: .82rem;
            line-height: 1.55;
            white-space: pre-line;
        }

        .kb-chat-time {
            margin-top: .45rem;
            text-align: right;
            color: rgba(15,23,42,.48);
            font-size: .68rem;
        }

        .kb-info-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            padding: 1.25rem;
        }

        .kb-info-item {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr);
            gap: .75rem;
            align-items: center;
            padding: .85rem;
            border: 1px solid rgba(148,163,184,.20);
            border-radius: 20px;
            background: #ffffff;
        }

        .kb-info-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: rgba(255,31,45,.10);
            color: var(--kb-red);
        }

        .kb-info-item strong {
            display: block;
            color: var(--kb-black);
            font-size: .9rem;
            font-weight: 1000;
        }

        .kb-info-item span {
            display: block;
            margin-top: .1rem;
            color: var(--kb-muted);
            font-size: .78rem;
            line-height: 1.45;
        }

        .kb-footer-actions {
            position: sticky;
            bottom: 1rem;
            z-index: 10;
            display: flex;
            justify-content: flex-end;
            gap: .8rem;
            padding: 1rem;
            border: 1px solid var(--kb-line);
            border-radius: 26px;
            background: rgba(255,255,255,.88);
            box-shadow: 0 24px 70px rgba(15,23,42,.14);
            backdrop-filter: blur(18px);
        }

        @media (max-width: 1280px) {
            .kb-settings-hero,
            .kb-settings-grid {
                grid-template-columns: 1fr;
            }

            .kb-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .kb-settings-hero,
            .kb-panel,
            .kb-preview-card {
                border-radius: 26px;
            }

            .kb-title {
                font-size: 2.35rem;
            }

            .kb-stats,
            .kb-section-grid {
                grid-template-columns: 1fr;
            }

            .kb-panel-header {
                flex-direction: column;
            }

            .kb-footer-actions {
                flex-direction: column;
            }

            .kb-footer-actions .kb-btn {
                width: 100%;
            }
        }
    </style>

    <form wire:submit.prevent="save" class="kb-settings-page">
        {{-- HERO --}}
        <section class="kb-settings-hero">
            <div class="kb-hero-content">
                <div class="kb-kicker">
                    <span class="kb-kicker-dot"></span>
                    CRM Configuration Center
                </div>

                <h1 class="kb-title">
                    Konfigurasi Sistem
                    <span>loyalty, retention, dan WhatsApp.</span>
                </h1>

                <p class="kb-subtitle">
                    Atur syarat redeem, status promo, durasi retensi pelanggan, jadwal reminder,
                    hingga template pesan WhatsApp Gateway dalam satu halaman pengaturan premium.
                </p>

                <div class="kb-hero-actions">
                    <button type="submit" class="kb-btn kb-btn-primary">
                        💾 Simpan Perubahan
                    </button>

                    <a href="{{ \App\Filament\Admin\Pages\CrmDashboard::getUrl() }}" class="kb-btn kb-btn-glass">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

            <div class="kb-hero-side">
                <div class="kb-hero-card">
                    <div class="kb-hero-card-label">Master Promo</div>
                    <div class="kb-hero-card-value">
                        {{ $redeem_required_points }} Poin = {{ $reward_name ?: 'Reward' }}
                    </div>
                    <div class="kb-hero-card-note">
                        Aturan redeem yang sedang digunakan oleh kasir.
                    </div>
                </div>

                <div class="kb-hero-card light">
                    <div class="kb-hero-card-label">Automated Retention</div>
                    <div class="kb-hero-card-value">
                        {{ $retention_days }} Hari • {{ $retention_send_time }}
                    </div>
                    <div class="kb-hero-card-note">
                        Sistem mengingatkan pelanggan pasif sesuai jadwal ini.
                    </div>
                </div>
            </div>
        </section>

        {{-- STATS --}}
        <section class="kb-stats">
            <div class="kb-stat-card">
                <div class="kb-stat-icon">🎁</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Syarat Redeem</div>
                    <div class="kb-stat-value">{{ $redeem_required_points }} Poin</div>
                    <div class="kb-stat-note">{{ $reward_name }}</div>
                </div>
            </div>

            <div class="kb-stat-card">
                <div class="kb-stat-icon">🔁</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Retention Days</div>
                    <div class="kb-stat-value">{{ $retention_days }} Hari</div>
                    <div class="kb-stat-note">Batas pelanggan pasif</div>
                </div>
            </div>

            <div class="kb-stat-card">
                <div class="kb-stat-icon">⏰</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Jam Kirim</div>
                    <div class="kb-stat-value">{{ $retention_send_time }}</div>
                    <div class="kb-stat-note">Laravel Scheduler</div>
                </div>
            </div>

            <div class="kb-stat-card">
                <div class="kb-stat-icon">💬</div>
                <div class="kb-stat-body">
                    <div class="kb-label">Auto Send WA</div>
                    <div class="kb-stat-value">{{ $auto_send_whatsapp ? 'Aktif' : 'Nonaktif' }}</div>
                    <div class="kb-stat-note">Twilio WhatsApp Gateway</div>
                </div>
            </div>
        </section>

        <section class="kb-settings-grid">
            <main style="display:flex; flex-direction:column; gap:1rem;">
                {{-- MASTER PROMO --}}
                <section class="kb-panel">
                    <div class="kb-panel-header">
                        <div class="kb-panel-title-wrap">
                            <div class="kb-panel-icon">🎁</div>
                            <div>
                                <h2 class="kb-panel-title">Master Promo</h2>
                                <div class="kb-panel-subtitle">
                                    Kelola aturan dasar untuk redeem reward pelanggan.
                                </div>
                            </div>
                        </div>

                        <div class="kb-label">
                            Loyalty Rule
                        </div>
                    </div>

                    <div class="kb-panel-body">
                        <div class="kb-section-grid">
                            <div class="kb-field">
                                <label>Syarat Redeem Poin</label>
                                <div class="kb-input-row">
                                    <input
                                        type="number"
                                        min="1"
                                        max="100"
                                        wire:model.live="redeem_required_points"
                                        class="kb-input"
                                    >
                                    <span class="kb-unit">Poin</span>
                                </div>
                                <div class="kb-field-help">
                                    Jumlah poin minimum yang harus dikumpulkan pelanggan.
                                </div>
                                @error('redeem_required_points')
                                    <div class="kb-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="kb-field">
                                <label>Nama Reward</label>
                                <input
                                    type="text"
                                    wire:model.live="reward_name"
                                    class="kb-input"
                                    placeholder="Contoh: 1 Kopi Gratis"
                                >
                                <div class="kb-field-help">
                                    Nama hadiah yang diberikan saat pelanggan redeem.
                                </div>
                                @error('reward_name')
                                    <div class="kb-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="kb-field full">
                                <div class="kb-switch-card">
                                    <div>
                                        <div class="kb-switch-title">Status Promo</div>
                                        <div class="kb-switch-desc">
                                            Aktifkan atau nonaktifkan program redeem point.
                                        </div>
                                    </div>

                                    <label class="kb-toggle">
                                        <input type="checkbox" wire:model.live="promo_is_active">
                                        <span class="kb-toggle-slider"></span>
                                    </label>
                                </div>
                                @error('promo_is_active')
                                    <div class="kb-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>

                {{-- RETENTION --}}
                <section class="kb-panel">
                    <div class="kb-panel-header">
                        <div class="kb-panel-title-wrap">
                            <div class="kb-panel-icon">🔁</div>
                            <div>
                                <h2 class="kb-panel-title">Automated Retention</h2>
                                <div class="kb-panel-subtitle">
                                    Atur pengingat otomatis untuk pelanggan yang tidak berkunjung.
                                </div>
                            </div>
                        </div>

                        <div class="kb-label">
                            Customer Retention
                        </div>
                    </div>

                    <div class="kb-panel-body">
                        <div class="kb-section-grid">
                            <div class="kb-field">
                                <label>Durasi Pengingat</label>
                                <div class="kb-input-row">
                                    <input
                                        type="number"
                                        min="1"
                                        max="365"
                                        wire:model.live="retention_days"
                                        class="kb-input"
                                    >
                                    <span class="kb-unit">Hari</span>
                                </div>
                                <div class="kb-field-help">
                                    Sistem mendeteksi member yang tidak berkunjung dalam kurun waktu ini.
                                </div>
                                @error('retention_days')
                                    <div class="kb-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="kb-field">
                                <label>Jam Kirim Retensi</label>
                                <input
                                    type="time"
                                    wire:model.live="retention_send_time"
                                    class="kb-input"
                                >
                                <div class="kb-field-help">
                                    Dipakai untuk schedule harian di Laravel Scheduler.
                                </div>
                                @error('retention_send_time')
                                    <div class="kb-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="kb-field full">
                                <div class="kb-switch-card">
                                    <div>
                                        <div class="kb-switch-title">Auto-Send WhatsApp</div>
                                        <div class="kb-switch-desc">
                                            Aktifkan pengiriman otomatis melalui Twilio WhatsApp Gateway.
                                        </div>
                                    </div>

                                    <label class="kb-toggle">
                                        <input type="checkbox" wire:model.live="auto_send_whatsapp">
                                        <span class="kb-toggle-slider"></span>
                                    </label>
                                </div>
                                @error('auto_send_whatsapp')
                                    <div class="kb-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>

                {{-- TEMPLATES --}}
                <section class="kb-panel">
                    <div class="kb-panel-header">
                        <div class="kb-panel-title-wrap">
                            <div class="kb-panel-icon">💬</div>
                            <div>
                                <h2 class="kb-panel-title">Template Pesan WhatsApp</h2>
                                <div class="kb-panel-subtitle">
                                    Sesuaikan gaya pesan untuk poin, redeem, dan retensi.
                                </div>
                            </div>
                        </div>

                        <div class="kb-label">
                            Message Template
                        </div>
                    </div>

                    <div class="kb-panel-body">
                        <div class="kb-template-grid">
                            <div class="kb-template-card">
                                <div class="kb-template-head">
                                    <strong>Template Tambah Poin</strong>
                                    <span>Point Notification</span>
                                </div>
                                <textarea
                                    wire:model.defer="point_message_template"
                                    class="kb-textarea"
                                    placeholder="Terima kasih Kak {name}. Poin Kakak bertambah {points}. Total poin sekarang {total_points}."
                                ></textarea>
                            </div>

                            <div class="kb-template-card">
                                <div class="kb-template-head">
                                    <strong>Template Redeem</strong>
                                    <span>Reward Notification</span>
                                </div>
                                <textarea
                                    wire:model.defer="redeem_message_template"
                                    class="kb-textarea"
                                    placeholder="Selamat Kak {name}, redeem {reward_name} berhasil. Sisa poin Kakak {total_points}."
                                ></textarea>
                            </div>

                            <div class="kb-template-card">
                                <div class="kb-template-head">
                                    <strong>Template Retensi</strong>
                                    <span>We Miss You Message</span>
                                </div>
                                <textarea
                                    wire:model.defer="retention_message_template"
                                    class="kb-textarea"
                                    placeholder="Halo Kak {name}, sudah lama belum mampir ke Kopi Banget. Poin Kakak masih ada {total_points}."
                                ></textarea>
                            </div>
                        </div>

                        <div class="kb-token-list">
                            <span class="kb-token">{name}</span>
                            <span class="kb-token">{points}</span>
                            <span class="kb-token">{total_points}</span>
                            <span class="kb-token">{reward_name}</span>
                            <span class="kb-token">{business_name}</span>
                        </div>
                    </div>
                </section>
            </main>

            <aside class="kb-side-stack">
                {{-- PHONE PREVIEW --}}
                <section class="kb-preview-card">
                    <div class="kb-panel-header">
                        <div class="kb-panel-title-wrap">
                            <div class="kb-panel-icon">📲</div>
                            <div>
                                <h2 class="kb-panel-title">Preview WhatsApp</h2>
                                <div class="kb-panel-subtitle">
                                    Tampilan perkiraan pesan yang diterima pelanggan.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="kb-phone-frame">
                        <div class="kb-phone-top">
                            <div class="kb-wa-avatar">☕</div>
                            <div>
                                <strong>Kopi Banget CRM</strong>
                                <span>online via WhatsApp Gateway</span>
                            </div>
                        </div>

                        <div class="kb-chat-body">
                            <div class="kb-chat-bubble">
                                {{ $point_message_template ?: 'Terima kasih Kak {name}. Poin Kakak bertambah {points}. Total poin sekarang {total_points}.' }}
                                <div class="kb-chat-time">{{ now()->format('H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- INFO --}}
                <section class="kb-preview-card">
                    <div class="kb-info-list">
                        <div class="kb-info-item">
                            <div class="kb-info-icon">🛡</div>
                            <div>
                                <strong>Credential Aman</strong>
                                <span>Twilio Account SID dan Auth Token tetap disimpan di file .env.</span>
                            </div>
                        </div>

                        <div class="kb-info-item">
                            <div class="kb-info-icon">⏱</div>
                            <div>
                                <strong>Scheduler Harian</strong>
                                <span>Retention dapat dijalankan otomatis lewat Laravel Scheduler.</span>
                            </div>
                        </div>

                        <div class="kb-info-item">
                            <div class="kb-info-icon">📜</div>
                            <div>
                                <strong>Log Tersimpan</strong>
                                <span>Setiap pengiriman WhatsApp dicatat pada tabel whatsapp_logs.</span>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </section>

        {{-- STICKY SAVE --}}
        <div class="kb-footer-actions">
            <a href="{{ \App\Filament\Admin\Pages\CrmDashboard::getUrl() }}" class="kb-btn kb-btn-light">
                Batal
            </a>

            <button type="submit" class="kb-btn kb-btn-primary">
                💾 Simpan Perubahan
            </button>
        </div>
    </form>
</x-filament-panels::page>
