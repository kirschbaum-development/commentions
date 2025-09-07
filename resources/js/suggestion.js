import tippy from "tippy.js";

// Removed insertMention function - using inline approach in command instead

const renderSuggestionsComponent = (items) => {
    let filteredItems = [];

    // Initialize the store
    Alpine.store('filamentCommentsMentionsFiltered', {
        items: [],
        selectedIndex: 0,
    });

    // Ensure items is always an array
    const safeItems = Array.isArray(items) ? items : [];

    return {
        items: ({ query }) => {
            filteredItems = safeItems
                .filter(item => {
                    return item.name.toLowerCase().startsWith(query.toLowerCase());
                })
                .slice(0, 5);

            Alpine.store('filamentCommentsMentionsFiltered').items = filteredItems;
            Alpine.store('filamentCommentsMentionsFiltered').selectedIndex = 0;

            return filteredItems
        },

        command: ({ editor, range, props }) => {
            try {
                const attrs = {
                    id: props.id ?? props.label ?? props.name,
                    label: props.label ?? props.name ?? String(props.id ?? ''),
                };

                // Replace the trigger + query with the mention node and a trailing space
                editor
                    .chain()
                    .focus()
                    .insertContentAt({ from: range.from, to: range.to }, [
                        { type: 'mention', attrs },
                        { type: 'text', text: ' ' },
                    ])
                    .run();
            } catch (error) {}
        },

        render: () => {
            let popup;
            let component;
            let command;

            return {
                onStart: (props) => {
                    command = props.command;

                    // Create a simple positioned div instead of using Tippy/Popper

                    // Register the Alpine component
                    Alpine.data('filamentCommentsMentions', () => ({
                        add(item) {
                            props.command({
                                id: item.id,
                                label: item.name
                            });
                        },
                    }));

                    const container = document.createElement('div');
                    container.setAttribute('x-data', 'filamentCommentsMentions');
                    container.className = 'mention-popup-container';
                    container.style.cssText = `
                        position: fixed;
                        z-index: 9999;
                        background: white;
                        border: 1px solid #d1d5db;
                        border-radius: 6px;
                        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                        padding: 8px;
                        max-height: 128px;
                        overflow-y: auto;
                        min-width: 200px;
                        max-width: 300px;
                    `;

                    container.innerHTML = `
                        <template x-for='(item, index) in $store.filamentCommentsMentionsFiltered.items' :key='item.id'>
                            <div
                                class="mention-item"
                                style="padding: 4px 8px; cursor: pointer; border-radius: 4px; font-size: 14px;"
                                x-text="item.name"
                                @click="add(item)"
                                :style="{ backgroundColor: $store.filamentCommentsMentionsFiltered.selectedIndex === index ? '#f3f4f6' : 'transparent' }"
                            ></div>
                        </template>
                        <div x-show="$store.filamentCommentsMentionsFiltered.items.length === 0" style="color: #6b7280; font-size: 14px; padding: 4px 8px;">
                            No matches found
                        </div>
                    `;

                    // Position the container
                    try {
                        const rect = props.clientRect();
                        const isRTL = document.dir === 'rtl';
                        
                        if (isRTL) {
                            // For RTL, position from the right edge
                            container.style.right = `${window.innerWidth - rect.right}px`;
                            container.style.left = 'auto';
                        } else {
                            // For LTR, position from the left edge
                            container.style.left = `${rect.left}px`;
                            container.style.right = 'auto';
                        }
                        container.style.top = `${rect.bottom + 4}px`;
                    } catch (error) {
                        container.style.left = '100px';
                        container.style.top = '100px';
                    }

                    // Add to document
                    document.body.appendChild(container);
                    popup = { element: container };

                    // Initialize Alpine on the container
                    setTimeout(() => {
                        Alpine.initTree(container);
                    }, 0);
                },
                onUpdate: (props) => {
                    if (!props.clientRect || !popup || !popup.element) {
                        return
                    }
                    try {
                        const rect = props.clientRect();
                        const isRTL = document.dir === 'rtl';
                        
                        if (isRTL) {
                            // For RTL, position from the right edge
                            popup.element.style.right = `${window.innerWidth - rect.right}px`;
                            popup.element.style.left = 'auto';
                        } else {
                            // For LTR, position from the left edge
                            popup.element.style.left = `${rect.left}px`;
                            popup.element.style.right = 'auto';
                        }
                        popup.element.style.top = `${rect.bottom + 4}px`;
                    } catch (error) {}
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
                        if (popup && popup.element && popup.element.parentNode) {
                            popup.element.parentNode.removeChild(popup.element);
                        }
                        return true;
                    }

                    return false;
                },

                onExit: () => {
                    if (popup && popup.element && popup.element.parentNode) {
                        popup.element.parentNode.removeChild(popup.element);
                    }
                },
            };
        },
    }
};

export default renderSuggestionsComponent;
