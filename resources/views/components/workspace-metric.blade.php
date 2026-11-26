@props(['label', 'value', 'description', 'icon'])

<section {{ $attributes->merge(['class' => 'panel flex flex-col justify-between gap-3 !p-4 sm:!p-5']) }}>
    <div class="flex items-start justify-between gap-2 text-sm text-slate-600">
        <h2 class="!text-sm !font-medium !tracking-normal">{{ $label }}</h2>
        <x-icon :name="$icon" :size="19" class="shrink-0 text-primary" />
    </div>
    <p class="text-3xl leading-none font-bold tracking-tight sm:text-[34px]">{{ number_format($value, 0, ',', '.') }}</p>
    <p class="text-xs leading-5 text-slate-600">{{ $description }}</p>
</section>
