/**
 * PicMo is a plain JavaScript emoji picker widget. It can be used in two ways:
 *
 * - As a standalone emoji picker inline in the page. The picker is rendered immediately on the page.
 * - As a popup emoji picker. The popup is triggered by a button or other interactive element.
 *
 * PicMo's emoji data comes from the Emojibase project. The data is cached locally in an IndexedDB database.
 */
import {createPopup} from '@picmo/popup-picker'
import {EmojiSelection} from 'picmo'

(function() {
    // MARK: - Properties
    let rootElement = document.querySelector('body')

    // MARK: - Initializers
    window.picmo = createPopup({
        // picker options go here
        rootElement: rootElement,
    }, {
        className: 'z-50',

        // Specify how to position the popup
        position: 'bottom-start',

        // Whether to show the close button
        showCloseButton: false
    })

    // MARK: - Events
    window.picmo.addEventListener('emoji:select', handleEmojiSelect)

    // MARK: - Functions
    /**
     * Inserts a text to the given textarea while preserving the selected start and end positions of the cursor.
     *
     * @param {HTMLTextAreaElement} element - textarea element
     * @param {string} text - text to insert
     */
    function insertTextInTextarea(element, text) {
        let [start, end] = [element.selectionStart, element.selectionEnd]
        element.setRangeText(text, start, end, 'end')
        element.focus()
        element.dispatchEvent(new Event('input'))
    }

    /**
     * Handle event when selecting an emoji.
     *
     * @param {EmojiSelection} event - event
     */
    function handleEmojiSelect(event) {
        let id = localStorage.getItem('_x_selectedCommentBox')
        let input = document.querySelector('#comment-box-' + id)
        insertTextInTextarea(input, event.emoji)
    }
})()
