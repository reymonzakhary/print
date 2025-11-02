import { useMagicTags } from "~/composables/useMagicTags";

export class MagicTagProcessor {
  constructor(api, tags, readOnly = false) {
    this.api = api;
    this.tags = tags;
    this.readOnly = readOnly;
    this.observer = null;
  }

  start() {
    this.processAllBlocks();
    this.observeChanges();
  }

  stop() {
    if (this.observer) {
      this.observer.disconnect();
      this.observer = null;
    }
  }

  processAllBlocks() {
    if (!this.api || !this.api.blocks) return;

    const blocks = this.api.blocks.getBlocksCount();
    for (let i = 0; i < blocks; i++) {
      const blockElement = this.api.blocks.getBlockByIndex(i).holder;
      if (blockElement) {
        this.processBlock(blockElement);
      }
    }
  }

  processBlock(blockElement) {
    const { TAG_PATTERN } = useMagicTags();

    const walker = document.createTreeWalker(blockElement, NodeFilter.SHOW_TEXT, {
      acceptNode: (node) => {
        const text = node.textContent || "";
        if (TAG_PATTERN.test(text)) {
          // Skip if already inside a magic tag
          const parent = node.parentElement;
          if (parent?.classList.contains("magic-tag")) {
            return NodeFilter.FILTER_REJECT;
          }
          return NodeFilter.FILTER_ACCEPT;
        }
        return NodeFilter.FILTER_SKIP;
      },
    });

    const nodesToProcess = [];
    let node;

    while ((node = walker.nextNode())) {
      nodesToProcess.push(node);
    }

    // Process nodes in reverse to avoid walker issues
    nodesToProcess.reverse().forEach((textNode) => {
      this.processTextNode(textNode);
    });
  }

  processTextNode(textNode) {
    const { TAG_PATTERN, createTagElement } = useMagicTags();
    const text = textNode.textContent || "";

    if (!TAG_PATTERN.test(text)) return;

    const fragment = document.createDocumentFragment();
    let lastIndex = 0;
    let match;

    TAG_PATTERN.lastIndex = 0;

    while ((match = TAG_PATTERN.exec(text))) {
      // Add text before tag
      if (match.index > lastIndex) {
        fragment.appendChild(document.createTextNode(text.substring(lastIndex, match.index)));
      }

      // Find matching tag
      const tagName = match[1];
      const tag = this.tags.find((t) => t.name === tagName || t.name === match[0]);

      if (tag) {
        fragment.appendChild(createTagElement(tag, this.readOnly));
      } else {
        // Keep original text if no tag found
        fragment.appendChild(document.createTextNode(match[0]));
      }

      lastIndex = TAG_PATTERN.lastIndex;
    }

    // Add remaining text
    if (lastIndex < text.length) {
      fragment.appendChild(document.createTextNode(text.substring(lastIndex)));
    }

    // Replace text node with fragment
    textNode.parentNode?.replaceChild(fragment, textNode);
  }

  observeChanges() {
    if (!this.api || !this.api.blocks) return;

    this.observer = new MutationObserver(() => {
      this.processAllBlocks();
    });

    const editorElement = document.querySelector(".codex-editor");
    if (editorElement) {
      this.observer.observe(editorElement, {
        childList: true,
        subtree: true,
        characterData: true,
      });
    }
  }
}
