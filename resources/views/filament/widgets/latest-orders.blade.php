<x-filament-widgets::widget>
    <x-filament::section>
        <div class="py-3">
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 font-bold">
                    <tr>
                        <td scope="col" class="px-6 py-2">Data</td>
                        <td scope="col" class="px-6 py-2">Receita</td>
                        <td scope="col" class="px-6 py-2">Pedidos</td>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latest as $item)
                        <tr class="border-b  dark:border-gray-700">
                            <td class="px-6 py-1">{{ $item['date'] }}</td>
                            <td class="px-6 py-1">
                                <span
                                    style="--c-50:var(--success-50);--c-400:var(--success-400);--c-600:var(--success-600);"
                                    class="fi-badge flex items-center justify-center rounded-md text-xs font-medium ring-1 ring-inset min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-success">R$
                                    {{ $item['revenue'] }}
                                </span>
                            </td>
                            <td class="px-6 py-1 text-left">{{ $item['orders'] }}</td>
                        </tr>
                    @empty
                        <tr class="border-b  dark:border-gray-700">
                            <td colspan="3" class="text-center px-6 py-1">
                                Sem Registros
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="flex justify-between items-center mt-3">
                <div>
                    <p class="text-sm text-gray-400 dark:text-gray-600">Últimos 7 dias</p>
                </div>
                <div class="text-sm flex justify-end gap-2">
                    <span class="font-bold text-lg">Total: </span>
                    <span class="text-lg">R$ {{ Number::format($latest->sum('revenue')) }}</span>
                </div>
            </div>
    </x-filament::section>
</x-filament-widgets::widget>
