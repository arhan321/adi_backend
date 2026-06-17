<x-filament-panels::page>
    @once
        <link rel="stylesheet" href="{{ asset('css/crm-kopi-banget.css') }}">
    @endonce

    <div class="kb-page kb-center-page">
        <div class="kb-form-card">
            <div class="kb-form-header">
                <div class="kb-header-icon">👥</div>
                <h2>Tambah Member Baru</h2>
                <p>Lengkapi data untuk pendaftaran membership baru.</p>
            </div>

            <form wire:submit.prevent="save" class="kb-form-body">
                <div class="kb-form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" wire:model.defer="name" placeholder="Masukkan nama lengkap">
                    @error('name')<p class="kb-error">{{ $message }}</p>@enderror
                </div>

                <div class="kb-form-group">
                    <label>Nomor WhatsApp</label>
                    <input type="text" wire:model.defer="phone" placeholder="Contoh: 081234567890">
                    @error('phone')<p class="kb-error">{{ $message }}</p>@enderror
                </div>

                <div class="kb-form-group">
                    <label>Tanggal Lahir <span>(opsional)</span></label>
                    <input type="date" wire:model.defer="birth_date">
                    @error('birth_date')<p class="kb-error">{{ $message }}</p>@enderror
                </div>

                <div class="kb-form-group">
                    <label>Catatan <span>(opsional)</span></label>
                    <textarea wire:model.defer="notes" rows="3" placeholder="Contoh: pelanggan sering pesan kopi susu"></textarea>
                    @error('notes')<p class="kb-error">{{ $message }}</p>@enderror
                </div>

                <div class="kb-form-actions">
                    <a href="{{ \App\Filament\Admin\Pages\CrmDashboard::getUrl() }}" class="kb-btn kb-btn-light">Batal</a>
                    <button type="submit" class="kb-btn kb-btn-primary">💾 Simpan Member</button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
