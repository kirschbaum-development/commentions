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

                editor = new Editor({
                    element: this.$refs.element,
                    extensions: [
                        StarterKit,
                        Mention.configure({
                            HTMLAttributes: {
                                class: 'mention',
                            },
                            suggestion: suggestion(mentions),
                        }),
                        Placeholder.configure({
                            placeholder: 'Type your comment…',
                        }),
                    ],
                    editorProps: {
                        attributes: {
                            class: `prose dark:prose-invert prose-sm sm:prose-base lg:prose-lg xl:prose-2xl focus:outline-none p-4 min-w-full w-full rounded-lg border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100`,
                        },
                    },
                    placeholder: 'Type something...',
                    content: content,

                    onCreate({ editor }) {
                        _this.updatedAt = Date.now()
                    },

                    onUpdate({ editor }) {
                        Livewire.dispatchTo(`filament-comments::${component}`, `body:updated`, {
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
