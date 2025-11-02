export function useMagicTags() {
  // Pattern to match magic tags in text
  const TAG_PATTERN = /\[\[%([\w.-]+)\]\]/g;

  // Convert text with magic tag patterns to HTML with spans
  const processTextToHTML = (text, tags) => {
    return text.replace(TAG_PATTERN, (match, tagName) => {
      const tag = tags.find(
        (t) => t.name === tagName || t.name === match || t.name === `[[%${tagName}]]`,
      );

      if (tag) {
        return `<span class="magic-tag" data-name="${tag.name}" contenteditable="false">${tag.display}</span>`;
      }

      return match;
    });
  };

  // Extract magic tag names from HTML
  const extractTagsFromHTML = (html) => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, "text/html");
    const tagElements = doc.querySelectorAll(".magic-tag[data-name]");

    return Array.from(tagElements)
      .map((el) => el.getAttribute("data-name") || "")
      .filter(Boolean);
  };

  // Create a magic tag element
  const createTagElement = (tag, readOnly = false) => {
    const span = document.createElement("span");
    span.className = readOnly ? "magic-tag magic-tag--readonly" : "magic-tag";
    span.dataset.name = tag.name;
    span.textContent = tag.display;
    span.contentEditable = "false";

    // Add readonly-specific attributes
    if (readOnly) {
      span.style.cursor = "default";
      span.style.pointerEvents = "none";
    }

    return span;
  };

  // Replace magic tag spans back to their text representation
  const htmlToText = (html) => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, "text/html");

    doc.querySelectorAll(".magic-tag[data-name]").forEach((tagEl) => {
      const name = tagEl.getAttribute("data-name");
      if (name) tagEl.replaceWith(name);
    });

    return doc.body.innerHTML;
  };

  // Process DOM element and convert magic tag patterns to spans
  const processElementContent = (element, tags, readOnly = false) => {
    if (!element || !tags || tags.length === 0) return;

    const walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT, {
      acceptNode: (node) => {
        // Skip if already inside a magic tag
        const parent = node.parentElement;
        if (parent?.classList.contains("magic-tag")) {
          return NodeFilter.FILTER_REJECT;
        }

        // Check if text contains magic tag pattern
        const text = node.textContent || "";
        if (TAG_PATTERN.test(text)) {
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
      processTextNode(textNode, tags, readOnly);
    });
  };

  // Process a single text node and replace magic tag patterns
  const processTextNode = (textNode, tags, readOnly = false) => {
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
      const fullMatch = match[0];
      const tag = tags.find(
        (t) => t.name === tagName || t.name === fullMatch || t.name === `[[%${tagName}]]`,
      );

      if (tag) {
        fragment.appendChild(createTagElement(tag, readOnly));
      } else {
        // Keep original text if no tag found
        fragment.appendChild(document.createTextNode(fullMatch));
      }

      lastIndex = TAG_PATTERN.lastIndex;
    }

    // Add remaining text
    if (lastIndex < text.length) {
      fragment.appendChild(document.createTextNode(text.substring(lastIndex)));
    }

    // Replace text node with fragment
    textNode.parentNode?.replaceChild(fragment, textNode);
  };

  // Process Vue slot content for magic tags
  const processSlotContent = (slotElement, tags, readOnly = false) => {
    if (!slotElement || !tags || tags.length === 0) return;

    // Use setTimeout to ensure DOM is updated
    setTimeout(() => {
      processElementContent(slotElement, tags, readOnly);
    }, 0);
  };

  return {
    TAG_PATTERN,
    processTextToHTML,
    extractTagsFromHTML,
    createTagElement,
    htmlToText,
    processElementContent,
    processTextNode,
    processSlotContent,
  };
}
