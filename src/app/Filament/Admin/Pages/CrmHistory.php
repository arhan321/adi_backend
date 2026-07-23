<?php

namespace App\Filament\Admin\Pages;

use App\Models\PointTransaction;
use App\Support\CrmAccess;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Response;
use UnitEnum;

class CrmHistory extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'History';

    protected static string|UnitEnum|null $navigationGroup = 'Kopi Banget CRM';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.crm-history';

    public string $keyword = '';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public static function canAccess(): bool
    {
        return CrmAccess::canViewHistory(auth()->user());
    }

    public function canExportHistory(): bool
    {
        return CrmAccess::canExportHistory(auth()->user());
    }

    public function getTransactions()
    {
        abort_unless(
            static::canAccess(),
            403,
            'Anda tidak memiliki akses untuk melihat history CRM.',
        );

        return $this->baseQuery()
            ->latest('transaction_at')
            ->limit(100)
            ->get();
    }

    public function exportCsv(): mixed
    {
        if (! $this->canExportHistory()) {
            Notification::make()
                ->title('Akses ditolak')
                ->body('Hanya manajemen dan super admin yang dapat export history.')
                ->danger()
                ->send();

            return null;
        }

        $fileName = 'history-crm-kopi-banget-'.now()->format('Ymd-His').'.csv';
        $rows = $this->baseQuery()
            ->latest('transaction_at')
            ->cursor();

        return Response::streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Tanggal',
                'Nama Customer',
                'Nomor WA',
                'Aktivitas',
                'Tipe',
                'Perubahan Poin',
                'Poin Sebelum',
                'Poin Sesudah',
                'Kasir',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    optional($row->transaction_at)->format('Y-m-d H:i:s'),
                    $this->safeCsvValue($row->member?->name),
                    $this->safeCsvValue($row->member?->phone),
                    $this->safeCsvValue($row->activity_name),
                    $this->safeCsvValue($row->type),
                    $row->points_change,
                    $row->points_before,
                    $row->points_after,
                    $this->safeCsvValue($row->user?->name),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function baseQuery()
    {
        return PointTransaction::query()
            ->with(['member', 'user'])
            ->when($this->keyword, function ($query): void {
                $keyword = trim($this->keyword);
                $digits = preg_replace('/[^0-9]/', '', $keyword);

                $query->where(function ($query) use ($keyword, $digits): void {
                    $query
                        ->where('activity_name', 'like', "%{$keyword}%")
                        ->orWhereHas('member', function ($query) use ($keyword, $digits): void {
                            $query->where('name', 'like', "%{$keyword}%");

                            if ($digits !== '') {
                                $query->orWhere('phone', 'like', "%{$digits}%");
                            }
                        });
                });
            })
            ->when(
                $this->startDate,
                fn ($query) => $query->whereDate(
                    'transaction_at',
                    '>=',
                    $this->startDate,
                )
            )
            ->when(
                $this->endDate,
                fn ($query) => $query->whereDate(
                    'transaction_at',
                    '<=',
                    $this->endDate,
                )
            );
    }

    private function safeCsvValue(mixed $value): string
    {
        $value = (string) ($value ?? '');

        if (
            $value !== ''
            && in_array($value[0], ['=', '+', '-', '@'], true)
        ) {
            return "'".$value;
        }

        return $value;
    }
}
