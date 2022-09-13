@props(['disabled' => false])

<button
    {{ $attributes->merge(['class' => 'flex justify-center text-orange-500 disabled:text-gray-400 disabled:cursor-default emoji-button', 'style' => 'width: 44px; height: 44px;']) }}
    {{ $disabled ? 'disabled' : '' }}
    x-on:click="picmo.toggle({referenceElement: $el, triggerElement: $el})"
>
    @svg('face_smiling', 'fill-current', ['width' => 24])
</button>
