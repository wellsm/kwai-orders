<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevenueResource\Pages;
use App\Models\Revenue;
use Carbon\Carbon;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class RevenueResource extends Resource
{
    protected static ?string $model = Revenue::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->pluralModelLabel('Receitas')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome'),
                TextColumn::make('value')
                    ->label('Valor')
                    ->summarize([
                        Sum::make()->money()->label('')
                    ])
                    ->money(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->date()
            ])
            ->filters(
                filters: [
                    Filter::make('name')
                        ->form([
                            TextInput::make('name')
                                ->label('Nome')
                                ->placeholder('Ex: Anúncios')
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
        return 'Receitas';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRevenues::route('/'),
        ];
    }
}
