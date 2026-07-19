<?php

namespace App\Filament\Admin\Pages;

use UnitEnum;
use BackedEnum;
use Filament\Pages\Page;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\Response;

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

    public function getTransactions()
    {
        return $this->baseQuery()
            ->latest('transaction_at')
            ->limit(100)
            ->get();
    }

    public function exportCsv(): mixed
    {
        $fileName = 'history-crm-kopi-banget-' . now()->format('Ymd-His') . '.csv';
        $rows = $this->baseQuery()->latest('transaction_at')->get();

        return Response::streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Tanggal',
                'Nama Member',
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
                    $row->member?->name,
                    $row->member?->phone,
                    $row->activity_name,
                    $row->type,
                    $row->points_change,
                    $row->points_before,
                    $row->points_after,
                    $row->user?->name,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
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
            ->when($this->startDate, fn ($query) => $query->whereDate('transaction_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($query) => $query->whereDate('transaction_at', '<=', $this->endDate));
    }
}