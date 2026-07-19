<x-filament-panels::page>
    <style>
        /* ==========================================================
           KOPI BANGET CRM - LUXURY ADD MEMBER PAGE
           File: resources/views/filament/admin/pages/crm-add-member.blade.php
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

        .kb-add-page {
            width: 100%;
            min-height: 76vh;
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(420px, 560px);
            gap: 1.25rem;
            align-items: stretch;
        }

        .kb-add-page * {
            box-sizing: border-box;
        }

        /* LEFT HERO */
        .kb-add-hero {
            position: relative;
            overflow: hidden;
            min-height: 720px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(1.35rem, 2vw, 2.15rem);
            border-radius: 34px;
            color: #ffffff;
            background:
                radial-gradient(circle at 12% 0%, rgba(255, 255, 255, .22), transparent 22rem),
                radial-gradient(circle at 90% 16%, rgba(255, 31, 45, .56), transparent 24rem),
                linear-gradient(135deg, #0b1020 0%, #111827 48%, #1f2937 76%, #e11d2e 138%);
            box-shadow:
                0 28px 90px rgba(15, 23, 42, .22),
                inset 0 1px 0 rgba(255, 255, 255, .10);
        }

        .kb-add-hero::before {
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

        .kb-add-hero::after {
            content: "";
            position: absolute;
            width: 540px;
            height: 540px;
            right: -210px;
            bottom: -260px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255,255,255,.20), rgba(255,31,45,.32), transparent 68%);
            pointer-events: none;
        }

        .kb-hero-top,
        .kb-hero-bottom {
            position: relative;
            z-index: 2;
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
            max-width: 780px;
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
            max-width: 720px;
            margin: 1rem 0 0;
            color: rgba(255,255,255,.78);
            font-size: 1rem;
            line-height: 1.75;
        }

        .kb-hero-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .85rem;
            margin-top: 1.35rem;
        }

        .kb-hero-metric {
            padding: 1rem;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 24px;
            background: rgba(255,255,255,.12);
            box-shadow: 0 18px 45px rgba(0,0,0,.16);
            backdrop-filter: blur(18px);
        }

        .kb-hero-metric strong {
            display: block;
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 1000;
            letter-spacing: -.035em;
        }

        .kb-hero-metric span {
            display: block;
            margin-top: .25rem;
            color: rgba(255,255,255,.64);
            font-size: .8rem;
            line-height: 1.5;
        }

        .kb-flow-card {
            position: relative;
            z-index: 2;
            overflow: hidden;
            padding: 1rem;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 26px;
            background: rgba(255,255,255,.12);
            box-shadow: 0 20px 55px rgba(0,0,0,.17);
            backdrop-filter: blur(18px);
        }

        .kb-flow-card-title {
            color: rgba(255,255,255,.70);
            font-size: .72rem;
            font-weight: 950;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .kb-flow-list {
            display: grid;
            gap: .8rem;
            margin-top: .9rem;
        }

        .kb-flow-item {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: .75rem;
            align-items: center;
        }

        .kb-flow-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            background: rgba(255,255,255,.92);
            color: var(--kb-red);
            font-weight: 1000;
        }

        .kb-flow-text strong {
            display: block;
            color: #ffffff;
            font-size: .9rem;
            font-weight: 950;
        }

        .kb-flow-text span {
            display: block;
            margin-top: .1rem;
            color: rgba(255,255,255,.58);
            font-size: .78rem;
            line-height: 1.45;
        }

        /* RIGHT FORM */
        .kb-form-shell {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .kb-form-card {
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 560px;
            border: 1px solid var(--kb-line);
            border-radius: 34px;
            background: rgba(255,255,255,.90);
            box-shadow: var(--kb-shadow);
            backdrop-filter: blur(16px);
        }

        .kb-form-card::before {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            right: -140px;
            top: -140px;
            border-radius: 999px;
            background: rgba(255,31,45,.10);
            pointer-events: none;
        }

        .kb-form-header {
            position: relative;
            z-index: 2;
            padding: 2rem 2rem 1.35rem;
            text-align: center;
            border-bottom: 1px solid var(--kb-line);
            background:
                radial-gradient(circle at top, rgba(255,31,45,.08), transparent 18rem),
                linear-gradient(180deg, rgba(248,250,252,.94), rgba(255,255,255,.76));
        }

        .kb-icon-orbit {
            position: relative;
            width: 76px;
            height: 76px;
            display: grid;
            place-items: center;
            margin: 0 auto;
            border-radius: 999px;
            background:
                linear-gradient(#fff, #fff) padding-box,
                conic-gradient(from 160deg, rgba(255,31,45,.10), rgba(15,23,42,.18), rgba(255,31,45,.48), rgba(255,31,45,.10)) border-box;
            border: 6px solid transparent;
            box-shadow: 0 18px 42px rgba(15,23,42,.10);
        }

        .kb-icon-orbit::after {
            content: "";
            position: absolute;
            right: 5px;
            bottom: 7px;
            width: 14px;
            height: 14px;
            border-radius: 999px;
            border: 3px solid #fff;
            background: #22c55e;
        }

        .kb-icon-core {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            background: rgba(255,31,45,.10);
            color: var(--kb-red);
            font-size: 1.2rem;
        }

        .kb-form-title {
            margin: 1rem 0 0;
            color: var(--kb-black);
            font-size: 1.55rem;
            font-weight: 1000;
            letter-spacing: -.045em;
        }

        .kb-form-subtitle {
            margin: .45rem auto 0;
            max-width: 420px;
            color: var(--kb-muted);
            line-height: 1.65;
            font-size: .94rem;
        }

        .kb-form-body {
            position: relative;
            z-index: 2;
            padding: 1.6rem 2rem 2rem;
        }

        .kb-form-grid {
            display: grid;
            gap: 1rem;
        }

        .kb-field label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin-bottom: .42rem;
            color: var(--kb-black);
            font-size: .84rem;
            font-weight: 950;
        }

        .kb-field label span {
            color: var(--kb-muted);
            font-weight: 800;
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

        .kb-textarea-icon {
            top: 1rem;
            transform: none;
        }

        .kb-input,
        .kb-textarea {
            width: 100%;
            border: 1px solid rgba(148, 163, 184, .35);
            border-radius: 18px;
            outline: none;
            background: rgba(255,255,255,.94);
            color: var(--kb-black);
            font-size: .95rem;
            transition: border .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .kb-input {
            min-height: 54px;
            padding: 0 1rem 0 2.75rem;
        }

        .kb-input[type="date"] {
            padding-left: 1rem;
        }

        .kb-textarea {
            min-height: 110px;
            resize: vertical;
            padding: .95rem 1rem .95rem 2.75rem;
        }

        .kb-input:focus,
        .kb-textarea:focus {
            border-color: rgba(255,31,45,.55);
            box-shadow: 0 0 0 4px rgba(255,31,45,.09);
            transform: translateY(-1px);
        }

        .kb-input::placeholder,
        .kb-textarea::placeholder {
            color: #94a3b8;
        }

        .kb-error {
            margin-top: .35rem;
            color: var(--kb-red-dark);
            font-size: .78rem;
            font-weight: 800;
        }

        .kb-actions {
            display: grid;
            grid-template-columns: 1fr 1.35fr;
            gap: .8rem;
            margin-top: 1.25rem;
        }

        .kb-btn {
            min-height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .52rem;
            padding: 0 1.1rem;
            border: 0;
            border-radius: 18px;
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

        .kb-btn-secondary {
            color: var(--kb-slate) !important;
            border: 1px solid rgba(148, 163, 184, .32);
            background: #ffffff;
            box-shadow: 0 12px 28px rgba(15,23,42,.05);
        }

        .kb-btn-primary {
            color: #ffffff !important;
            background:
                radial-gradient(circle at 18% 0%, rgba(255,255,255,.28), transparent 28%),
                linear-gradient(135deg, var(--kb-red), var(--kb-red-2));
            box-shadow: var(--kb-red-shadow);
        }

        .kb-form-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
            margin-top: 1.15rem;
            padding: .85rem 1rem;
            border: 1px solid rgba(148,163,184,.20);
            border-radius: 18px;
            background: #f8fafc;
            color: var(--kb-muted);
            font-size: .78rem;
        }

        .kb-form-footer strong {
            color: var(--kb-black);
        }

        .kb-preview-card {
            margin-top: 1rem;
            padding: 1rem;
            border: 1px solid rgba(255,31,45,.12);
            border-radius: 22px;
            background:
                radial-gradient(circle at top right, rgba(255,31,45,.07), transparent 16rem),
                #ffffff;
        }

        .kb-preview-title {
            color: #94a3b8;
            font-size: .70rem;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .kb-preview-body {
            display: grid;
            grid-template-columns: 50px minmax(0, 1fr);
            align-items: center;
            gap: .8rem;
            margin-top: .75rem;
        }

        .kb-preview-avatar {
            width: 50px;
            height: 50px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--kb-black), #475569);
            color: #fff;
            font-weight: 1000;
        }

        .kb-preview-body strong {
            display: block;
            color: var(--kb-black);
            font-weight: 1000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kb-preview-body span {
            display: block;
            margin-top: .15rem;
            color: var(--kb-muted);
            font-size: .82rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 1180px) {
            .kb-add-page {
                grid-template-columns: 1fr;
            }

            .kb-add-hero {
                min-height: auto;
            }
        }

        @media (max-width: 720px) {
            .kb-add-page {
                gap: 1rem;
            }

            .kb-add-hero,
            .kb-form-card {
                border-radius: 26px;
            }

            .kb-title {
                font-size: 2.35rem;
            }

            .kb-hero-metrics {
                grid-template-columns: 1fr;
            }

            .kb-form-header,
            .kb-form-body {
                padding-left: 1.2rem;
                padding-right: 1.2rem;
            }

            .kb-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="kb-add-page">
        <section class="kb-add-hero">
            <div class="kb-hero-top">
                <div class="kb-kicker">
                    <span class="kb-kicker-dot"></span>
                    Kopi Banget Membership
                </div>

                <h1 class="kb-title">
                    Tambah Member Baru
                    <span>dengan pengalaman kasir premium.</span>
                </h1>

                <p class="kb-subtitle">
                    Daftarkan pelanggan menggunakan nomor WhatsApp sebagai identitas utama.
                    Setelah tersimpan, member dapat langsung menerima poin, redeem reward,
                    dan notifikasi otomatis dari CRM.
                </p>

                <div class="kb-hero-metrics">
                    <div class="kb-hero-metric">
                        <strong>WhatsApp Identity</strong>
                        <span>Nomor pelanggan dipakai sebagai kunci pencarian member.</span>
                    </div>

                    <div class="kb-hero-metric">
                        <strong>Ready for Loyalty</strong>
                        <span>Member baru otomatis siap menerima poin pembelian.</span>
                    </div>
                </div>
            </div>

            <div class="kb-hero-bottom">
                <div class="kb-flow-card">
                    <div class="kb-flow-card-title">Alur setelah member dibuat</div>

                    <div class="kb-flow-list">
                        <div class="kb-flow-item">
                            <div class="kb-flow-icon">1</div>
                            <div class="kb-flow-text">
                                <strong>Data member tersimpan</strong>
                                <span>Nama dan nomor WhatsApp masuk ke database CRM.</span>
                            </div>
                        </div>

                        <div class="kb-flow-item">
                            <div class="kb-flow-icon">2</div>
                            <div class="kb-flow-text">
                                <strong>Kasir cari member</strong>
                                <span>Dashboard menampilkan profil, total poin, dan status reward.</span>
                            </div>
                        </div>

                        <div class="kb-flow-item">
                            <div class="kb-flow-icon">3</div>
                            <div class="kb-flow-text">
                                <strong>Tambah poin dan kirim WA</strong>
                                <span>Transaksi poin tercatat dan notifikasi WhatsApp dapat dikirim otomatis.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="kb-form-shell">
            <div class="kb-form-card">
                <div class="kb-form-header">
                    <div class="kb-icon-orbit">
                        <div class="kb-icon-core">👥</div>
                    </div>

                    <h2 class="kb-form-title">Form Pendaftaran Member</h2>
                    <p class="kb-form-subtitle">
                        Lengkapi data pelanggan untuk mengaktifkan membership Kopi Banget.
                    </p>
                </div>

                <div class="kb-form-body">
                    <form wire:submit.prevent="save" class="kb-form-grid">
                        <div class="kb-field">
                            <label for="name">Nama Lengkap</label>
                            <div class="kb-input-wrap">
                                <span class="kb-input-icon">👤</span>
                                <input
                                    id="name"
                                    type="text"
                                    wire:model.defer="name"
                                    class="kb-input"
                                    placeholder="Masukkan nama lengkap"
                                    autocomplete="off"
                                >
                            </div>
                            @error('name')
                                <div class="kb-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="kb-field">
                            <label for="phone">Nomor WhatsApp</label>
                            <div class="kb-input-wrap">
                                <span class="kb-input-icon">📱</span>
                                <input
                                    id="phone"
                                    type="text"
                                    wire:model.defer="phone"
                                    class="kb-input"
                                    placeholder="Contoh: +6281287968048"
                                    autocomplete="off"
                                >
                            </div>
                            @error('phone')
                                <div class="kb-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="kb-field">
                            <label for="birth_date">
                                Tanggal Lahir
                                <span>opsional</span>
                            </label>
                            <input
                                id="birth_date"
                                type="date"
                                wire:model.defer="birth_date"
                                class="kb-input"
                            >
                            @error('birth_date')
                                <div class="kb-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="kb-field">
                            <label for="notes">
                                Catatan
                                <span>opsional</span>
                            </label>
                            <div class="kb-input-wrap">
                                <span class="kb-input-icon kb-textarea-icon">☕</span>
                                <textarea
                                    id="notes"
                                    wire:model.defer="notes"
                                    class="kb-textarea"
                                    placeholder="Contoh: pelanggan sering pesan kopi susu"
                                ></textarea>
                            </div>
                            @error('notes')
                                <div class="kb-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="kb-preview-card">
                            <div class="kb-preview-title">Preview Membership</div>
                            <div class="kb-preview-body">
                                <div class="kb-preview-avatar">
                                    {{ $name ? strtoupper(substr($name, 0, 1)) : 'K' }}
                                </div>

                                <div>
                                    <strong>{{ $name ?: 'Nama Member' }}</strong>
                                    <span>{{ $phone ?: 'Nomor WhatsApp akan tampil di sini' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="kb-actions">
                            <a href="{{ \App\Filament\Admin\Pages\CrmDashboard::getUrl() }}" class="kb-btn kb-btn-secondary">
                                Batal
                            </a>

                            <button type="submit" class="kb-btn kb-btn-primary">
                                💾 Simpan Member
                            </button>
                        </div>
                    </form>

                    <div class="kb-form-footer">
                        <span>Data member akan tersimpan otomatis ke CRM.</span>
                        <strong>0 Poin Awal</strong>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
