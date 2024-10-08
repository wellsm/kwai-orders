<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form;
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
                    ->label('%')
                    ->state(fn(Order $order) => "{$order->commission}%")
                    ->alignCenter(),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money()
                    ->summarize([
                        Sum::make()->money()->label('')
                    ]),
                TextColumn::make('quantity')
                    ->label('Qtd')
                    ->alignCenter()
                    ->summarize([
                        Sum::make()->label('')
                    ]),
                TextColumn::make('revenue')
                    ->label('Comissão')
                    ->money()
                    ->badge()
                    ->color(fn (Order $order) => $order->revenue == 0 ? 'gray' : 'success')
                    ->summarize([
                        Sum::make()->money()->label('')
                    ])
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime(),
            ])
            ->actions([
                Action::make('product')
                    ->label('Ver Produto')
                    ->link()
                    ->url(fn(Order $order): string => "https://m-shop.kwai.com/krn-web/detail?itemId={$order->product}")
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
                            return $query->when($data['name'], fn(Builder $query) => $query->where('name', 'like', "%{$data['name']}%"));
                        })
                        ->indicateUsing(fn(array $data) => $data['name'] ? "Pedidos que contém \"{$data['name']}\" no nome" : null),
                    Filter::make('range')
                        ->form([
                            DateRangePicker::make('range')
                                ->label('Data do Pedido')
                                ->maxDate(now())
                                ->startDate(now()->subMonth())
                                ->endDate(now())
                                ->maxSpan(['months' => 3])
                                ->autoApply(),
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['range'], function (Builder $query, string $range) {
                                [$from, $to] = explode(' - ', $range);

                                $from = Carbon::createFromFormat('d/m/Y', $from)->startOfDay();
                                $to   = Carbon::createFromFormat('d/m/Y', $to)->endOfDay();

                                return $query->whereBetween('created_at', [$from, $to]);
                            });
                        })
                        ->indicateUsing(function (array $data) {
                            if (empty($data['range'])) {
                                return null;
                            }

                            [$from, $to] = explode(' - ', $data['range']);

                            if ($from === $to) {
                                return "Pedidos do dia {$from}";
                            }

                            return "Pedidos entre os dias {$from} e {$to}";
                        }),
                ],
                layout: FiltersLayout::AboveContentCollapsible
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
