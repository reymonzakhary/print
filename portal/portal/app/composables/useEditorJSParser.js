import { useMagicTags } from "./useMagicTags";

export const useEditorJSParser = () => {
  const { htmlToText } = useMagicTags();

  const htmlToEditorJS = (html) => {
    if (!html || typeof html !== "string") {
      return {
        time: Date.now(),
        blocks: [],
        version: "2.26.5",
      };
    }

    const parser = new DOMParser();
    const doc = parser.parseFromString(html, "text/html");
    const blocks = [];

    // Process each child element
    doc.body.childNodes.forEach((node) => {
      if (node.nodeType === Node.ELEMENT_NODE) {
        const block = parseElement(node);
        if (block) blocks.push(block);
      } else if (node.nodeType === Node.TEXT_NODE && node.textContent?.trim()) {
        blocks.push({
          type: "paragraph",
          data: { text: node.textContent.trim() },
        });
      }
    });

    return {
      time: Date.now(),
      blocks,
      version: "2.26.5",
    };
  };

  const parseElement = (element) => {
    const tagName = element.tagName.toLowerCase();

    switch (tagName) {
      case "h1":
      case "h2":
      case "h3":
      case "h4":
      case "h5":
      case "h6":
        return {
          type: "header",
          data: {
            text: element.innerHTML,
            level: parseInt(tagName[1]),
          },
        };

      case "p":
        return {
          type: "paragraph",
          data: { text: element.innerHTML },
        };

      case "ul":
      case "ol": {
        const items = Array.from(element.querySelectorAll("li")).map((li) => li.innerHTML);
        return {
          type: "list",
          data: {
            style: tagName === "ul" ? "unordered" : "ordered",
            items,
          },
        };
      }

      case "hr":
        return {
          type: "delimiter",
          data: {},
        };

      default:
        return null;
    }
  };

  const editorJSToHTML = (data) => {
    if (!data?.blocks?.length) return "";

    return data.blocks
      .map((block) => blockToHTML(block))
      .filter(Boolean)
      .join("");
  };

  const blockToHTML = (block) => {
    switch (block.type) {
      case "header": {
        const level = block.data.level || 2;
        const text = htmlToText(block.data.text || "");
        return `<h${level}>${text}</h${level}>`;
      }

      case "paragraph": {
        const text = htmlToText(block.data.text || "");
        return `<p>${text}</p>`;
      }

      case "list": {
        const tag = block.data.style === "ordered" ? "ol" : "ul";
        const items = (block.data.items || [])
          .map((item) => {
            const content = typeof item === "string" ? item : item.content;
            return `<li>${htmlToText(content || "")}</li>`;
          })
          .join("");
        return `<${tag}>${items}</${tag}>`;
      }

      case "delimiter":
        return "<hr />";

      default:
        return "";
    }
  };

  return {
    htmlToEditorJS,
    editorJSToHTML,
  };
};
