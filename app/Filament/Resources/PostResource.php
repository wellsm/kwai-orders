<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        $syncing = Cache::has(sprintf(Post::CACHE_SYNCING, Filament::getTenant()->getUsername()));

        return $table
            ->striped()
            ->modifyQueryUsing(fn (Builder $query) => $query->when($syncing, fn (Builder $query) => $query->whereDate('updated_at', '>', now())))
            ->description('Ultima sincronização: ' . (Filament::getTenant()->getSyncedAt()?->format('d/m/Y H:i:s') ?? ' N/A '))
            ->deferLoading()
            ->emptyStateIcon('heroicon-o-clock')
            ->emptyStateHeading(
                $syncing
                    ? 'Sincronizando...'
                    : 'Sem registros'
            )
            ->emptyStateDescription(
                $syncing
                    ? str('Sincronizando suas postagens<br>Esse processo pode demorar alguns minutos')->toHtmlString()
                    : str('Clique no botão de [Sincronizar Posts]<br>Somente postagens publicas são sincronizadas')->toHtmlString()
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(),
                TextColumn::make('title')
                    ->label('Titulo')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('product.name')
                    ->label('Nome do Produto')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('views')
                    ->label('Views')
                    ->numeric()
                    ->alignCenter()
                    ->sortable()
                    ->summarize([
                        Sum::make()->label('')
                    ]),
                TextColumn::make('product.quantity')
                    ->label('Estoque')
                    ->getStateUsing(fn (Post $post) => Filament::getTenant()->verified_at === null ? ' - ' : $post->product?->quantity)
                    ->alignCenter()
                    ->numeric(),
                IconColumn::make('product_id')
                    ->getStateUsing(fn(Post $post) => $post->has_product)
                    ->label('Produto')
                    ->alignCenter()
                    ->boolean()
                    ->sortable(),
                ToggleColumn::make('notify')
                    ->label('Notificar?')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Data de Publicação')
                    ->date('d/m/Y H:i:s')
            ])
            ->filters(
                filters: [
                    Filter::make('title')
                        ->form([
                            TextInput::make('title')
                                ->label('Titulo do Post')
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['title'], fn(Builder $query) => $query->where('title', 'like', "%{$data['title']}%"));
                        })
                        ->indicateUsing(fn(array $data) => $data['title'] ? "Posts que contém \"{$data['title']}\" no título" : null),
                    Filter::make('product')
                        ->form([
                            TextInput::make('product')
                                ->label('Produto')
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['product'], fn(Builder $query) => $query->whereHas('product', fn(Builder $query) => $query->where('name', 'like', "%{$data['product']}%")));
                        })
                        ->indicateUsing(fn(array $data) => $data['product'] ? "Produtos que contém \"{$data['product']}\" no nome" : null),
                    Filter::make('views')
                        ->form([
                            TextInput::make('views')
                                ->label('Visualizações')
                                ->suffixIcon('heroicon-o-arrow-up')
                                ->numeric()
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['views'], fn(Builder $query) => $query->where('views', '>', $data['views']));
                        })
                        ->indicateUsing(fn(array $data) => $data['views'] ? "Posts com mais de " . Number::format($data['views']) . " visualizações" : null),
                    Filter::make('range')
                        ->form([
                            DateRangePicker::make('range')
                                ->label('Data da Publicação')
                                ->maxDate(now())
                                ->endDate(now())
                                ->maxSpan(['months' => 3])
                                ->default(null)
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
                                return "Posts do dia {$from}";
                            }

                            return "Posts entre os dias {$from} e {$to}";
                        }),
                ],
                layout: FiltersLayout::Modal
            )
            ->deferFilters()
            ->actions([
                Action::make('product')
                    ->label('Ver Produto')
                    ->link()
                    ->hidden(fn(Post $post) => $post->product_id === null)
                    ->url(fn(Post $post): string => "https://m-shop.kwai.com/krn-web/detail?itemId={$post->product?->id}")
                    ->openUrlInNewTab(),
                Action::make('product')
                    ->label('Ver Post')
                    ->color('success')
                    ->link()
                    ->url(fn(Post $post): string => "https://www.kwai.com/@kwai/video/{$post->id}")
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePosts::route('/'),
        ];
    }
}
