<x-filament-widgets::widget>
    <x-filament::section>
        <div class="py-4 overflow-x-auto">
            <table class="w-full text-sm table-auto">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 font-bold">
                    <tr>
                        <td scope="col" class="px-3 py-2 text-center">Data</td>
                        <td scope="col" class="px-3 py-2 text-center">Comissão</td>
                        <td scope="col" class="px-3 py-2 text-center">Receita</td>
                        <td scope="col" class="px-3 py-2 text-center">Qtd</td>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latest as $item)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-3 py-1 flex items-center justify-center">
                                <span class="hidden sm:flex">{{ $item['date']->format('d/m/Y') }}</span>
                                <span class="sm:hidden">{{ $item['date']->format('d/m') }}</span>
                            </td>
                            <td class="px-3 py-1 text-center">
                                <span
                                    class="flex justify-center rounded-md text-xs font-medium ring-1 ring-inset py-1 bg-warning-50 text-warning-600 ring-warning-600/10 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/30 w-full">
                                    <span class="hidden sm:flex">R$&nbsp;</span>
                                    {{ Number::format($item['commission'], 2) }}
                                </span>
                            </td>
                            <td class="px-3 py-1 text-center">
                                <span
                                    class="flex justify-center rounded-md text-xs font-medium ring-1 ring-inset py-1 bg-success-50 text-success-600 ring-success-600/10 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30 w-full">
                                    <span class="hidden sm:flex">R$&nbsp;</span>
                                    {{ Number::format($item['revenue'], 2) }}
                                </span>
                            </td>
                            <td class="px-3 py-1 text-center">{{ $item['orders'] }}</td>
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
                    <span class="font-bold text-base sm:text-lg">Total: </span>
                    <span class="text-base sm:text-lg">R$ {{ Number::format($latest->sum('total'), 2) }}</span>
                </div>
            </div>
    </x-filament::section>
</x-filament-widgets::widget>
