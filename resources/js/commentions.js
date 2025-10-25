import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Mention from '@tiptap/extension-mention'
import Placeholder from '@tiptap/extension-placeholder'
import suggestion from './suggestion'

document.addEventListener('alpine:init', () => {
    Alpine.data('editor', (content, mentions, component, placeholder, editorCssClasses) => {
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
                            placeholder: placeholder,
                        }),
                    ],
                    editorProps: {
                        attributes: {
                            class: editorCssClasses,
                        },
                    },
                    placeholder: 'Type something...',
                    content: content,

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
