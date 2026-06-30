@props(['image', 'name', 'price', 'renewalStatus', 'purchaseStatus' => null])

<div class="flex flex-col gap-2 pl-4 pr-4 pt-4 pb-4 bg-secondary rounded-xl">
    <div class="flex items-center gap-4">
        <picture class="relative flex-shrink-0 w-16 h-16">
            <img class="w-full h-full rounded-xl border border-primary object-cover" src="{{ $image }}" alt="{{ $name }}" width="64" height="64">

            <div class="absolute top-0 left-0 h-full w-full"></div>
        </picture>

        <div class="flex flex-col">
            <p class="font-semibold">{{ $name }}</p>
            <p class="text-sm text-secondary">{{ $price }}</p>
            <p class="text-sm text-secondary">{{ $renewalStatus }}</p>
        </div>
    </div>

    @if (!empty($purchaseStatus))
        <p class="text-sm">{{ $purchaseStatus }}</p>
    @endif
</div>
