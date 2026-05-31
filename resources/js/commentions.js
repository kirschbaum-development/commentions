import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Mention from '@tiptap/extension-mention'
import Placeholder from '@tiptap/extension-placeholder'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import suggestion from './suggestion'

const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

const SAFE_LINK_PROTOCOLS = ['http:', 'https:', 'mailto:'];

const isSafeUrl = (url) => {
    try {
        return SAFE_LINK_PROTOCOLS.includes(new URL(url, window.location.origin).protocol);
    } catch {
        return false;
    }
};

const promptForLink = (editor, labels = {}) => {
    if (editor.isActive('link')) {
        editor.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    const previousUrl = editor.getAttributes('link').href;
    const url = window.prompt(labels.prompt ?? 'Enter the URL', previousUrl || 'https://');

    if (url === null) {
        return;
    }

    if (url === '') {
        editor.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    if (! isSafeUrl(url)) {
        window.alert(labels.invalid ?? 'That link uses an unsupported or unsafe URL.');
        return;
    }

    editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

// Maps a toolbar button name to the TipTap command it runs and the predicate
// used to highlight it when the active selection already has that formatting.
const toolbarCommands = {
    bold: { run: (e) => e.chain().focus().toggleBold().run(), active: (e) => e.isActive('bold') },
    italic: { run: (e) => e.chain().focus().toggleItalic().run(), active: (e) => e.isActive('italic') },
    underline: { run: (e) => e.chain().focus().toggleUnderline().run(), active: (e) => e.isActive('underline') },
    strike: { run: (e) => e.chain().focus().toggleStrike().run(), active: (e) => e.isActive('strike') },
    h1: { run: (e) => e.chain().focus().toggleHeading({ level: 1 }).run(), active: (e) => e.isActive('heading', { level: 1 }) },
    h2: { run: (e) => e.chain().focus().toggleHeading({ level: 2 }).run(), active: (e) => e.isActive('heading', { level: 2 }) },
    h3: { run: (e) => e.chain().focus().toggleHeading({ level: 3 }).run(), active: (e) => e.isActive('heading', { level: 3 }) },
    blockquote: { run: (e) => e.chain().focus().toggleBlockquote().run(), active: (e) => e.isActive('blockquote') },
    bulletList: { run: (e) => e.chain().focus().toggleBulletList().run(), active: (e) => e.isActive('bulletList') },
    orderedList: { run: (e) => e.chain().focus().toggleOrderedList().run(), active: (e) => e.isActive('orderedList') },
    code: { run: (e) => e.chain().focus().toggleCode().run(), active: (e) => e.isActive('code') },
    link: { run: (e, labels) => promptForLink(e, labels), active: (e) => e.isActive('link') },
};

document.addEventListener('alpine:init', () => {
    Alpine.data('editor', (content, mentions, component, placeholder, editorCssClasses, componentAlias = null, toolbarLabels = {}) => {
        let editor

        const defaultEditorCssClasses = `comm:prose comm:dark:prose-invert comm:prose-sm comm:sm:prose-base comm:lg:prose-lg comm:xl:prose-2xl comm:focus:outline-none comm:p-4 comm:min-w-full comm:w-full`;

        return {
            updatedAt: Date.now(),

            init() {
                const _this = this
                const targetComponent = componentAlias ?? `commentions::${component}`

                const debouncedUpdate = debounce((editor) => {
                    Livewire.dispatchTo(targetComponent, `body:updated`, editor.getHTML());
                }, 300);

                editor = new Editor({
                    element: this.$refs.element,
                    extensions: [
                        StarterKit.configure({
                            heading: { levels: [1, 2, 3] },
                        }),
                        Underline,
                        Link.configure({
                            openOnClick: false,
                            validate: (url) => isSafeUrl(url),
                            HTMLAttributes: {
                                class: 'comm-link',
                            },
                        }),
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
                            class: editorCssClasses || defaultEditorCssClasses,
                        },
                    },
                    placeholder: 'Type something...',
                    content: content,

                    onCreate({ editor }) {
                        _this.updatedAt = Date.now()
                    },

                    onUpdate({ editor }) {
                        debouncedUpdate(editor);
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

            runToolbarCommand(name) {
                const command = toolbarCommands[name]

                if (command && editor) {
                    command.run(editor, toolbarLabels)
                }
            },

            isToolbarButtonActive(name) {
                // Touch `updatedAt` so Alpine re-evaluates this on every
                // selection/content change, keeping button highlights in sync.
                void this.updatedAt

                const command = toolbarCommands[name]

                return !! (command && editor && command.active(editor))
            },
        }
    })
})
