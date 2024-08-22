<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Textarea::make('content')
                    ->label('Conteúdo')
                    ->rows(20)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->searchOnBlur()
            ->searchPlaceholder('Buscar')
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->wrap()
                    ->lineClamp(2),
                TextColumn::make('product')
                    ->label('Produto')
                    ->copyable()
                    ->copyMessage('ID do Produto copiado')
                    ->copyMessageDuration(1500),
                TextColumn::make('commission')
                    ->label('Comissão')
                    ->state(fn (Order $order) => "{$order->commission}%")
                    ->alignCenter(),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL'),
                TextColumn::make('quantity')
                    ->label('Qtd')
                    ->alignCenter(),
                TextColumn::make('revenue')
                    ->label('Receita')
                    ->money('BRL')
                    ->badge()
                    ->color('success'),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime(),
            ])
            ->actions([
                Action::make('product')
                    ->label('Ver Produto')
                    ->link()
                    ->url(fn (Order $order): string => "https://m-shop.kwai.com/krn-web/detail?itemId={$order->product}")
                    ->openUrlInNewTab()
            ])
            ->filtersFormColumns(3)
            ->filters([
                /* Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('Nome')
                            ->placeholder('Ex: Fone de Ouvido X')
                    ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_at')
                            ->label('Data')
                            ->maxDate(now())
                            ->default(now()->subDay()->toDateString())
                    ]) */], layout: FiltersLayout::AboveContent);
    }

    public static function getNavigationLabel(): string
    {
        return 'Pedidos';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOrders::route('/'),
        ];
    }
}
