import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Mention from '@tiptap/extension-mention'
import Placeholder from '@tiptap/extension-placeholder'
import suggestion from './suggestion'

document.addEventListener('alpine:init', () => {
    Alpine.data('editor', (content, mentions, component) => {
        let editor

        return {
            updatedAt: Date.now(),

            init() {
                const _this = this

                const safeContentRaw = typeof content === 'string' || content == null ? (content ?? '') : content
                let safeContent
                if (typeof safeContentRaw === 'string') {
                    if (safeContentRaw.trim() === '') {
                        safeContent = { type: 'doc', content: [{ type: 'paragraph' }] }
                    } else {
                        // Fallback: strip HTML and initialize as plain text to avoid DOM parsing of HTML
                        const div = document.createElement('div')
                        div.innerHTML = safeContentRaw
                        const text = div.textContent || ''
                        safeContent = { type: 'doc', content: [{ type: 'paragraph', content: text ? [{ type: 'text', text }] : [] }] }
                    }
                } else {
                    safeContent = safeContentRaw
                }

                // Ensure mentions is always an array to prevent forEach errors
                const safeMentions = Array.isArray(mentions) ? mentions : []

                const mentionExtension = Mention.configure({
                    HTMLAttributes: {
                        class: 'mention',
                    },
                    suggestion: suggestion(safeMentions),
                    char: '@',
                });

                editor = new Editor({
                    element: this.$refs.element,
                    extensions: [
                        StarterKit,
                        mentionExtension,
                        Placeholder.configure({
                            placeholder: 'Type your comment…',
                        }),
                    ],
                    editorProps: {
                        attributes: {
                            class: `comm:prose comm:dark:prose-invert comm:prose-sm comm:sm:prose-base comm:lg:prose-lg comm:xl:prose-2xl comm:focus:outline-none comm:p-4 comm:min-w-full comm:w-full comm:rounded-lg comm:border comm:border-gray-300 comm:dark:border-gray-700`,
                        },
                    },
                    placeholder: 'Type something...',
                    content: safeContent,

                    onCreate({ editor }) {
                        _this.updatedAt = Date.now()
                    },

                    onUpdate({ editor }) {
                        Livewire.dispatchTo(`commentions::${component}`, `body:updated`, {
                            value: editor.getHTML()
                        });

                        _this.updatedAt = Date.now()
                    },

                    onSelectionUpdate({ editor }) {
                        _this.updatedAt = Date.now()
                    },
                });

                // Watch for changes in the content property from Livewire
                Livewire.on(`${component}:content:cleared`, () => {
                    editor.commands.setContent('');
                });
            },

            isLoaded() {
                return editor
            },

            isActive(type, opts = {}) {
                return editor.isActive(type, opts)
            },
        }
    })
})
