<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Jobs\ProfileSync;
use App\Models\Post;
use App\Models\Team;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;

class ManagePosts extends ManageRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Team */
        $team = Filament::getTenant();
        $date = $team->getSyncedAt()?->format('d/m/Y H:i:s') ?? 'N/A';

        return [
            Action::make('sync')
                ->label('Sincronizar Posts')
                ->outlined()
                ->disabled(
                    $team->getSyncedAt()?->addHours(6)?->isFuture() ?? false
                )
                ->requiresConfirmation()
                ->modalAlignment(Alignment::Center)
                ->modalDescription(
                    str("Ultima sincronização: **{$date}**<br/>Você poderá realizar essa ação novamente após **6 horas**<br/>Aguarde, pode demorar alguns minutos")
                        ->markdown()
                        ->toHtmlString()
                )
                ->action(fn () => ProfileSync::dispatch($team))
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label('Todos')
                ->badge(
                    Post::query()
                        ->where('team_id', Filament::getTenant()->id)
                        ->count()
                ),
            'out-of-stock' => Tab::make()
                ->label('Esgotados')
                ->badge(
                    Post::query()
                        ->whereHas('product', fn(Builder $query) => $query->where('quantity', 0))
                        ->whereHas('team', fn(Builder $query) => $query->where('id', Filament::getTenant()->id)->whereNotNull('verified_at'))
                        ->count()
                )
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('product', fn(Builder $query) => $query->where('quantity', 0))),
            'without-product' => Tab::make()
                ->label('Sem Produto')
                ->badge(
                    Post::query()
                        ->where('team_id', Filament::getTenant()->id)
                        ->whereNull('product_id')
                        ->count()
                )
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('product_id')),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
