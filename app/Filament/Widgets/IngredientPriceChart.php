<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use App\Models\PurchaseItem;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class IngredientPriceChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Harga Bahan Baku';
    
    // Urutan widget di dashboard (paling atas = 1)
    protected static ?int $sort = 2; 
    
    // Agar widget melebar penuh (opsional, hapus jika ingin setengah layar)
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // 1. Ambil ID bahan dari filter dropdown. 
        // Jika null (pertama load), ambil bahan pertama di DB.
        $activeFilter = $this->filter;
        
        if (! $activeFilter) {
            $firstIngredient = Ingredient::first();
            $activeFilter = $firstIngredient ? $firstIngredient->id : null;
        }

        // Jika tidak ada data bahan sama sekali di DB, kembalikan array kosong
        if (! $activeFilter) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // 2. Query Tren Harga menggunakan Library 'flowframe/trend'
        // Kita hitung Rata-rata (average) kolom 'unit_price' per bulan
        $data = Trend::query(
                PurchaseItem::query()->where('ingredient_id', $activeFilter)
            )
            ->between(
                start: now()->subMonths(6), // Data 6 bulan terakhir
                end: now(),
            )
            ->perMonth()
            ->average('unit_price');

        // 3. Format data untuk Chart.js
        return [
            'datasets' => [
                [
                    'label' => 'Harga Rata-rata (Rp)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#EF4444', // Warna Merah (sesuai branding)
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)', // Merah transparan
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        // Pilihan: 'line', 'bar', 'bubble', 'doughnut', 'pie'
        return 'line';
    }

    protected function getFilters(): ?array
    {
        // Mengisi Dropdown Filter dengan Nama Bahan Baku
        // Format: [id => 'Nama Bahan']
        return Ingredient::all()->pluck('name', 'id')->toArray();
    }
}