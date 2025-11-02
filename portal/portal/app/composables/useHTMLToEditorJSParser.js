export const useHTMLToEditorJSParser = () => {
  const parseHTMLToEditorJSData = (htmlString) => {
    if (!htmlString || typeof htmlString !== "string") {
      return {
        time: Date.now(),
        blocks: [],
        version: "2.26.5", // Default version, matches typical Editor.js output
      };
    }

    const parser = new DOMParser();
    const doc = parser.parseFromString(htmlString, "text/html");
    const editorBlocks = [];

    // Helper to split content by newlines and add blocks
    // For headers, the first line becomes a header, subsequent lines become paragraphs.
    // For other types (context 'paragraph'), all lines become paragraphs.
    const addProcessedBlocks = (content, contextType, contextLevel) => {
      const parts = content
        .split("\n")
        .map((part) => part.trim())
        .filter((part) => part !== "");
      parts.forEach((part, index) => {
        if (contextType === "header" && index === 0) {
          editorBlocks.push({ type: "header", data: { text: part, level: contextLevel } });
        } else {
          editorBlocks.push({ type: "paragraph", data: { text: part } });
        }
      });
    };

    // Helper to process list item content, splitting by newlines
    const processListItemContent = (htmlContent) => {
      return htmlContent
        .split("\n")
        .map((part) => part.trim())
        .filter((part) => part !== "");
    };

    Array.from(doc.body.childNodes).forEach((node) => {
      if (node.nodeType === Node.ELEMENT_NODE) {
        const tagName = node.tagName.toLowerCase();
        switch (tagName) {
          case "p":
            addProcessedBlocks(node.innerHTML, "paragraph");
            break;
          case "h1":
          case "h2":
          case "h3":
          case "h4":
          case "h5":
          case "h6":
            const level = parseInt(tagName.substring(1), 10);
            addProcessedBlocks(node.innerHTML, "header", level);
            break;
          case "ul":
          case "ol": {
            const listBlockItems = [];
            Array.from(node.children).forEach((childNode) => {
              if (childNode.tagName.toLowerCase() === "li") {
                const liParts = processListItemContent(childNode.innerHTML);
                listBlockItems.push(...liParts); // Each part from li becomes a new item
              }
            });
            // Add list block whether it has items or was an empty <ol>/<ul> tag originally
            editorBlocks.push({
              type: "list",
              data: {
                style: tagName === "ul" ? "unordered" : "ordered",
                items: listBlockItems,
              },
            });
            break;
          }
          case "hr":
            editorBlocks.push({ type: "delimiter", data: {} });
            break;
          default: {
            // For any other unhandled element node, treat its innerHTML as content
            // to be split into paragraphs by newlines.
            addProcessedBlocks(node.innerHTML, "paragraph");
            break;
          }
        }
      } else if (
        node.nodeType === Node.TEXT_NODE &&
        node.textContent &&
        node.textContent.trim() !== ""
      ) {
        // For text nodes directly under the body
        addProcessedBlocks(node.textContent, "paragraph");
      }
    });

    // Fallback: if no blocks were parsed from direct children, but the body still has content
    // (e.g., htmlString was just "Some text\n<b>bold</b> with [[%tag]]")
    if (editorBlocks.length === 0 && doc.body.innerHTML && doc.body.innerHTML.trim() !== "") {
      const bodyParts = doc.body.innerHTML
        .split("\n")
        .map((part) => part.trim())
        .filter((part) => part !== "");
      bodyParts.forEach((part) => {
        editorBlocks.push({
          type: "paragraph",
          data: { text: part },
        });
      });
    }

    return {
      time: Date.now(),
      blocks: editorBlocks,
      version: "2.26.5",
    };
  };

  return {
    parseHTMLToEditorJSData,
  };
};
