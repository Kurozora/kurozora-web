export default class Markdown {
    constructor() {
    }

    /**
     * Parses a Markdown string into an array of tokens.
     *
     * @param text {string} The Markdown string.
     * @param chartLimit {number} The character limit for rendering.
     * @param charCountCallback {function(count: number)} A callback function to receive the character count.
     * @param forView {boolean} If true, the output is formatted for view.
     *
     * @returns {Object<type:string, content:string>}
     */
    parse(text, chartLimit, charCountCallback = null, forView = false) {
        const ast = this.#parse(text)

        if (forView) {
            return this.#formatView(ast)
        } else {
            return this.#formatEdit(ast, chartLimit, charCountCallback)
        }
    }

    /**
     * Escapes HTML special characters in a string.
     *
     * @param text {string} The text to escape.
     *
     * @returns {string} The escaped text.
     */
    #escapeHTML(text) {
        return text.replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
    }

    /**
     * Calculates the character count in a Markdown AST.
     *
     * @param ast {Object<type: string, content: string>[]} The AST of the Markdown.
     *
     * @returns {number} The character count of the Markdown AST.
     */
    #calculateCharCount(ast) {
        let count = 0

        for (const token of ast) {
            switch (token.type) {
                case 'text':
                case 'mention':
                case 'hashtag':
                case 'reference':
                    count += [...token.content.replace(/\r\n|\r|\n/g, '')].length
                    break
                case 'emoji':
                    count += 1
                    break
                case 'code':
                case 'url':
                    for (const content of token.content) {
                        count += this.#calculateCharCount(content)
                    }
                    break
                case 'bold':
                case 'italic':
                case 'underline':
                case 'strikethrough':
                case 'highlight':
                case 'superscript':
                case 'subscript':
                case 'spoiler':
                case 'link':
                    count += this.#calculateCharCount(token.content)
                    break
                default:
                    break
            }
        }

        return count
    }

    /**
     * Parses a Markdown string into an array of tokens using CommonMark subset.
     * Supported: bold, italics, underline, strikethrough, superscript, subscript, spoiler, emoji, highlight, link, mono, auto-link, escaping,
     * and detection for username, hashtag, anime, manga, game, song, character, person, studio, episode references.
     *
     * @param {string} input
     * @param {boolean} [asPlainText=false] If true, the input is treated as plain text without Markdown parsing.
     *
     * @returns {Array<{type: string, content: any, charCount: number}>} An array of tokens representing the parsed Markdown.
     */
    #parse(input, asPlainText = false) {
        const tokens = []
        let pos = 0

        const self = this // preserve context for matchPair

        function matchPair(str, start, open, close, type, parseInner) {
            if (str.startsWith(open, start)) {
                let end = str.indexOf(close, start + open.length)
                if (
                    end !== -1 &&
                    end > start + open.length &&
                    !str.slice(start + open.length, end).includes('\n')
                ) {
                    const parsed = parseInner(str.slice(start + open.length, end))
                    return {
                        token: self.#tokenWithCount(type, {
                            prefix: open,
                            content: parsed
                        }),
                        len: end + close.length - start
                    }
                }
            }
            return null
        }

        while (pos < input.length) {
            if (input[pos] === '\\' && pos + 1 < input.length && !asPlainText) {
                const content = input[pos + 1]
                tokens.push(this.#tokenWithCount('text', {content}))
                pos += 2
                continue
            }

            if (input[pos] === '`' && !asPlainText) {
                const end = input.indexOf('`', pos + 1)
                if (end !== -1 && !input.slice(pos + 1, end).includes('\n')) {
                    const content = this.#parse(input.slice(pos + 1, end), true)
                    tokens.push(this.#tokenWithCount('code', {content}))
                    pos = end + 1
                    continue
                }
            }

            const pairTypes = [
                ['**', '**', 'bold'],
                ['*', '*', 'italic'],
                ['_', '_', 'italic'],
                ['__', '__', 'underline'],
                ['~~', '~~', 'strikethrough'],
                ['==', '==', 'highlight'],
                ['||', '||', 'spoiler'],
                ['^', '^', 'superscript'],
                ['~', '~', 'subscript']
            ]

            let matched = false
            for (const [open, close, type] of pairTypes) {
                const result = matchPair(input, pos, open, close, type, this.#parse.bind(this))
                if (result && !asPlainText) {
                    tokens.push(result.token)
                    pos += result.len
                    matched = true
                    break
                }
            }
            if (matched) continue

            if (input[pos] === ':' && input.indexOf(':', pos + 1) !== -1 && !asPlainText) {
                const end = input.indexOf(':', pos + 1)
                const name = input.slice(pos + 1, end)
                if (/^[\w+\-]+$/.test(name)) {
                    tokens.push(this.#tokenWithCount('emoji', {name}))
                    pos = end + 1
                    continue
                }
            }

            if (input[pos] === '[' && !asPlainText) {
                const endBracket = input.indexOf(']', pos)
                const startParen = input.indexOf('(', endBracket)
                const endParen = input.indexOf(')', startParen)
                if (
                    endBracket !== -1 &&
                    startParen === endBracket + 1 &&
                    endParen !== -1 &&
                    !input.slice(pos + 1, endBracket).includes('\n') &&
                    !input.slice(startParen + 1, endParen).includes('\n')
                ) {
                    const text = this.#parse(input.slice(pos + 1, endBracket))
                    const href = this.#parse(input.slice(startParen + 1, endParen))
                    tokens.push(this.#tokenWithCount('link', {
                        prefix: '[]()',
                        content: text,
                        href
                    }))
                    pos = endParen + 1
                    continue
                }
            }

            if ((input.startsWith('http://', pos) || input.startsWith('https://', pos)) && !asPlainText) {
                const match = input.slice(pos).match(/^(https?:\/\/[^\s`<>\[\]()]+)/)
                if (match) {
                    const url = match[1]
                    const prefix = url.startsWith('https://') ? 'https://' : 'http://'
                    const content = this.#parse(url.slice(prefix.length))
                    tokens.push(this.#tokenWithCount('url', {prefix, content}))
                    pos += url.length
                    continue
                }
            }

            if (input[pos] === '@' && !asPlainText) {
                const match = input.slice(pos).match(/^@([\w_]+)/)
                if (match) {
                    const content = this.#parse(match[1])
                    tokens.push(this.#tokenWithCount('mention', {prefix: '@', content}))
                    pos += match[0].length
                    continue
                }
            }

            if (input[pos] === '#' && !asPlainText) {
                const match = input.slice(pos).match(/^#([\w-]+)/)
                if (match) {
                    const content = this.#parse(match[1])
                    tokens.push(this.#tokenWithCount('hashtag', {prefix: '#', content}))
                    pos += match[0].length
                    continue
                }
            }

            const refMatchers = [
                {prefix: 'a:', type: 'reference', regex: /^a:([\w_]+)/},
                {prefix: 'm:', type: 'reference', regex: /^m:([\w_]+)/},
                {prefix: 'g:', type: 'reference', regex: /^g:([\w_]+)/},
                {prefix: 's:', type: 'reference', regex: /^s:([\w_]+)/},
                {prefix: 'c:', type: 'reference', regex: /^c:([\w_]+)/},
                {prefix: 'p:', type: 'reference', regex: /^p:([\w_]+)/},
                {prefix: 'st:', type: 'reference', regex: /^st:([\w_]+)/},
                {prefix: 'e:', type: 'reference', regex: /^e:([\w_]+)/}
            ]
            if (!asPlainText) {
                let refMatched = false
                for (const {prefix, type, regex} of refMatchers) {
                    // Ensure match happens at the start of a word
                    const isWordBoundary = pos === 0 || /\W/.test(input[pos - 1])

                    if (!isWordBoundary) {
                        continue
                    }

                    const match = input.slice(pos).match(regex)

                    if (match) {
                        const content = this.#parse(match[1])
                        tokens.push(this.#tokenWithCount(type, {prefix, content}))
                        pos += match[0].length
                        refMatched = true
                        break
                    }
                }

                if (refMatched) {
                    continue
                }
            }


            // Default: consume as plain text
            let next = pos + 1

            while (
                next < input.length &&
                input[next] !== '\\' &&
                input[next] !== '`' &&
                input[next] !== '*' &&
                input[next] !== '_' &&
                input[next] !== '~' &&
                input[next] !== '^' &&
                input[next] !== '|' &&
                input[next] !== '=' &&
                input[next] !== '[' &&
                input[next] !== ':' &&
                input[next] !== '@' &&
                input[next] !== '#' &&
                !refMatchers.some(({regex}) => input.slice(next).match(regex)) &&
                !(input.startsWith('http://', next) || input.startsWith('https://', next))
                ) {
                next++
            }

            const content = input.slice(pos, next)
            tokens.push(this.#tokenWithCount('text', {content}))
            pos = next
        }

        return tokens
    }

    /**
     * Creates a token with a character count.
     *
     * @param type {string} The type of the token.
     * @param data {Object<content>} Additional data for the token, typically containing content.
     *
     * @returns {Object<type: string, content: string, charCount: number>}
     */
    #tokenWithCount(type, data = {}) {
        if (Array.isArray(data.content)) {
            return {type, ...data, charCount: this.#calculateCharCount(data.content)}
        } else if (typeof data.content === 'string') {
            /** @type {Object<type: string, content: string, charCount: number>} */
            let ast = {type: type, ...data, charCount: 0}
            ast.charCount = this.#calculateCharCount([ast])
            return ast
        } else {
            return {type, ...data, charCount: 0} // fallback for empty or unexpected content
        }
    }

    /**
     * Formats a Markdown AST into HTML, applying character limits.
     *
     * @param ast {Object<type: string, content: string>[]} The AST of the Markdown.
     * @param charLimit {number} The character limit for rendering.
     * @param charCountCallback {function(count: number)} A callback function to receive the character count.
     *
     * @returns {string} The formatted HTML string.
     */
    #formatEdit(ast, charLimit, charCountCallback) {
        const escapeHTML = this.#escapeHTML
        let count = 0
        charCountCallback(count)

        const renderToken = (token) => {
            return [...token.content].map(ch => {
                let render = this.#renderOverCharacterLimit(count, charLimit, escapeHTML(ch))
                    .replace(/\r\n|\r|\n/g, '<br>')
                count += ch.replace(/\r\n|\r|\n/g, '').length
                charCountCallback(count)
                return render
            }).join('')
        }

        const renderAst = (ast) => {
            let output = []
            const render = (token, html) => {
                const subAST = renderAst(token.content)
                return html.replace('%s', subAST)
            };

            for (const token of ast) {
                switch (token.type) {
                    case 'text':
                        output.push(renderToken(token))
                        break
                    case 'bold':
                        output.push(render(token, '<b>' + this.#wrapWithOpacity(token.prefix, '%s', count, charLimit) + '</b>'))
                        break
                    case 'italic':
                        output.push(render(token, '<i>' + this.#wrapWithOpacity(token.prefix, '%s', count, charLimit) + '</i>'))
                        break
                    case 'underline':
                        output.push(render(token, '<u>' + this.#wrapWithOpacity(token.prefix, '%s', count, charLimit) + '</u>'))
                        break
                    case 'strikethrough':
                        output.push(render(token, '<s>' + this.#wrapWithOpacity(token.prefix, '%s', count, charLimit) + '</s>'))
                        break
                    case 'highlight':
                        output.push(render(token, '<span class="bg-secondary">' + this.#wrapWithOpacity(token.prefix, '%s', count, charLimit) + '</span>'))
                        break
                    case 'superscript':
                        output.push(render(token, '<sup>' + this.#wrapWithOpacity(token.prefix, '%s', count, charLimit) + '</sup>'))
                        break
                    case 'subscript':
                        output.push(render(token, '<sub>' + this.#wrapWithOpacity(token.prefix, '%s', count, charLimit) + '</sub>'))
                        break
                    case 'spoiler':
                        output.push(render(token, '<span class="spoiled">' + this.#wrapWithOpacity(token.prefix, '%s', count, charLimit) + '</span>'))
                        break
                    case 'code':
                        output.push(this.#renderOverCharacterLimit(count, charLimit, `<code class="bg-secondary">\`${renderAst(token.content)}\`</code>`))
                        break
                    case 'emoji':
                        output.push(this.#renderOverCharacterLimit(count, charLimit, this.#renderEmoji(token.name)))
                        charCountCallback(count++)
                        break
                    case 'mention':
                        output.push(this.#renderMention(renderAst(token.content)))
                        break
                    case 'hashtag':
                        output.push(this.#renderHashtag(renderAst(token.content)))
                        break
                    case 'url':
                        output.push(this.#renderURL(token.prefix, renderAst(token.content)))
                        break
                    case 'reference':
                        output.push(this.#renderReference(token.prefix, renderAst(token.content)))
                        break
                    case 'link':
                        output.push(`<span>[${renderAst(token.content)}](${renderAst(token.href)})</span>`)
                        break
                    default:
                        break
                }
            }
            return output.join('')
        }

        return renderAst(ast)
    }

    /**
     * Formats a Markdown AST into HTML for viewing.
     *
     * @param ast {Object<type: string, content: string>[]} The AST of the Markdown.
     *
     * @returns {string} The formatted HTML string.
     */
    #formatView(ast) {
        const escapeHTML = this.#escapeHTML

        const renderToken = (token, asPlainText) => {
            return asPlainText
                ? escapeHTML(token.content)
                : `<span>${escapeHTML(token.content)}</span>`
        }

        const renderAst = (ast, asPlainText = false) => {
            let output = []
            const render = (token, html) => {
                const subAST = renderAst(token.content)
                return html.replace('%s', subAST)
            };

            for (const token of ast) {
                switch (token.type) {
                    case 'text':
                        output.push(renderToken(token, asPlainText))
                        break
                    case 'bold':
                        output.push(render(token, '<b>%s</b>'))
                        break
                    case 'italic':
                        output.push(render(token, '<i>%s</i>'))
                        break
                    case 'underline':
                        output.push(render(token, '<u>%s</u>'))
                        break
                    case 'strikethrough':
                        output.push(render(token, '<s>%s</s>'))
                        break
                    case 'highlight':
                        output.push(render(token, '<span class="bg-secondary">%s</span>'))
                        break
                    case 'superscript':
                        output.push(render(token, '<sup>%s</sup>'))
                        break
                    case 'subscript':
                        output.push(render(token, '<sub>%s</sub>'))
                        break
                    case 'spoiler':
                        output.push(render(token, `<span class="spoiler" x-on:click="$el.classList.replace('spoiler', 'spoiled')">%s</span>`))
                        break
                    case 'code':
                        output.push(`<code class="bg-secondary">${renderAst(token.content)}</code>`)
                        break
                    case 'emoji':
                        output.push(this.#renderEmoji(token.name))
                        break
                    case 'mention':
                        const username = renderAst(token.content, true)
                        output.push(`<a href="/profile/${username}" class="text-tint" wire:navigate.hover>${'@' + username}</a>`)
                        break
                    case 'hashtag':
                        output.push(this.#renderHashtag(renderAst(token.content, true)))
                        break
                    case 'url':
                        const url = renderAst(token.content, true)
                        output.push(
                            asPlainText
                                ? (token.prefix + url)
                                : `<a href="${token.prefix + url}" target="_blank" class="text-tint">${url}</a>`
                        )
                        break
                    case 'reference':
                        output.push(`<span class="text-tint">${renderAst(token.content, true)}</span>`)
                        break
                    case 'link':
                        output.push(`<a href="${renderAst(token.href, true)}" target="_blank" class="text-tint">${renderAst(token.content, true)}</a>`)
                        break
                    default:
                        break
                }
            }
            return output.join('')
        }

        return renderAst(ast)
    }

    #wrapWithTint(marker, content) {
        return `<span class="text-tint">${marker}${content}</span>`
    }

    /**
     * Wraps the content with opacity for character limit indication.
     *
     * @param marker The marker to wrap around the content.
     * @param content The content to be wrapped.
     * @param count The current character count.
     * @param charLimit The character limit to check against.
     *
     * @returns {string}
     */
    #wrapWithOpacity(marker, content, count, charLimit) {
        return `<span class="opacity-75">${this.#renderOverCharacterLimit(count, charLimit, marker)}</span>${content}<span class="opacity-75">${this.#renderOverCharacterLimit(count, charLimit, marker)}</span>`
    }

    /**
     * Renders a URL token into an HTML anchor tag.
     *
     * @param name {string} The emoji name.
     *
     * @returns {*|string} The rendered HTML string.
     */
    #renderEmoji(name) {
        const map = {smile: '😄', sad: '😢', fire: '🔥', heart: '❤️'}
        return map[name] ? map[name] : `:${name}:`
    }

    /**
     * Renders a URL token into an HTML anchor tag.
     *
     * @param name {string} The username.
     *
     * @returns {string} The rendered HTML string.
     */
    #renderMention(name) {
        return `<span class="text-tint">@${name}</span>`
    }

    /**
     * Renders a URL token into an HTML anchor tag.
     *
     * @param name {string} The hashtag name.
     *
     * @returns {string} The rendered HTML string.
     */
    #renderHashtag(name) {
        return `<span class="text-tint">#${name}</span>`;
    }

    /**
     * Renders over character limit by wrapping the text in a span with a red background.
     *
     * @param currentCount {number} The current character count.
     * @param limit {number} The character limit.
     * @param html {string} The HTML content to render.
     *
     * @returns {*|string} The rendered HTML string, with text wrapped in a red background if over the limit.
     */
    #renderOverCharacterLimit(currentCount, limit, html) {
        const isOver = currentCount >= limit
        if (!isOver) {
            return html
        }

        const div = document.createElement('div')
        div.innerHTML = html

        // Recursively wrap the text content of child nodes
        const wrapTextNodes = node => {
            node.childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE) {
                    const span = document.createElement('span')
                    span.className = 'bg-red-500'
                    span.textContent = child.textContent
                    node.replaceChild(span, child)
                } else if (child.nodeType === Node.ELEMENT_NODE) {
                    wrapTextNodes(child)
                }
            })
        }

        wrapTextNodes(div)

        return div.innerHTML
    }

    /**
     * Renders a URL token into an HTML span with a specific scheme.
     *
     * @param scheme {string} The URL scheme (e.g., 'http://', 'https://').
     * @param url {string} The URL to render.
     *
     * @returns {string} The rendered HTML string.
     */
    #renderURL(scheme, url) {
        return `<span class="text-tint">${scheme}${url}</span>`
    }

    /**
     * Renders a reference token into an HTML span with a specific prefix.
     *
     * @param prefix {string} The prefix for the reference (e.g., 'a:', 'm:', 'g:').
     * @param name {string} The name of the reference.
     *
     * @returns {string} The rendered HTML string.
     */
    #renderReference(prefix, name) {
        return `<span class="text-tint">${prefix}${name}</span>`
    }
}
