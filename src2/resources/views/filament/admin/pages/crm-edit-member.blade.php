<x-filament-panels::page>
    @php
        $member = $this->getMemberRecord();
        $memberInitial = $name !== '' ? strtoupper(substr($name, 0, 1)) : 'K';

        $statusLabels = [
            \App\Models\Member::STATUS_ACTIVE => 'Active Member',
            \App\Models\Member::STATUS_INACTIVE => 'Inactive Member',
            \App\Models\Member::STATUS_BLOCKED => 'Blocked Member',
        ];
    @endphp

    <style>
        :root {
            --kb-red: #ff1f2d;
            --kb-red-2: #ff4b55;
            --kb-red-dark: #c80f1b;
            --kb-black: #0b1020;
            --kb-slate: #334155;
            --kb-muted: #64748b;
            --kb-soft: #f8fafc;
            --kb-line: rgba(148, 163, 184, .22);
            --kb-shadow-soft: 0 24px 80px rgba(15, 23, 42, .10);
            --kb-shadow-red: 0 24px 48px rgba(255, 31, 45, .25);
        }

        .fi-main {
            background:
                radial-gradient(circle at top right, rgba(255, 31, 45, .10), transparent 34rem),
                radial-gradient(circle at 12% 22%, rgba(15, 23, 42, .06), transparent 32rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 42%, #f1f5f9 100%) !important;
        }

        .kb-edit-page,
        .kb-edit-page * {
            box-sizing: border-box;
        }

        .kb-edit-page {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(420px, 1.1fr);
            gap: 1.25rem;
            width: 100%;
            padding-bottom: 2rem;
        }

        .kb-panel {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--kb-line);
            border-radius: 34px;
            background: rgba(255, 255, 255, .88);
            box-shadow: var(--kb-shadow-soft);
        }

        .kb-hero {
            min-height: 620px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(1.4rem, 2vw, 2.1rem);
            color: #fff;
            background:
                radial-gradient(circle at 12% 0%, rgba(255,255,255,.20), transparent 20rem),
                radial-gradient(circle at 90% 10%, rgba(255,31,45,.55), transparent 20rem),
                linear-gradient(135deg, #0b1020 0%, #111827 48%, #1f2937 74%, #e11d2e 135%);
        }

        .kb-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 38px 38px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,1), rgba(0,0,0,.28));
            pointer-events: none;
        }

        .kb-hero > * {
            position: relative;
            z-index: 2;
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
            color: rgba(255,255,255,.78);
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
            margin: 1rem 0 0;
            font-size: clamp(2.4rem, 4vw, 4.2rem);
            line-height: .98;
            font-weight: 1000;
            letter-spacing: -.075em;
        }

        .kb-title span {
            display: block;
            margin-top: .22rem;
            color: rgba(255,255,255,.60);
        }

        .kb-subtitle {
            max-width: 620px;
            margin: 1rem 0 0;
            color: rgba(255,255,255,.78);
            font-size: 1rem;
            line-height: 1.75;
        }

        .kb-mini-stack {
            display: grid;
            gap: .75rem;
            margin-top: 1.4rem;
        }

        .kb-mini-card {
            display: flex;
            gap: .85rem;
            padding: 1rem;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 22px;
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(16px);
        }

        .kb-mini-icon {
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: rgba(255,255,255,.16);
            font-weight: 1000;
        }

        .kb-mini-card strong {
            display: block;
            font-weight: 1000;
        }

        .kb-mini-card span {
            display: block;
            margin-top: .2rem;
            color: rgba(255,255,255,.68);
            font-size: .86rem;
            line-height: 1.5;
        }

        .kb-preview {
            display: grid;
            grid-template-columns: 76px minmax(0, 1fr);
            align-items: center;
            gap: 1rem;
            padding: 1.1rem;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 26px;
            background: rgba(255,255,255,.92);
            color: var(--kb-black);
        }

        .kb-preview-avatar {
            width: 76px;
            height: 76px;
            display: grid;
            place-items: center;
            border-radius: 24px;
            background: linear-gradient(135deg, #0b1020, #334155);
            color: #fff;
            font-size: 1.45rem;
            font-weight: 1000;
        }

        .kb-preview small {
            display: block;
            color: #94a3b8;
            font-size: .68rem;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .kb-preview strong {
            display: block;
            margin-top: .15rem;
            overflow: hidden;
            color: var(--kb-black);
            font-size: 1.25rem;
            font-weight: 1000;
            letter-spacing: -.04em;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kb-preview span {
            display: block;
            margin-top: .15rem;
            overflow: hidden;
            color: var(--kb-muted);
            font-size: .9rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kb-form-card {
            background:
                radial-gradient(circle at top right, rgba(255,31,45,.08), transparent 20rem),
                rgba(255,255,255,.92);
        }

        .kb-form-header {
            padding: 1.8rem 2rem 1.2rem;
            border-bottom: 1px solid var(--kb-line);
            text-align: center;
        }

        .kb-form-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto;
            display: grid;
            place-items: center;
            border-radius: 20px;
            background: rgba(255,31,45,.10);
            color: var(--kb-red);
            font-size: 1.35rem;
        }

        .kb-form-title {
            margin: 1rem 0 0;
            color: var(--kb-black);
            font-size: 1.6rem;
            font-weight: 1000;
            letter-spacing: -.045em;
        }

        .kb-form-subtitle {
            max-width: 490px;
            margin: .45rem auto 0;
            color: var(--kb-muted);
            line-height: 1.65;
            font-size: .94rem;
        }

        .kb-form-body {
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
        .kb-select,
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

        .kb-input,
        .kb-select {
            min-height: 54px;
            padding: 0 1rem 0 2.75rem;
        }

        .kb-input[type="date"] {
            padding-left: 1rem;
        }

        .kb-select {
            appearance: none;
            cursor: pointer;
        }

        .kb-textarea {
            min-height: 120px;
            resize: vertical;
            padding: .95rem 1rem .95rem 2.75rem;
        }

        .kb-input:focus,
        .kb-select:focus,
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
            font-size: .94rem;
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

        .kb-btn-soft {
            color: var(--kb-black) !important;
            border: 1px solid rgba(148, 163, 184, .24);
            background: #ffffff;
            box-shadow: 0 14px 30px rgba(15,23,42,.08);
        }

        .kb-note-card {
            margin-top: 1.1rem;
            padding: 1rem;
            border: 1px solid rgba(148,163,184,.20);
            border-radius: 20px;
            background: #f8fafc;
            color: var(--kb-muted);
            font-size: .83rem;
            line-height: 1.65;
        }

        .kb-note-card strong {
            color: var(--kb-black);
        }

        @media (max-width: 1180px) {
            .kb-edit-page {
                grid-template-columns: 1fr;
            }

            .kb-hero {
                min-height: auto;
            }
        }

        @media (max-width: 720px) {
            .kb-edit-page {
                gap: 1rem;
            }

            .kb-panel {
                border-radius: 26px;
            }

            .kb-form-header,
            .kb-form-body {
                padding-left: 1.2rem;
                padding-right: 1.2rem;
            }

            .kb-actions {
                grid-template-columns: 1fr;
            }

            .kb-preview {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .kb-preview-avatar {
                margin: 0 auto;
            }
        }
    </style>

    <div class="kb-edit-page">
        <section class="kb-panel kb-hero">
            <div>
                <div class="kb-kicker">
                    <span class="kb-kicker-dot"></span>
                    Edit Loyalty Member
                </div>

                <h1 class="kb-title">
                    Update Data Member
                    <span>tanpa mengubah poin loyalty.</span>
                </h1>

                <p class="kb-subtitle">
                    Perbarui nama, nomor WhatsApp, tanggal lahir, status, dan catatan member.
                    Total poin, history transaksi, dan riwayat redeem tetap aman karena data yang diedit hanya profil member.
                </p>

                <div class="kb-mini-stack">
                    <div class="kb-mini-card">
                        <div class="kb-mini-icon">1</div>
                        <div>
                            <strong>Nomor WhatsApp dinormalisasi</strong>
                            <span>Format nomor akan dirapikan otomatis agar tetap cocok dengan sistem CRM.</span>
                        </div>
                    </div>

                    <div class="kb-mini-card">
                        <div class="kb-mini-icon">2</div>
                        <div>
                            <strong>Anti nomor duplikat</strong>
                            <span>Jika nomor sudah dipakai member lain, sistem akan menolak perubahan.</span>
                        </div>
                    </div>

                    <div class="kb-mini-card">
                        <div class="kb-mini-icon">3</div>
                        <div>
                            <strong>Kembali ke dashboard</strong>
                            <span>Setelah update sukses, kamu langsung diarahkan ke profil member di Dashboard CRM.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kb-preview">
                <div class="kb-preview-avatar">{{ $memberInitial }}</div>
                <div>
                    <small>Preview Member</small>
                    <strong>{{ $name !== '' ? $name : 'Nama Member' }}</strong>
                    <span>{{ $phone !== '' ? $phone : 'Nomor WhatsApp' }}</span>
                    <span>{{ $member?->member_code ?? 'Kode member otomatis' }} • {{ $statusLabels[$status] ?? 'Active Member' }}</span>
                </div>
            </div>
        </section>

        <section class="kb-panel kb-form-card">
            <div class="kb-form-header">
                <div class="kb-form-icon">✏️</div>
                <h2 class="kb-form-title">Form Edit Member</h2>
                <p class="kb-form-subtitle">
                    Pastikan data member sudah benar sebelum disimpan. Bagian ini hanya mengubah data profil member.
                </p>
            </div>

            <div class="kb-form-body">
                <form wire:submit.prevent="save" class="kb-form-grid">
                    <div class="kb-field">
                        <label>
                            Nama Member
                            <span>wajib</span>
                        </label>
                        <div class="kb-input-wrap">
                            <span class="kb-input-icon">👤</span>
                            <input
                                type="text"
                                wire:model.defer="name"
                                class="kb-input"
                                placeholder="Contoh: Gusti"
                                autocomplete="off"
                            >
                        </div>
                        @error('name')
                            <div class="kb-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="kb-field">
                        <label>
                            Nomor WhatsApp
                            <span>wajib</span>
                        </label>
                        <div class="kb-input-wrap">
                            <span class="kb-input-icon">☎</span>
                            <input
                                type="text"
                                wire:model.defer="phone"
                                class="kb-input"
                                placeholder="Contoh: +6281234567890"
                                autocomplete="off"
                            >
                        </div>
                        @error('phone')
                            <div class="kb-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="kb-field">
                        <label>
                            Tanggal Lahir
                            <span>opsional</span>
                        </label>
                        <input
                            type="date"
                            wire:model.defer="birth_date"
                            class="kb-input"
                        >
                        @error('birth_date')
                            <div class="kb-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="kb-field">
                        <label>
                            Status Member
                            <span>wajib</span>
                        </label>
                        <div class="kb-input-wrap">
                            <span class="kb-input-icon">●</span>
                            <select wire:model.defer="status" class="kb-select">
                                <option value="{{ \App\Models\Member::STATUS_ACTIVE }}">Active Member</option>
                                <option value="{{ \App\Models\Member::STATUS_INACTIVE }}">Inactive Member</option>
                                <option value="{{ \App\Models\Member::STATUS_BLOCKED }}">Blocked Member</option>
                            </select>
                        </div>
                        @error('status')
                            <div class="kb-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="kb-field">
                        <label>
                            Catatan Member
                            <span>opsional</span>
                        </label>
                        <div class="kb-input-wrap">
                            <span class="kb-input-icon kb-textarea-icon">📝</span>
                            <textarea
                                wire:model.defer="notes"
                                class="kb-textarea"
                                placeholder="Contoh: Suka kopi susu less sugar, sering datang sore hari."
                            ></textarea>
                        </div>
                        @error('notes')
                            <div class="kb-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="kb-actions">
                        <a
                            href="{{ \App\Filament\Admin\Pages\CrmDashboard::getUrl(['phone' => $phone]) }}"
                            class="kb-btn kb-btn-soft"
                        >
                            Batal
                        </a>

                        <button type="submit" class="kb-btn kb-btn-primary">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

                <div class="kb-note-card">
                    <strong>Catatan:</strong> fitur edit ini tidak mengubah total poin member. Untuk menambah poin atau redeem reward,
                    tetap gunakan Dashboard CRM agar semua aktivitas masuk ke history transaksi.
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
