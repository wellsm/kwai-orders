<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Jobs\ProfileSync;
use App\Models\Post;
use App\Models\Team;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ManagePosts extends ManageRecords
{
    protected static string $resource = PostResource::class;

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        /** @var Team */
        $team = Filament::getTenant();
        $date = $team->getVerifiedAt()?->format('d/m/Y H:i:s') ?? 'N/A';

        return [
            CreateAction::make('sync')
                ->label('Sincronizar Posts')
                ->createAnother(false)
                ->outlined()
                ->disabled(
                    $team->getVerifiedAt()?->addHours(6)?->isFuture() ?? false
                )
                ->requiresConfirmation()
                ->modalAlignment(Alignment::Center)
                ->modalHeading('Sincronizar Posts')
                ->modalDescription(
                    str("Ultima sincronização: **{$date}**<br/>Você poderá realizar essa ação novamente após **6 horas**<br/>Aguarde, pode demorar alguns minutos")
                        ->markdown()
                        ->toHtmlString()
                )
                ->action(fn () => $this->sync($team))
        ];
    }

    private function sync(Team $team): void
    {
        Cache::put(sprintf(Post::CACHE_SYNCING, $team->getUsername()), 1);

        ProfileSync::dispatch($team);
    }

    public function getTabs(): array
    {
        $syncing = Cache::has(sprintf(Post::CACHE_SYNCING, Filament::getTenant()->getUsername()));

        return [
            'all' => Tab::make()
                ->label('Todos')
                ->badge(
                    $syncing
                        ? 0 
                        : Post::query()
                            ->where('team_id', Filament::getTenant()->id)
                            ->where('notify', true)
                            ->count()
                ),
            'out-of-stock' => Tab::make()
                ->label('Esgotados')
                ->badge(
                    $syncing
                        ? 0 
                        : Post::query()
                            ->whereHas('product', fn(Builder $query) => $query->where('quantity', 0))
                            ->whereHas('team', fn(Builder $query) => $query->where('id', Filament::getTenant()->id)->whereNotNull('verified_at'))
                            ->where('notify', true)
                            ->count()
                )
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('product', fn(Builder $query) => $query->where('quantity', 0))),
            'without-product' => Tab::make()
                ->label('Sem Produto')
                ->badge(
                    $syncing
                        ? 0 
                        : Post::query()
                            ->where('team_id', Filament::getTenant()->id)
                            ->whereNull('product_id')
                            ->where('notify', true)
                            ->count()
                )
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('product_id')->orderBy('notify', 'DESC')),
            'dont-notify' => Tab::make()
                ->label('Não Notificar')
                ->badge(
                    $syncing
                        ? 0 
                        : Post::query()
                            ->where('team_id', Filament::getTenant()->id)
                            ->where('notify', false)
                            ->count()
                )
                ->modifyQueryUsing(fn(Builder $query) => $query->where('notify', false)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
