@props(['size' => '32px', 'imageUrl' => null])

@if (!empty($imageUrl))
    <div class="relative overflow-hidden" style="border-radius: 26%;">
        <div class="bg-cover bg-no-repeat" :style="`background-image: url('{{ $imageUrl }}'); border-radius: 26%; height: {{ $size }}; width: {{ $size }};`"></div>

        <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20" style="border-radius: 26%;"></div>
    </div>
@else
    <div
        class="relative overflow-hidden"
        style="border-radius: 26%;"
        x-data="{
            selectedAppIcon: settings.selectedAppIcon?.url ?? '{{ asset('images/icons/Default/Kurozora/Kurozora.webp') }}'
        }"
    >
        <div id="app-icon" class="bg-cover bg-no-repeat" :style="`background-image: url('${selectedAppIcon}'); border-radius: 26%; height: {{ $size }}; width: {{ $size }};`"></div>

        <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20" style="border-radius: 26%;"></div>
    </div>
@endif
