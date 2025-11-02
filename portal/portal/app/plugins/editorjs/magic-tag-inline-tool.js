export class MagicTagInlineTool {
  static get isInline() {
    return true;
  }

  static get title() {
    return "Insert Tag";
  }

  static get sanitize() {
    return {
      span: {
        class: "magic-tag",
        "data-name": true,
        contenteditable: false,
      },
    };
  }

  static ICONS = {
    ARROW_LEFT:
      '<svg viewBox="0 0 448 512" width="12" height="12" fill="currentColor"><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/></svg>',
    ARROW_RIGHT:
      '<svg viewBox="0 0 448 512" width="12" height="12" fill="currentColor"><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg>',
    INFO_CIRCLE:
      '<svg viewBox="0 0 512 512" width="12" height="12" fill="currentColor"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-144c-17.7 0-32-14.3-32-32s14.3-32 32-32s32 14.3 32 32s-14.3 32-32 32z"/></svg>',
    TRASH:
      '<svg viewBox="0 0 448 512" width="12" height="12" fill="currentColor"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg>',
  };

  static STYLES = {
    TAG_TOOLBAR_CONTAINER: `
      display: none;
      position: absolute;
      z-index: 1002;
      display: flex;
      align-items: center;
      gap: 4px;
      border-radius: 6px;
      background-color: #4A5568;
      padding: 4px;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
      color: white;
      font-family: sans-serif;
    `,
    TAG_TOOLBAR_SELECT: `
      min-width: 180px;
      background-color: #2D3748;
      color: white;
      border: 1px solid #4A5568;
      border-radius: 4px;
      padding: 4px 8px;
      font-size: 12px;
      outline: none;
    `,
    TAG_TOOLBAR_BUTTON: `
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #718096;
      padding: 5px 7px;
      border: none;
      border-radius: 4px;
      color: white;
      font-size: 11px;
      cursor: pointer;
      transition: background-color 0.2s;
    `,
    TAG_TOOLBAR_BUTTON_HOVER: `#A0AEC0`,
  };

  constructor({ api, config }) {
    this.api = api;
    this.config = config || {};
    this.tags = (this.config.tags || []).map((tag) => ({
      value: tag.name,
      name: tag.name,
      display: tag.display,
      description: tag.description || "",
    }));

    // Use custom notifier if provided, otherwise fallback to EditorJS notifier or alert
    this.notifier = this.config.notifier ||
      this.api.notifier || {
        show: ({ message }) => alert(message),
      };

    this.selectedTag = null;
    this.tagToolbar = null;
    this.currentRangeForInsertion = null;
    this.currentToolbarMode = "edit";

    this._editorWrapper = null;
    this._toolbarButtonListeners = [];
    this._boundHandleToolbarSelectChange = this._handleToolbarSelectChange.bind(this);

    this._createToolButton();
    this._createTagToolbar();
    this._bindEditorEvents();
    this._setupLiveTagProcessing();

    requestAnimationFrame(() => {
      this.processAllBlocks();
    });
  }

  _getEditorWrapper() {
    if (this._editorWrapper && document.body.contains(this._editorWrapper)) {
      return this._editorWrapper;
    }
    let wrapper = null;
    if (this.button && this.button.isConnected) {
      wrapper = this.button.closest('.codex-editor, .editorjs, [data-gramm="false"]');
      if (wrapper) {
        this._editorWrapper = wrapper;
        return this._editorWrapper;
      }
    }
    if (this.api && this.api.blocks && typeof this.api.blocks.getBlockByIndex === "function") {
      try {
        const currentBlockIndex = this.api.blocks.getCurrentBlockIndex();
        const block = this.api.blocks.getBlockByIndex(
          currentBlockIndex > -1 ? currentBlockIndex : 0,
        );
        if (block && block.holder) {
          wrapper = block.holder.closest('.codex-editor, .editorjs, [data-gramm="false"]');
        }
      } catch {
        /* No blocks yet or API error */
      }
    }
    if (wrapper) {
      this._editorWrapper = wrapper;
      return this._editorWrapper;
    } else {
      this._editorWrapper = document.body;
      return this._editorWrapper;
    }
  }

  _createToolButton() {
    this.button = document.createElement("button");
    this.button.type = "button";
    this.button.innerHTML =
      '<svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M8 3.2c.5 0 .9.4.9.9s-.4.9-.9.9-.9-.4-.9-.9.4-.9.9-.9zm0 7.8c-.5 0-.9-.4-.9-.9s.4-.9.9-.9.9.4.9.9-.4.9-.9.9zm3.6-2.1L15 6.3c.4-.4.4-1 0-1.4l-3.1-3.1c-.4-.4-1-.4-1.4 0L7 5.3c-.3.3-.4.7-.3 1.1L1.1 12c-.4.4-.4 1 0 1.4l1.5 1.5c.4.4 1 .4 1.4 0l5.6-5.6c.3.1.7.0 1-.3l1-1zM3.3 13.6L2 12.3l5-5 1.3 1.3-5 5z"/></svg>';
    const inlineToolButtonClass =
      this.api.styles && this.api.styles.inlineToolButton
        ? this.api.styles.inlineToolButton
        : "ce-inline-tool";
    this.button.classList.add(inlineToolButtonClass);
  }

  render() {
    return this.button;
  }

  checkState() {
    const parentTagElement = this.api.selection.findParentTag("SPAN", "magic-tag");
    if (parentTagElement) {
      return false;
    } else {
      if (this.selectedTag) {
        this._deselectAndClosePrevious();
      }
      if (this.api.selection.isCollapsed && !this.api.selection.isCollapsed()) {
        return true;
      }
      return false;
    }
  }

  surround(range) {
    this.currentRangeForInsertion = range;
    this._showTagToolbar(null, range, "insert");
  }

  _createTagToolbar() {
    this.tagToolbar = document.createElement("div");
    this.tagToolbar.className = "magic-tag-toolbar";
    this.tagToolbar.style.cssText = MagicTagInlineTool.STYLES.TAG_TOOLBAR_CONTAINER;
    this.tagToolbar.style.display = "none";

    const select = document.createElement("select");
    select.style.cssText = MagicTagInlineTool.STYLES.TAG_TOOLBAR_SELECT;
    const placeholderOption = document.createElement("option");
    placeholderOption.value = "";
    placeholderOption.textContent = "Select a tag...";
    placeholderOption.disabled = true;
    select.appendChild(placeholderOption);

    this.tags.forEach((tag) => {
      const option = document.createElement("option");
      option.value = tag.value;
      option.textContent = tag.display;
      select.appendChild(option);
    });

    select.addEventListener("change", this._boundHandleToolbarSelectChange);
    this.tagToolbar.appendChild(select);
    this.tagToolbarSelect = select;

    this._toolbarButtonListeners = [];

    const addButtonWithCleanup = (iconHTML, title, action) => {
      const button = this._createToolbarButton(iconHTML, title, action);
      this.tagToolbar.appendChild(button.element);
      this._toolbarButtonListeners.push(button.cleanup);
      return button.element;
    };

    this.tagToolbarMoveLeftButton = addButtonWithCleanup(
      MagicTagInlineTool.ICONS.ARROW_LEFT,
      "Move tag left",
      () => this._moveTagLeft(),
    );
    this.tagToolbarMoveRightButton = addButtonWithCleanup(
      MagicTagInlineTool.ICONS.ARROW_RIGHT,
      "Move tag right",
      () => this._moveTagRight(),
    );
    this.tagToolbarInfoButton = addButtonWithCleanup(
      MagicTagInlineTool.ICONS.INFO_CIRCLE,
      "Tag description",
      () => this._showTagInfo(),
    );
    this.tagToolbarRemoveButton = addButtonWithCleanup(
      MagicTagInlineTool.ICONS.TRASH,
      "Remove tag",
      () => this._removeSelectedTag(),
    );

    document.body.appendChild(this.tagToolbar);
  }

  _handleToolbarSelectChange(e) {
    const newTagName = e.target.value;
    const newTagData = this.tags.find((t) => t.value === newTagName);
    if (!newTagData) return;

    if (this.currentToolbarMode === "edit" && this.selectedTag) {
      this._replaceTagInEditor(this.selectedTag, newTagData);
    } else if (this.currentToolbarMode === "insert" && this.currentRangeForInsertion) {
      this._insertTagInEditor(newTagData, this.currentRangeForInsertion);
      this._deselectAndClosePrevious();
      this.currentRangeForInsertion = null;
    }
    if (this.currentToolbarMode === "insert" && e.target) {
      e.target.value = "";
    }
  }

  _createToolbarButton(iconHTML, title, onClickAction) {
    const button = document.createElement("button");
    button.type = "button";
    button.style.cssText = MagicTagInlineTool.STYLES.TAG_TOOLBAR_BUTTON;
    button.innerHTML = iconHTML;
    button.title = title;

    const originalBg = "#718096";

    const handleClick = (e) => {
      e.stopPropagation();
      onClickAction();
    };
    const handleMouseEnter = () =>
      (button.style.backgroundColor = MagicTagInlineTool.STYLES.TAG_TOOLBAR_BUTTON_HOVER);
    const handleMouseLeave = () => (button.style.backgroundColor = originalBg);

    button.addEventListener("click", handleClick);
    button.addEventListener("mouseenter", handleMouseEnter);
    button.addEventListener("mouseleave", handleMouseLeave);

    return {
      element: button,
      cleanup: () => {
        button.removeEventListener("click", handleClick);
        button.removeEventListener("mouseenter", handleMouseEnter);
        button.removeEventListener("mouseleave", handleMouseLeave);
      },
    };
  }

  _insertTagInEditor(tag, range) {
    if (!range) return;
    if (typeof tag !== "object" || !tag.name || !tag.display) {
      console.error("MagicTagInlineTool: Invalid tag data for insertion.", tag);
      return;
    }

    const span = document.createElement("span");
    span.classList.add("magic-tag");
    span.dataset.name = tag.name;
    span.textContent = tag.display;
    span.contentEditable = false;
    span.setAttribute("contenteditable", "false");
    span.setAttribute("data-editable", "false");

    let editableParent = range.startContainer;
    while (editableParent) {
      if (
        editableParent.nodeType === Node.ELEMENT_NODE &&
        editableParent.contentEditable === "true"
      )
        break;
      if (
        editableParent.nodeType === Node.DOCUMENT_NODE ||
        editableParent === this._getEditorWrapper() ||
        editableParent === document.body
      ) {
        editableParent = null;
        break;
      }
      editableParent = editableParent.parentNode;
    }
    if (!editableParent) {
      if (this.notifier && typeof this.notifier.show === "function") {
        this.notifier.show({ message: "Cannot insert tag here.", style: "error" });
      }
      return;
    }

    range.deleteContents();
    range.insertNode(span);

    const zeroWidthSpace = document.createTextNode("\u200B");
    const parent = span.parentNode;
    if (parent) {
      parent.insertBefore(zeroWidthSpace, span.nextSibling);
    }

    const newRange = document.createRange();
    newRange.setStartAfter(span);
    newRange.collapse(true);
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(newRange);

    this._triggerEditorChange();
  }

  _handleTagClick(event, clickedTagElement) {
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    // Clear any existing selection to prevent interference
    const selection = window.getSelection();
    if (selection && selection.rangeCount > 0) {
      selection.removeAllRanges();
    }

    // Ensure the tag maintains its properties
    this._ensureTagIntegrity(clickedTagElement);

    if (this.selectedTag === clickedTagElement) {
      this._deselectAndClosePrevious();
    } else {
      this._deselectAndClosePrevious();
      this.selectedTag = clickedTagElement;
      this.currentRangeForInsertion = null;
      this._showTagToolbar(this.selectedTag, null, "edit");
    }
  }

  _ensureTagIntegrity(tagElement) {
    if (!tagElement || !tagElement.classList.contains("magic-tag")) return;

    // Ensure the tag has the correct attributes
    tagElement.contentEditable = false;
    tagElement.setAttribute("contenteditable", "false");
    tagElement.setAttribute("data-editable", "false");

    // Ensure it has the magic-tag class
    if (!tagElement.classList.contains("magic-tag")) {
      tagElement.classList.add("magic-tag");
    }

    // Ensure it has a data-name attribute
    if (!tagElement.dataset.name) {
      // Try to recover from text content or set a fallback
      const textContent = tagElement.textContent || "";
      tagElement.dataset.name = textContent;
    }

    // Prevent any child elements from being editable
    const children = tagElement.querySelectorAll("*");
    children.forEach((child) => {
      child.contentEditable = false;
      child.setAttribute("contenteditable", "false");
      child.setAttribute("data-editable", "false");
    });
  }

  _deselectAndClosePrevious() {
    if (this.selectedTag) {
      this.selectedTag.classList.remove("magic-tag--selected");
    }
    this.selectedTag = null;
    this.currentRangeForInsertion = null;
    this._closeTagToolbar();
  }

  _showTagToolbar(tagElement, rangeForPositioning, mode) {
    if (!this.tagToolbar) this._createTagToolbar();

    this.currentToolbarMode = mode;
    const toolbarElement = this.tagToolbar;

    if (mode === "edit" && tagElement) {
      this.selectedTag = tagElement;
      tagElement.classList.add("magic-tag--selected");
      const tagName = tagElement.dataset.name;
      const tagData = this.tags.find((t) => t.value === tagName);
      this.tagToolbarSelect.value = tagName || "";
      this.tagToolbarInfoButton.title = tagData
        ? tagData.description || "No description"
        : "No description";

      this.tagToolbarMoveLeftButton.style.display = "flex";
      this.tagToolbarMoveRightButton.style.display = "flex";
      this.tagToolbarInfoButton.style.display = "flex";
      this.tagToolbarRemoveButton.style.display = "flex";
      this.tagToolbarSelect.querySelector('option[value=""]').disabled = true;
    } else if (mode === "insert") {
      this.selectedTag = null;
      this.tagToolbarSelect.value = "";
      this.tagToolbarInfoButton.title = "Select a tag to insert";

      this.tagToolbarMoveLeftButton.style.display = "none";
      this.tagToolbarMoveRightButton.style.display = "none";
      this.tagToolbarInfoButton.style.display = "none";
      this.tagToolbarRemoveButton.style.display = "none";
      this.tagToolbarSelect.querySelector('option[value=""]').disabled = false;
      this.tagToolbarSelect.value = "";
    }

    // Positioning Logic
    let rect;
    if (mode === "edit" && tagElement) {
      rect = tagElement.getBoundingClientRect();
    } else if (mode === "insert" && rangeForPositioning) {
      rect = rangeForPositioning.getBoundingClientRect();
      const inlineToolbarButtonRect = this.button.getBoundingClientRect();
      if (inlineToolbarButtonRect.width > 0 && inlineToolbarButtonRect.height > 0) {
        const inlineToolbar = this.button.closest(
          ".ce-inline-toolbar--showed, .ce-inline-toolbar--opened",
        );
        if (inlineToolbar) {
          rect = inlineToolbarButtonRect;
        }
      }
    } else {
      console.error(
        "MagicTagInlineTool: _showTagToolbar called with invalid arguments for mode:",
        mode,
      );
      return;
    }

    if (
      !rect ||
      (rect.width === 0 &&
        rect.height === 0 &&
        mode === "insert" &&
        !this.button.closest(".ce-inline-toolbar--showed"))
    ) {
      const viewportWidth = window.innerWidth;
      const viewportHeight = window.innerHeight;
      toolbarElement.style.left = `${viewportWidth / 2 - toolbarElement.offsetWidth / 2 + window.scrollX}px`;
      toolbarElement.style.top = `${viewportHeight / 3 + window.scrollY}px`;
    } else {
      const toolbarHeight = toolbarElement.offsetHeight || 30;
      const toolbarWidth = toolbarElement.offsetWidth || 250;

      let top = rect.top + window.scrollY - toolbarHeight - 5;
      if (
        top < window.scrollY + 5 ||
        (mode === "insert" &&
          rect.bottom + window.scrollY + 5 + toolbarHeight > window.scrollY + window.innerHeight)
      ) {
        top = rect.bottom + window.scrollY + 5;
      }
      let left = rect.left + window.scrollX + rect.width / 2 - toolbarWidth / 2;

      if (left < window.scrollX + 5) left = window.scrollX + 5;
      if (left + toolbarWidth > window.innerWidth + window.scrollX - 5) {
        left = window.innerWidth + window.scrollX - toolbarWidth - 5;
      }
      if (top + toolbarHeight > window.innerHeight + window.scrollY - 5) {
        top = window.innerHeight + window.scrollY - toolbarHeight - 5;
      }
      if (top < window.scrollY + 5) top = window.scrollY + 5;

      toolbarElement.style.top = `${top}px`;
      toolbarElement.style.left = `${left}px`;
    }

    toolbarElement.style.display = "flex";
    setTimeout(() => this._addDocumentListener(this._closeTagToolbarListener), 0);
  }

  _closeTagToolbarListener = (e) => {
    if (
      this.tagToolbar &&
      this.tagToolbar.style.display !== "none" &&
      !this.tagToolbar.contains(e.target) &&
      (!this.selectedTag || !this.selectedTag.contains(e.target)) &&
      !this.button.contains(e.target)
    ) {
      this._deselectAndClosePrevious();
    }
  };

  _closeTagToolbar() {
    if (this.tagToolbar) {
      this.tagToolbar.style.display = "none";
    }
    this.currentToolbarMode = "edit";
    if (this.tagToolbarSelect) this.tagToolbarSelect.value = "";
    this._removeDocumentListener(this._closeTagToolbarListener);
  }

  _replaceTagInEditor(tagElement, newTagData) {
    tagElement.dataset.name = newTagData.name;
    tagElement.textContent = newTagData.display;
    this._deselectAndClosePrevious();
    this._triggerEditorChange();
  }

  _removeSelectedTag() {
    if (this.selectedTag) {
      this.selectedTag.remove();
      this._deselectAndClosePrevious();
      this._triggerEditorChange();
    }
  }

  _showTagInfo() {
    if (!this.selectedTag) return;
    const tagName = this.selectedTag.dataset.name;
    const tagData = this.tags.find((t) => t.value === tagName);
    const description = tagData?.description || "No description available";

    if (this.notifier && typeof this.notifier.show === "function") {
      this.notifier.show({
        message: `${tagData?.display || tagName}: ${description}`,
        style: "info",
      });
    } else {
      alert(`${tagData?.display || tagName}: ${description}`);
    }
  }

  _moveTag(direction) {
    if (!this.selectedTag) return;

    const currentTag = this.selectedTag;
    const parent = currentTag.parentNode;

    if (!parent) return;

    let targetPositionNode = null;

    if (direction === "left") {
      let foundPrevMagicTag = null;
      let prevNode = currentTag.previousSibling;
      while (prevNode) {
        if (
          prevNode.nodeType === Node.ELEMENT_NODE &&
          prevNode.classList &&
          prevNode.classList.contains("magic-tag")
        ) {
          foundPrevMagicTag = prevNode;
          break;
        }
        prevNode = prevNode.previousSibling;
      }
      if (foundPrevMagicTag) {
        targetPositionNode = foundPrevMagicTag;
      } else {
        if (parent.firstChild !== currentTag) {
          targetPositionNode = parent.firstChild;
        } else return;
      }
    } else {
      let foundNextMagicTag = null;
      let nextNode = currentTag.nextSibling;
      while (nextNode) {
        if (
          nextNode.nodeType === Node.ELEMENT_NODE &&
          nextNode.classList &&
          nextNode.classList.contains("magic-tag")
        ) {
          foundNextMagicTag = nextNode;
          break;
        }
        nextNode = nextNode.nextSibling;
      }

      if (foundNextMagicTag) {
        targetPositionNode = foundNextMagicTag.nextSibling;
      } else {
        if (parent.lastChild !== currentTag) {
          targetPositionNode = null;
        } else return;
      }
    }

    if (targetPositionNode === currentTag) return;

    if (targetPositionNode) {
      parent.insertBefore(currentTag, targetPositionNode);
    } else if (direction === "right" && parent.lastChild !== currentTag) {
      parent.appendChild(currentTag);
    } else if (direction === "left" && !targetPositionNode && parent.firstChild !== currentTag) {
      parent.insertBefore(currentTag, parent.firstChild);
    } else {
      return;
    }

    this._triggerEditorChange();
    this._showTagToolbar(currentTag, null, "edit");
  }

  _moveTagLeft() {
    this._moveTag("left");
  }

  _moveTagRight() {
    this._moveTag("right");
  }

  processAllBlocks() {
    if (!this.api || !this.api.blocks || typeof this.api.blocks.getBlocksCount !== "function")
      return;
    if (this.selectedTag) {
      const editorWrapper = this._getEditorWrapper();
      if (!editorWrapper.ownerDocument.body.contains(this.selectedTag)) {
        this._deselectAndClosePrevious();
      }
    }
    try {
      const blockCount = this.api.blocks.getBlocksCount();
      for (let i = 0; i < blockCount; i++) {
        const block = this.api.blocks.getBlockByIndex(i);
        if (block && block.holder) {
          this._processBlockElement(block.holder);
          this._ensureAllTagsIntegrity(block.holder);
        }
      }
    } catch (err) {
      console.error("MagicTagInlineTool: Error during block processing iteration:", err);
    }
  }

  _ensureAllTagsIntegrity(container) {
    const tags = container.querySelectorAll(".magic-tag");
    tags.forEach((tag) => this._ensureTagIntegrity(tag));
  }

  _processBlockElement(element) {
    const walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT, {
      acceptNode: (node) => {
        if (
          node.parentElement &&
          (node.parentElement.classList.contains("magic-tag") ||
            node.parentElement.closest('[contenteditable="false"]'))
        )
          return NodeFilter.FILTER_REJECT;
        if (node.nodeValue.includes("[[%") && node.nodeValue.includes("]]")) {
          if (node.parentElement && node.parentElement.classList.contains("magic-tag"))
            return NodeFilter.FILTER_REJECT;
          return NodeFilter.FILTER_ACCEPT;
        }
        return NodeFilter.FILTER_SKIP;
      },
    });
    const nodesToProcess = [];
    let node;
    while ((node = walker.nextNode())) nodesToProcess.push(node);
    for (let i = nodesToProcess.length - 1; i >= 0; i--) this._processTextNode(nodesToProcess[i]);
  }

  _processTextNode(textNode) {
    const text = textNode.nodeValue;
    const pattern = /\[\[%([\w.-]+)\]\]/g;
    if (!pattern.test(text)) return;
    pattern.lastIndex = 0;
    const fragment = document.createDocumentFragment();
    let lastIndex = 0,
      match,
      createdMagicTag = false;
    while ((match = pattern.exec(text)) !== null) {
      if (match.index > lastIndex)
        fragment.appendChild(document.createTextNode(text.substring(lastIndex, match.index)));
      const fullMatch = match[0];
      const tagNameInPattern = match[1];
      const tagConfig = this.tags.find(
        (t) => t.value === fullMatch || t.value === tagNameInPattern,
      );
      const span = document.createElement("span");
      span.classList.add("magic-tag");
      span.dataset.name = tagConfig ? tagConfig.name : fullMatch;
      span.textContent = tagConfig ? tagConfig.display : fullMatch;
      span.contentEditable = false;
      span.setAttribute("contenteditable", "false");
      span.setAttribute("data-editable", "false");
      fragment.appendChild(span);
      lastIndex = pattern.lastIndex;
      createdMagicTag = true;
    }
    if (lastIndex < text.length)
      fragment.appendChild(document.createTextNode(text.substring(lastIndex)));
    if (textNode.parentNode && createdMagicTag) {
      textNode.parentNode.replaceChild(fragment, textNode);
    }
  }

  _bindEditorEvents() {
    const editorWrapper = this._getEditorWrapper();
    if (!editorWrapper) return;

    this.delegatedTagClickListener = (event) => {
      const clickedTag = event.target.closest(".magic-tag");
      if (clickedTag && editorWrapper.contains(clickedTag)) {
        this._handleTagClick(event, clickedTag);
      }
    };

    // Add additional protection against tag modification
    this.delegatedTagProtectionListener = (event) => {
      const targetTag = event.target.closest(".magic-tag");
      if (targetTag && editorWrapper.contains(targetTag)) {
        // Prevent any modification events on magic tags
        if (event.type === "keydown" || event.type === "input" || event.type === "paste") {
          event.preventDefault();
          event.stopPropagation();
          return false;
        }
      }
    };

    editorWrapper.addEventListener("click", this.delegatedTagClickListener);
    editorWrapper.addEventListener("keydown", this.delegatedTagProtectionListener, true);
    editorWrapper.addEventListener("input", this.delegatedTagProtectionListener, true);
    editorWrapper.addEventListener("paste", this.delegatedTagProtectionListener, true);
  }

  _setupLiveTagProcessing() {
    const editorWrapper = this._getEditorWrapper();
    if (!editorWrapper || !window.MutationObserver) return;
    if (
      editorWrapper === document.body &&
      !this.button.closest('.codex-editor, .editorjs, [data-gramm="false"]')
    ) {
      console.warn("MagicTagInlineTool: MutationObserver is attached to document.body.");
    }
    this.observer = new MutationObserver((mutationsList) => {
      if (!this.api || !this.api.blocks || typeof this.api.blocks.getBlocksCount !== "function")
        return;
      let shouldProcess = false;
      for (const mutation of mutationsList) {
        if (
          editorWrapper === document.body &&
          mutation.target.closest &&
          !mutation.target.closest('.codex-editor, .editorjs, [data-gramm="false"]')
        ) {
          let targetInEditor = false;
          let current = mutation.target;
          while (current && current !== document.body) {
            if (
              current.matches &&
              current.matches('.codex-editor, .editorjs, [data-gramm="false"]')
            ) {
              targetInEditor = true;
              break;
            }
            current = current.parentElement;
          }
          if (!targetInEditor) continue;
        }
        if (
          mutation.target.closest &&
          (mutation.target.closest(".magic-tag-toolbar") ||
            mutation.target.closest(".magic-tag-selection-dialog"))
        )
          continue;
        if (
          mutation.target.nodeType === Node.ELEMENT_NODE &&
          mutation.target.classList.contains("magic-tag")
        )
          continue;
        if (
          mutation.type === "characterData" &&
          (!mutation.target.parentElement ||
            !mutation.target.parentElement.classList.contains("magic-tag"))
        ) {
          shouldProcess = true;
          break;
        }
        if (mutation.type === "childList" && mutation.addedNodes.length > 0) {
          for (const addedNode of mutation.addedNodes) {
            if (
              addedNode.nodeType === Node.ELEMENT_NODE &&
              addedNode.classList &&
              addedNode.classList.contains("magic-tag")
            )
              continue;
            if (
              addedNode.nodeType === Node.TEXT_NODE ||
              (addedNode.textContent && addedNode.textContent.includes("[[%"))
            ) {
              const parentOfAddedText = addedNode.parentElement;
              const mutationTargetIsMagicTag =
                mutation.target.classList && mutation.target.classList.contains("magic-tag");
              const parentOfAddedNodeIsMagicTag =
                parentOfAddedText && parentOfAddedText.classList.contains("magic-tag");
              if (!mutationTargetIsMagicTag && !parentOfAddedNodeIsMagicTag) {
                shouldProcess = true;
                break;
              }
            }
          }
          if (shouldProcess) break;
        }
      }
      if (shouldProcess) this.processAllBlocks();
    });
    this.observer.observe(editorWrapper, {
      childList: true,
      subtree: true,
      characterData: true,
      characterDataOldValue: false,
    });
  }

  _documentListeners = new Map();
  _addDocumentListener(handler) {
    const handlerName = handler.name || handler.toString();
    if (!this._documentListeners.has(handlerName)) {
      document.addEventListener("click", handler, true);
      this._documentListeners.set(handlerName, handler);
    }
  }
  _removeDocumentListener(handler) {
    const handlerName = handler.name || handler.toString();
    if (this._documentListeners.has(handlerName)) {
      document.removeEventListener("click", handler, true);
      this._documentListeners.delete(handlerName);
    }
  }

  _triggerEditorChange() {
    try {
      if (
        this.api &&
        this.api.blocks &&
        typeof this.api.blocks.getCurrentBlockIndex === "function"
      ) {
        const blockIndex = this.api.blocks.getCurrentBlockIndex();
        if (blockIndex > -1) {
          const block = this.api.blocks.getBlockByIndex(blockIndex);
          if (block && block.holder) {
            block.holder.dispatchEvent(new Event("input", { bubbles: true, cancelable: true }));
            return;
          }
        }
      }
      const editorWrapper = this._getEditorWrapper();
      if (editorWrapper && editorWrapper !== document.body) {
        editorWrapper.dispatchEvent(new Event("input", { bubbles: true, cancelable: true }));
      }
    } catch (err) {
      console.warn("MagicTagInlineTool: Unable to notify editor about changes optimally.", err);
    }
  }

  destroy() {
    if (this.observer) {
      this.observer.disconnect();
      this.observer = null;
    }

    if (this._editorWrapper && this.delegatedTagClickListener) {
      this._editorWrapper.removeEventListener("click", this.delegatedTagClickListener);
      this.delegatedTagClickListener = null;
    }

    if (this._editorWrapper && this.delegatedTagProtectionListener) {
      this._editorWrapper.removeEventListener("keydown", this.delegatedTagProtectionListener, true);
      this._editorWrapper.removeEventListener("input", this.delegatedTagProtectionListener, true);
      this._editorWrapper.removeEventListener("paste", this.delegatedTagProtectionListener, true);
      this.delegatedTagProtectionListener = null;
    }

    this._removeDocumentListener(this._closeTagToolbarListener);
    this._documentListeners.forEach((handler) => {
      document.removeEventListener("click", handler, true);
    });
    this._documentListeners.clear();

    if (this.tagToolbar) {
      if (this.tagToolbarSelect) {
        this.tagToolbarSelect.removeEventListener("change", this._boundHandleToolbarSelectChange);
      }
      this._toolbarButtonListeners.forEach((cleanup) => cleanup());
      this._toolbarButtonListeners = [];

      if (this.tagToolbar.parentNode) {
        this.tagToolbar.parentNode.removeChild(this.tagToolbar);
      }

      document.querySelectorAll(".magic-tag-toolbar").forEach((el) => {
        if (el.parentNode) el.parentNode.removeChild(el);
      });
    }

    this.tagToolbar = null;
    this.tagToolbarSelect = null;
    this.tagToolbarMoveLeftButton = null;
    this.tagToolbarMoveRightButton = null;
    this.tagToolbarInfoButton = null;
    this.tagToolbarRemoveButton = null;

    this.selectedTag = null;
    this.currentRangeForInsertion = null;
    this._editorWrapper = null;
    this.button = null;
    this.api = null;
    this.config = null;
  }
}
