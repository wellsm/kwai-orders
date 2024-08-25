<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->pluralModelLabel('Pedidos')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                TextColumn::make('name')
                    ->label('Nome')
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
                    ->money(),
                TextColumn::make('quantity')
                    ->label('Qtd')
                    ->alignCenter(),
                TextColumn::make('revenue')
                    ->label('Receita')
                    ->money()
                    ->badge()
                    ->color('success')
                    ->summarize([
                        Sum::make()->money()->label('')
                    ]),
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
            ->filters(
                filters: [
                    Filter::make('name')
                        ->form([
                            TextInput::make('name')
                                ->label('Nome')
                                ->placeholder('Ex: Fone de Ouvido X')
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['name'], fn (Builder $query) => $query->where('name', 'like', "%{$data['name']}%"));
                        })
                        ->indicateUsing(fn (array $data) => $data['name'] ? "Pedidos que contém \"{$data['name']}\" no nome" : null),
                    Filter::make('created_at')
                        ->form([
                            DatePicker::make('created_at')
                                ->label('Data')
                                ->maxDate(now())
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['created_at'], fn (Builder $query) => $query->whereDate('created_at', $data['created_at']));
                        })
                        ->indicateUsing(fn (array $data) => $data['created_at'] ? "Pedidos do dia " . Carbon::parse($data['created_at'])->format('d/m/Y') : null),
                ], 
                layout: FiltersLayout::AboveContent
            )
            ->persistFiltersInSession()
            ->deferFilters();
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
