<div>
    <x-card>
        <div class="flex justify-between items-center border-b px-6 py-4 text-sm dark:border-gray-700">
            <a href="{{ route('emails') }}" class="font-bold uppercase tracking-wide hover:underline flex items-center gap-2">
                {{ __("Emails Created") }}
                <span aria-hidden="true">&rarr;</span>
            </a>
            <x-select size="xs" wire:model="filter" wire:change="updateChartData">
                <option value="last_7_days">{{ __("Last 7 Days") }}</option>
                <option value="last_6_weeks">{{ __("Last 6 Weeks") }}</option>
                <option value="last_12_months">{{ __("Last 12 Months") }}</option>
            </x-select>
        </div>
        <x-charts.area :chartId="$chartId" :chartData="$chartData" function="updateChartData" color="#E7000B" />
    </x-card>
</div>
