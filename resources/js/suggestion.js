import tippy from "tippy.js";

const insertMention = (editor, range, props) => {
    // delete the existing text before insertion
    editor
        .chain()
        .focus()
        .deleteRange(range)
        .insertContentAt(range, [
            {
                type: 'mention',
                attrs: props,
            },
            {
                type: 'text',
                text: ' ',
            },
        ])
        .run()

    // get reference to `window` object from editor element, to support cross-frame JS usage
    editor.view.dom.ownerDocument.defaultView?.getSelection()?.collapseToEnd();
};

const renderSuggestionsComponent = (items) => {
    let filteredItems = [];

    Alpine.store('filamentCommentsMentionsFiltered', {
        items: [],
        selectedIndex: 0,
    });

    return {
        items: ({ query }) => {
            filteredItems = items
                .filter(
                    item => item.name
                        .toLowerCase()
                        .replace(/\s/g, '')
                        .includes(query.toLowerCase())
                )
                .slice(0, 5);

            console.log('filteredItems', items, filteredItems, query);

            Alpine.store('filamentCommentsMentionsFiltered').items = filteredItems;
            Alpine.store('filamentCommentsMentionsFiltered').selectedIndex = 0;

            return filteredItems
        },

        command: ({ editor, range, props }) => {
            // increase range.to by one when the next node is of type "text"
            // and starts with a space character
            const nodeAfter = editor.view.state.selection.$to.nodeAfter
            const overrideSpace = nodeAfter?.text?.startsWith(' ')

            if (editor.view.state.mention$.text.length > 1) {
                range.to = range.from + (editor.view.state.mention$.text.length - 1);
            }

            if (overrideSpace) {
                range.to += 1
            }

            let attempts = 3;
            let success = false;

            while (attempts > 0 && !success) {
                try {
                    insertMention(editor, range, props);
                    success = true;
                } catch (error) {
                    attempts--;
                    range.to -= 1;
                }
            }
        },

        render: () => {
            let popup;
            let component;
            let command;

            return {
                onStart: (props) => {
                    command = props.command;
                    popup = tippy('body', {
                        getReferenceClientRect: props.clientRect,
                        content: (() => {
                            component = Alpine.data('filamentCommentsMentions', () => ({
                                add(item) {
                                    props.command({
                                        id: item.id,
                                        label: item.name
                                    });
                                },
                            }));

                            const container = document.createElement('div');
                            container.setAttribute('x-data', 'filamentCommentsMentions');
                            container.innerHTML = `
                                <template x-for='(item, index) in $store.filamentCommentsMentionsFiltered.items' :key='item.id'>
                                    <div
                                        class="mention-item"
                                        x-text="item.name"
                                        @click="add(item)"
                                        :class="{ 'comm:bg-gray-100': $store.filamentCommentsMentionsFiltered.selectedIndex === index }"
                                    ></div>
                                </template>
                            `;
                            return container;
                        })(),
                        showOnCreate: true,
                        interactive: true,
                        trigger: 'manual',
                        placement: document.dir === 'rtl' ? 'bottom-end' : 'bottom-start',
                        theme: 'light',
                        arrow: true,
                    });
                },
                onUpdate: (props) => {
                    if (!props.clientRect) {
                        return
                    }
                    popup[0].setProps({
                        getReferenceClientRect: props.clientRect,
                    });
                },
                onKeyDown: (props) => {
                    const items = Alpine.store('filamentCommentsMentionsFiltered').items;
                    let currentIndex = Alpine.store('filamentCommentsMentionsFiltered').selectedIndex;

                    if (props.event.key === 'ArrowDown') {
                        Alpine.store('filamentCommentsMentionsFiltered').selectedIndex = (currentIndex + 1) % items.length;
                        return true;
                    }

                    if (props.event.key === 'ArrowUp') {
                        Alpine.store('filamentCommentsMentionsFiltered').selectedIndex = ((currentIndex - 1) + items.length) % items.length;
                        return true;
                    }

                    if (props.event.key === 'Enter') {
                        const selectedItem = items[currentIndex];

                        if (selectedItem) {
                            command({
                                id: selectedItem.id,
                                label: selectedItem.name
                            });
                        }

                        return true;
                    }

                    if (props.event.key === 'Escape') {
                        popup[0].hide();
                        return true;
                    }

                    return false;
                },

                onExit: () => {
                    popup[0].hide();
                },
            };
        },
    }
};

export default renderSuggestionsComponent;
