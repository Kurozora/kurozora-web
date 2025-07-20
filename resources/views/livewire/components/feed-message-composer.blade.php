<div
    x-data="composer({
        charLimit: @js($charLimit),
        content: @entangle('content').defer
    })"
    x-init="init()"
    class="space-y-3"
    x-on:keydown="handleShortcuts($event)"
>
    <div
        id="comment-box-message-box"
        class="w-full rounded whitespace-pre-wrap break-words focus:ring-0 focus:ring-offset-0"
        style="min-height: 100px"
        role="textbox"
        aria-multiline="true"
        placeholder="{{ $this->isReply ? __('Post your reply') : __('What’s happening?') }}"
        contenteditable
        x-ref="editor"
        x-html="displayContent"
        x-on:input="onInput"
        x-on:paste.prevent="onPaste($event)"
    ></div>

    <input type="hidden" x-model="content">

    <div
        class="flex gap-4 justify-between overflow-x-scroll no-scrollbar"
        x-bind:class="{'hidden': attachments.length === 0}"
    >
        <template x-for="(file, i) in attachments" :key="file.previewUrl">
            <div class="relative inline-block mr-2">
                <div class="rounded-lg overflow-hidden">
                    <img :src="file.previewUrl" class="object-cover max-w-2xl" />
                </div>

                <x-tinted-pill-button color="glass" class="absolute top-0 left-0 mt-2 ml-2" tabindex="0">
                    {{ __('Edit') }}
                </x-tinted-pill-button>

                <x-circle-button color="glass" class="absolute top-0 right-0 mt-2 mr-2" tabindex="0" x-on:click="remove($wire, i)">
                    @svg('xmark', 'fill-current', ['width' => '20'])
                </x-circle-button>

                <div class="mt-4 text-xs">
                    <div>
                        <x-checkbox x-on:change="$wire.toggleMeta(i, 'spoiler')">{{ __('Spoiler') }}</x-checkbox>

                        <x-checkbox x-on:change="$wire.toggleMeta(i, 'nsfw')">{{ __('NSFW') }}</x-checkbox>
                    </div>

                    <div class="mt-4">
                        <x-input class="w-full" x-on:input="$wire.updatedMeta(i, 'alt', $event.target.value)" placeholder="{{ __('Alt text') }}" />
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div
        class="flex justify-between pt-2"
        @if(!$this->isReply)
            x-bind:class="{'border-t border-primary': charCount !== 0}"
        @endif
    >
        <div class="flex gap-2">
{{--            <div>--}}
{{--                <x-circle-button tabindex="0" title="{{ __('Add media') }}" x-ref="filesButton" x-on:click="$refs.files.click()">--}}
{{--                    @svg('photo_on_rectangle_angled', 'fill-current', ['width' => '24'])--}}
{{--                </x-circle-button>--}}

{{--                <x-input-file class="hidden" x-ref="files" multiple accept="image/*,video/gif" x-on:change="Array.from($event.target.files).forEach(file => addAttachment($wire, file))" />--}}
{{--            </div>--}}
        </div>

        <div class="flex gap-4 items-center">
            <div class="text-sm" :class="charCount > charLimit ? 'text-red-600' : 'text-secondary'" x-text="`${charLimit - charCount}`">
                {{ $charLimit }}</div>

            <div class="h-full border-e border-primary"></div>

            <x-button
                x-on:click="$wire.set('content', content); $wire.submit()"
                x-bind:disabled="charCount == 0 || charCount > charLimit"
            >{{ __('Post') }}</x-button>
        </div>
    </div>

    <script>
        function composer({ charLimit, content }) {
            return {
                content: content || '',
                displayContent: '',
                charLimit: charLimit,
                attachments: [],
                lastSelection: null,
                draftKey: '_x_feed_message_composer_draft',
                history: [],
                historyIndex: -1,
                isComposing: false,
                lastInputTime: 0,
                charCount: 0,

                initializeComponent() {
                    console.log('calling this one')
                },

                init() {
                    // Restore draft from localStorage if available and `content` is empty
                    const saved = localStorage.getItem(this.draftKey)
                    if (saved && !this.content) {
                        this.content = saved;
                    }

                    if (this.content) {
                        this.renderEditMode()
                    }
                    // Initialize history stack
                    this.pushHistory(this.content)
                },
                onInput() {
                    this.saveSelection()
                    this.content = this.extractTextFromEditor()

                    // Save draft to localStorage
                    if (this.content) {
                        localStorage.setItem(this.draftKey, this.content)
                    } else {
                        localStorage.removeItem(this.draftKey)
                    }

                    // Only push to history if not undo/redo
                    if (!this.isComposing) {
                        this.pushHistory(this.content)
                    }

                    const formatted = markdown.parse(this.content, this.charLimit, this.charCountCallback.bind(this))

                    if (this.displayContent !== formatted) {
                        this.displayContent = formatted;
                        this.$nextTick(() => this.restoreSelection())
                    } else {
                        this.restoreSelection()
                    }
                },
                charCountCallback(newCount) {
                    this.charCount = newCount
                },
                onPaste(e) {
                    const text = (e.clipboardData || window.clipboardData).getData('text')

                    if (text === this.content) {
                        return
                    }

                    document.execCommand('insertText', false, text)
                },
                extractTextFromEditor() {
                    if (this.$refs.editor.innerText.trim() === '') {
                        return ''
                    }

                    // Remove zero-width space
                    return this.$refs.editor.innerText.replace(/\u200B/g, '')
                },
                saveSelection() {
                    const sel = window.getSelection()
                    if (!sel.rangeCount) return;
                    const range = sel.getRangeAt(0)
                    // Only save if selection is inside the editor
                    if (this.$refs.editor.contains(range.startContainer)) {
                        this.lastSelection = {
                            startOffset: range.startOffset,
                            endOffset: range.endOffset,
                            startContainerPath: this.getNodePath(range.startContainer, this.$refs.editor),
                            endContainerPath: this.getNodePath(range.endContainer, this.$refs.editor)
                        };
                    }
                },
                restoreSelection() {
                    if (!this.lastSelection) return;
                    // Only restore if editor is focused
                    if (document.activeElement !== this.$refs.editor) return;

                    const sel = window.getSelection()
                    sel.removeAllRanges()

                    const startNode = this.getNodeFromPath(this.lastSelection.startContainerPath, this.$refs.editor)
                    const endNode = this.getNodeFromPath(this.lastSelection.endContainerPath, this.$refs.editor)

                    if (!startNode || !endNode) return;

                    const range = document.createRange()
                    range.setStart(startNode, Math.min(this.lastSelection.startOffset, startNode.length || 0))
                    range.setEnd(endNode, Math.min(this.lastSelection.endOffset, endNode.length || 0))
                    sel.addRange(range)
                },
                getNodePath(node, root) {
                    // Returns an array representing the path from root to node
                    const path = []
                    let current = node;
                    while (current && current !== root) {
                        let idx = 0;
                        let sibling = current;
                        while ((sibling = sibling.previousSibling) != null) idx++;
                        path.unshift(idx)
                        current = current.parentNode;
                    }
                    return path;
                },
                getNodeFromPath(path, root) {
                    // Returns the node at the given path from root
                    let node = root;
                    for (let idx of path) {
                        if (!node.childNodes[idx]) return null;
                        node = node.childNodes[idx]
                    }
                    return node;
                },
                renderEditMode() {
                    this.displayContent = markdown.parse(this.content, this.charLimit, this.charCountCallback.bind(this))
                },
                handleShortcuts(e) {
                    if (!e.metaKey) {
                        return
                    }

                    // Undo/Redo support
                    if (e.metaKey && e.key.toLowerCase() === 'z') {
                        if (e.shiftKey) {
                            // Redo (Cmd/Ctrl+Shift+Z)
                            e.preventDefault()
                            this.redo()
                        } else {
                            // Undo (Cmd/Ctrl+Z)
                            e.preventDefault()
                            this.undo()
                        }
                        return;
                    }

                    const shortcuts = {
                        'b': '**', // bold
                        'i': '_',  // italic
                        'u': '__', // underline
                        '`': '`',  // code
                        's': '~~', // strikethrough
                        '=': '==', // highlight
                        '~': '~',  // subscript
                        '^': '^',  // superscript
                    };

                    const key = e.key.toLowerCase()
                    if (!(key in shortcuts)) {
                        return
                    }

                    e.preventDefault()
                    const marker = shortcuts[key]
                    this.insertWrappedText(marker)
                },
                insertWrappedText(marker) {
                    this.saveSelection()
                    const sel = window.getSelection()
                    if (!sel.rangeCount) return;

                    const range = sel.getRangeAt(0)
                    const selectedText = range.toString()
                    const before = marker;
                    const after = marker;

                    const newNode = document.createTextNode(before + selectedText + after)
                    range.deleteContents()
                    range.insertNode(newNode)

                    // Move cursor to end of newNode
                    const newRange = document.createRange()
                    newRange.setStart(newNode, newNode.length)
                    newRange.setEnd(newNode, newNode.length)
                    sel.removeAllRanges()
                    sel.addRange(newRange)

                    // Force update
                    this.onInput()
                },
                pushHistory(value) {
                    // Prevent duplicate consecutive entries
                    if (this.history.length && this.history[this.historyIndex] === value) return;
                    // Remove redo stack if new input after undo
                    if (this.historyIndex < this.history.length - 1) {
                        this.history = this.history.slice(0, this.historyIndex + 1)
                    }
                    this.history.push(value)
                    this.historyIndex = this.history.length - 1;
                },
                undo() {
                    if (this.historyIndex > 0) {
                        this.isComposing = true;
                        this.historyIndex--;
                        this.content = this.history[this.historyIndex]
                        this.renderEditMode()
                        this.$nextTick(() => {
                            this.placeCaretAtEnd()
                            this.isComposing = false;
                        })
                    }
                },
                redo() {
                    console.log('redo')
                    if (this.historyIndex < this.history.length - 1) {
                        console.log(this.historyIndex, this.history.length, this.history.length - 1)

                        this.isComposing = true;
                        this.historyIndex++;
                        this.content = this.history[this.historyIndex]
                        this.renderEditMode()
                        this.$nextTick(() => {
                            this.placeCaretAtEnd()
                            this.isComposing = false;
                        })
                    }
                },
                placeCaretAtEnd() {
                    // Place caret at end of contenteditable
                    const el = this.$refs.editor;
                    el.focus()
                    const range = document.createRange()
                    range.selectNodeContents(el)
                    range.collapse(false)
                    const sel = window.getSelection()
                    sel.removeAllRanges()
                    sel.addRange(range)
                },
                addAttachment($wire, file) {
                    const reader = new FileReader()
                    reader.onload = e => {
                        this.attachments.push({ file, previewUrl: e.target.result })
                        $wire.call('addAttachment', file)
                    };
                    reader.readAsDataURL(file)
                },
                remove($wire, index) {
                    this.attachments.splice(index, 1)
                    $wire.call('removeAttachment', index)
                    if (this.attachments.length < 4) {
                        $wire.$refs.filesButton.removeClass
                    }
                    // // Unselect files from the file input
                    // const fileInputs = this.$refs.files
                    // console.log(fileInputs)
                    // fileInputs.forEach(input => { input.value = ''; })
                }
            }
        }
    </script>

    <style>
        [contenteditable]:empty:before {
            content: attr(placeholder);
            color: var(--bg-tertiary-color);
        }
        .spoiler {
            background-color: var(--bg-tertiary-color);
            color: transparent;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        .spoiled {
            color: var(--primary-text-color);
            background: var(--bg-secondary-color);
            transition: color 0.2s ease;
        }
    </style>
</div>
