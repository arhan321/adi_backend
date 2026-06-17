<x-filament-panels::page>
    @once
        <link rel="stylesheet" href="{{ asset('css/crm-kopi-banget.css') }}">
    @endonce

    @php
        $stats = $this->getStats();
        $member = $this->getSelectedMember();
        $setting = $this->getSetting();
        $canRedeem = $member && $setting->promo_is_active && $member->total_points >= $setting->redeem_required_points;
    @endphp

    <div class="kb-page">
        <section class="kb-stats">
            <div class="kb-stat-card">
                <span class="kb-stat-icon">↻</span>
                <div>
                    <small>Automated Retention</small>
                    <strong>Retention: {{ $stats['retention_days'] }} Days</strong>
                </div>
            </div>

            <div class="kb-stat-card">
                <span class="kb-stat-icon">🎁</span>
                <div>
                    <small>Master Promo</small>
                    <strong>{{ $stats['redeem_rule'] }}</strong>
                </div>
            </div>

            <div class="kb-stat-card">
                <span class="kb-stat-icon">👥</span>
                <div>
                    <small>Total Member</small>
                    <strong>{{ number_format($stats['total_member']) }} Members</strong>
                </div>
            </div>

            <div class="kb-stat-card">
                <span class="kb-stat-icon">☕</span>
                <div>
                    <small>Aktivitas Hari Ini</small>
                    <strong>{{ number_format($stats['today_activity']) }} Transaksi</strong>
                </div>
            </div>
        </section>

        <section class="kb-dashboard-grid">
            <div class="kb-panel kb-search-panel">
                <form wire:submit.prevent="searchMember" class="kb-search-form">
                    <div class="kb-input-with-icon">
                        <span>🔎</span>
                        <input type="text" wire:model.defer="searchPhone" placeholder="Masukkan nomor WhatsApp member, contoh: 081234567890">
                    </div>
                    <button type="submit" class="kb-btn kb-btn-dark">Cari</button>
                </form>

                @error('searchPhone')
                    <p class="kb-error">{{ $message }}</p>
                @enderror

                @if (! $member)
                    <div class="kb-empty-state">
                        <div class="kb-avatar kb-avatar-empty">👤</div>
                        <h3>Member belum dipilih</h3>
                        <p>Masukkan nomor WhatsApp pelanggan. Jika nomor belum ada, daftarkan member baru terlebih dahulu.</p>
                        <button type="button" wire:click="goToAddMember" class="kb-btn kb-btn-primary">+ Tambah Member Baru</button>
                    </div>
                @else
                    <div class="kb-member-card">
                        <div class="kb-member-left">
                            <div class="kb-avatar">{{ mb_substr($member->name, 0, 1) }}</div>
                            <h2>{{ $member->name }}</h2>
                            <p>Customer ID: {{ $member->member_code ?? 'KB-' . str_pad($member->id, 5, '0', STR_PAD_LEFT) }}</p>
                            <span class="kb-badge kb-badge-success">● Active Member</span>

                            <div class="kb-member-meta">
                                <div><span>No. WhatsApp</span><strong>{{ $member->phone }}</strong></div>
                                <div><span>Last Visit</span><strong>{{ $member->last_visit_at?->format('d M Y, H:i') ?? '-' }}</strong></div>
                                <div><span>Member Since</span><strong>{{ $member->created_at?->format('d M Y') }}</strong></div>
                            </div>
                        </div>

                        <div class="kb-member-right">
                            <small>Current Loyalty Balance</small>
                            <div class="kb-points">{{ $member->total_points }} <span>Total Poin</span></div>

                            <form wire:submit.prevent="addPoints" class="kb-point-form">
                                <label>Tambah Poin</label>
                                <div class="kb-row">
                                    <input type="number" wire:model.defer="pointsInput" min="1" max="100">
                                    <input type="text" wire:model.defer="activityName" placeholder="Contoh: Pembelian Kopi Susu">
                                    <button type="submit" class="kb-btn kb-btn-dark">Simpan</button>
                                </div>
                                @error('pointsInput')<p class="kb-error">{{ $message }}</p>@enderror
                            </form>

                            <button type="button" wire:click="redeem" @disabled(! $canRedeem) class="kb-btn kb-btn-primary kb-redeem-btn">
                                🎁 Redeem Reward
                            </button>

                            <p class="kb-helper">
                                @if ($canRedeem)
                                    Redeem tersedia untuk {{ $setting->redeem_required_points }} poin atau lebih.
                                @else
                                    Redeem tersedia jika poin mencapai {{ $setting->redeem_required_points }} poin.
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-filament-panels::page>
