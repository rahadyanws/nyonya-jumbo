<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Ingredient;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Procurement';
    protected static ?string $navigationLabel = 'Belanja Bahan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SECTION 1: HEADER NOTA ---
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Tanggal Belanja')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('supplier_name')
                            ->label('Toko/Supplier')
                            ->placeholder('Contoh: Toko "Aheng" Pasar Pagi')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),

                // --- SECTION 2: ITEMS (REPEATER) ---
                Forms\Components\Section::make('Daftar Barang')
                    ->schema([
                        Forms\Components\Repeater::make('purchaseItems') // Pastikan nama relasi di Model Purchase: "purchaseItems()"
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('ingredient_id')
                                    ->label('Bahan Baku')
                                    ->options(Ingredient::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(3)
                                    // (Opsional) Tampilkan satuan saat bahan dipilih
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $unit = Ingredient::find($state)?->unit ?? '';
                                        $set('unit_display', $unit);
                                    }),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true) // Update saat user pindah field
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateTotals($get, $set);
                                    })
                                    ->required()
                                    ->columnSpan(2),
                                
                                // Field Dummy untuk menampilkan Satuan (Read Only)
                                Forms\Components\TextInput::make('unit_display')
                                    ->label('Satuan')
                                    ->disabled()
                                    ->dehydrated(false) // Tidak disimpan ke DB
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateTotals($get, $set);
                                    })
                                    ->required()
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->readOnly()
                                    ->dehydrated() // Penting: Agar tetap tersimpan ke DB meski disabled/readonly
                                    ->columnSpan(3),
                            ])
                            ->columns(12)
                            // Fitur agar Total Bawah update saat baris dihapus
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateGrandTotal($get, $set);
                            }),
                    ]),

                // --- SECTION 3: GRAND TOTAL ---
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('total_cost')
                            ->label('Total Belanja')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated()
                            ->extraInputAttributes(['style' => 'font-weight: bold; font-size: 1.2rem; text-align: right;'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    // --- LOGIC PERHITUNGAN (Static Method agar bersih) ---
    
    // 1. Hitung Subtotal per Baris
    public static function updateTotals(Get $get, Set $set): void
    {
        // Ambil nilai qty dan harga dari baris yang sedang aktif
        $quantity = (float) $get('quantity');
        $price = (float) $get('unit_price');

        // Set Subtotal baris tersebut
        $subtotal = $quantity * $price;
        $set('subtotal', $subtotal);

        // Panggil fungsi untuk update Grand Total di luar repeater
        self::updateGrandTotal($get, $set);
    }

    // 2. Hitung Grand Total (Looping semua item di Repeater)
    public static function updateGrandTotal(Get $get, Set $set): void
    {
        // Ambil semua data di repeater 'purchaseItems'
        $items = collect($get('../../purchaseItems')); // Naik 2 level path untuk baca repeater utama jika dipanggil dari child
        
        // Jika dipanggil dari Repeater itu sendiri (saat delete row), path-nya beda. 
        // Filament agak tricky di sini, jadi kita pakai pendekatan path absolut jika memungkinkan, 
        // atau kita ambil state container.
        
        // Cara paling aman di Filament v3 untuk context nested:
        // Kita hitung manual berdasarkan array state yang ada.
        
        $total = $items->sum(function ($item) {
            return (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
        });

        // Set field 'total_cost' yang ada di root form
        $set('../../total_cost', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Supplier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchaseItems_count')
                    ->label('Jml Item')
                    ->counts('purchaseItems'),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}