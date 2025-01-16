@props(['disabled' => false])

<button
    {{ $attributes->merge(['class' => 'flex justify-center text-tint disabled:text-gray-400 disabled:cursor-default emoji-button', 'style' => 'width: 44px; height: 44px;']) }}
    {{ $disabled ? 'disabled' : '' }}
    x-on:click.prevent="
        localStorage.setItem('_x_selectedCommentBox', $el.id)
        picmo.toggle({
            referenceElement: $el,
            triggerElement: $el
        })
    "
    type="button"
>
    @svg('face_smiling', 'fill-current', ['width' => 24])
</button>
