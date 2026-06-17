<x-filament-panels::page>
    @once
        <link rel="stylesheet" href="{{ asset('css/crm-kopi-banget.css') }}">
    @endonce

    @php($transactions = $this->getTransactions())

    <div class="kb-page">
        <div class="kb-page-header">
            <div>
                <h1>Riwayat Aktivitas</h1>
                <p>Menampilkan riwayat tambah poin, redeem, dan aktivitas transaksi member.</p>
            </div>
            <button wire:click="exportCsv" class="kb-btn kb-btn-light">⬇ Export CSV</button>
        </div>

        <div class="kb-panel kb-filter-panel">
            <input type="text" wire:model.live.debounce.500ms="keyword" placeholder="Cari nama, nomor WA, atau aktivitas">
            <input type="date" wire:model.live="startDate">
            <input type="date" wire:model.live="endDate">
        </div>

        <div class="kb-panel kb-table-panel">
            <table class="kb-table">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>Member Name</th>
                        <th>Phone Number</th>
                        <th>Activity Type</th>
                        <th>Points Change</th>
                        <th>Kasir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_at?->format('d M H:i') }}</td>
                            <td><strong>{{ $transaction->member?->name ?? '-' }}</strong></td>
                            <td>{{ $transaction->member?->phone ?? '-' }}</td>
                            <td>{{ $transaction->activity_name ?? '-' }}</td>
                            <td>
                                <span class="kb-pill {{ $transaction->points_change >= 0 ? 'kb-pill-green' : 'kb-pill-red' }}">
                                    {{ $transaction->formatted_points_change }}
                                </span>
                            </td>
                            <td>{{ $transaction->user?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="kb-empty-cell">Belum ada data riwayat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="kb-table-footer">Showing {{ $transactions->count() }} latest entries</div>
        </div>
    </div>
</x-filament-panels::page>
