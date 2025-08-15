<p
    x-data="{
        isCollapsed: true,
        countNumberOfLines() {
            let lineHeight = parseFloat(window.getComputedStyle(this.$refs.description).lineHeight);
            return this.$refs.description.offsetHeight / lineHeight;
        },
        handleExpand() {
            this.$refs.description.classList.toggle('line-clamp-5');
            this.isCollapsed = this.$refs.description.classList.contains('line-clamp-5');
        }
    }"
    x-init="isCollapsed = countNumberOfLines() >= 4.5"
    @resize.window="if (isCollapsed) { isCollapsed = countNumberOfLines() >= 4.5; }"
    {{ $attributes->merge(['class' => 'relative']) }}
>
    <span
        x-ref="description"
        x-bind:class="isCollapsed ? 'line-clamp-5' : ''"
        x-bind:style="isCollapsed ? 'mask: linear-gradient(0deg, rgba(0, 0, 0, 0) 0px, rgba(0, 0, 0, 0) 22px, rgb(0, 0, 0) 22px), linear-gradient(270deg, rgba(0, 0, 0, 0) 0px, rgba(0, 0, 0, 0) 40px, rgb(0, 0, 0) 70px);' : ''"
    >
        {{ $text }}
    </span>

    <x-simple-button
        @click="handleExpand()"
        x-show="isCollapsed"
        x-text="'{{ __('more') }}'"
        class="absolute bottom-0 right-0 text-base tracking-normal leading-snug"
    />
</p>
