<x-filament-panels::page>
    @once
        <link rel="stylesheet" href="{{ asset('css/crm-kopi-banget.css') }}">
    @endonce

    <div class="kb-page kb-settings-page">
        <div class="kb-page-header">
            <div>
                <h1>Konfigurasi Sistem</h1>
                <p>Kelola Master Promo, Automated Retention, dan template pesan WhatsApp.</p>
            </div>
        </div>

        <form wire:submit.prevent="save" class="kb-settings-form">
            <section class="kb-panel kb-setting-card">
                <div class="kb-setting-title">
                    <span>🎁</span>
                    <h3>Master Promo</h3>
                </div>

                <div class="kb-setting-row">
                    <div>
                        <label>Syarat Redeem Poin</label>
                        <p>Tentukan jumlah poin minimum untuk penukaran hadiah.</p>
                    </div>
                    <div class="kb-inline-input">
                        <input type="number" wire:model.defer="redeem_required_points" min="1">
                        <span>Poin</span>
                    </div>
                </div>

                <div class="kb-setting-row">
                    <div>
                        <label>Reward</label>
                        <p>Nama hadiah yang didapat pelanggan saat redeem.</p>
                    </div>
                    <input type="text" wire:model.defer="reward_name" placeholder="Contoh: 1 Kopi Gratis">
                </div>

                <div class="kb-setting-row">
                    <div>
                        <label>Status Promo</label>
                        <p>Aktifkan atau nonaktifkan program redeem poin.</p>
                    </div>
                    <label class="kb-switch">
                        <input type="checkbox" wire:model.defer="promo_is_active">
                        <span></span>
                    </label>
                </div>
            </section>

            <section class="kb-panel kb-setting-card">
                <div class="kb-setting-title">
                    <span>↻</span>
                    <h3>Automated Retention</h3>
                </div>

                <div class="kb-setting-row">
                    <div>
                        <label>Durasi Pengingat</label>
                        <p>Sistem mendeteksi member yang tidak berkunjung dalam kurun waktu ini.</p>
                    </div>
                    <div class="kb-inline-input">
                        <input type="number" wire:model.defer="retention_days" min="1">
                        <span>Hari</span>
                    </div>
                </div>

                <div class="kb-setting-row">
                    <div>
                        <label>Jam Kirim Retensi</label>
                        <p>Dipakai untuk schedule harian di Laravel Scheduler.</p>
                    </div>
                    <input type="time" wire:model.defer="retention_send_time">
                </div>

                <div class="kb-setting-row">
                    <div>
                        <label>Auto-Send WA</label>
                        <p>Aktifkan pengiriman otomatis melalui Twilio WhatsApp Gateway.</p>
                    </div>
                    <label class="kb-switch">
                        <input type="checkbox" wire:model.defer="auto_send_whatsapp">
                        <span></span>
                    </label>
                </div>
            </section>

            <section class="kb-panel kb-setting-card">
                <div class="kb-setting-title">
                    <span>💬</span>
                    <h3>Template Pesan WhatsApp</h3>
                </div>

                <div class="kb-form-group">
                    <label>Template Tambah Poin</label>
                    <textarea wire:model.defer="point_message_template" rows="3"></textarea>
                </div>

                <div class="kb-form-group">
                    <label>Template Redeem</label>
                    <textarea wire:model.defer="redeem_message_template" rows="3"></textarea>
                </div>

                <div class="kb-form-group">
                    <label>Template Retensi</label>
                    <textarea wire:model.defer="retention_message_template" rows="3"></textarea>
                </div>

                <p class="kb-helper">Placeholder yang tersedia: {name}, {phone}, {total_points}, {points_change}, {reward_name}, {redeem_required_points}, {days_inactive}, {business_name}.</p>
            </section>

            <div class="kb-save-area">
                <button type="submit" class="kb-btn kb-btn-primary kb-large-btn">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
