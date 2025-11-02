/**
 * Rules for context menu items (show/hide)
 * {name}Rule
 */
export default {
  methods: {
    /**
     * Open - menu item status - show or hide
     * @returns {boolean}
     */
    openRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        !this.multiSelect &&
        this.firstItemType === "dir" &&
        permissions.value.includes("media-sources-read")
      );
    },

    /**
     * Play audio - menu item status - show or hide
     * @returns {boolean}
     */
    audioPlayRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        this.selectedItems.every((elem) => elem.type === "file") &&
        this.selectedItems.every((elem) => this.canAudioPlay(elem.extension)) &&
        permissions.value.includes("media-sources-read")
      );
    },

    /**
     * Play video - menu item status - show or hide
     * @returns {boolean}
     */
    videoPlayRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        !this.multiSelect &&
        this.canVideoPlay(this.selectedItems[0]?.extension) &&
        permissions.value.includes("media-sources-read")
      );
    },

    /**
     * View - menu item status - show or hide
     * @returns {boolean|*}
     */
    viewRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        !this.multiSelect &&
        this.firstItemType === "file" &&
        this.canView(this.selectedItems[0].extension) &&
        permissions.value.includes("media-sources-read")
      );
    },

    /**
     * Edit - menu item status - show or hide
     * @returns {boolean|*}
     */
    editRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        !this.multiSelect &&
        this.firstItemType === "file" &&
        this.canEdit(this.selectedItems[0].extension) &&
        permissions.value.includes("media-sources-update")
      );
    },

    /**
     * Edit Image - menu item status - show or hide
     * @returns {boolean|*}
     */
    editImageRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        !this.multiSelect &&
        this.firstItemType === "file" &&
        this.canView(this.selectedItems[0].extension) &&
        permissions.value.includes("media-sources-update")
      );
    },

    /**
     * Select - menu item status - show or hide
     * @returns {boolean|null}
     */
    selectRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        !this.multiSelect &&
        this.firstItemType === "file" &&
        this.$store.state.fm.fileCallback &&
        permissions.value.includes("media-sources-update")
      );
    },

    /**
     * Download - menu item status - show or hide
     * @returns {boolean}
     */
    downloadRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        !this.multiSelect &&
        this.firstItemType === "file" &&
        permissions.value.includes("media-sources-read")
      );
    },

    /**
     * Copy - menu item status - show or hide
     * @returns {boolean}
     */
    copyRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return permissions.value.includes("media-sources-update");
    },

    /**
     * Cut - menu item status - show or hide
     * @returns {boolean}
     */
    cutRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return permissions.value.includes("media-sources-update");
    },

    /**
     * Rename - menu item status - show or hide
     * @returns {boolean}
     */
    renameRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return !this.multiSelect && permissions.value.includes("media-sources-update");
    },

    /**
     * Paste - menu item status - show or hide
     * @returns {boolean}
     */
    pasteRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        !!this.$store.state.fm.filemanager.clipboard.type &&
        permissions.value.includes("media-sources-update")
      );
    },

    /**
     * Zip - menu item status - show or hide
     * @returns {boolean}
     */
    zipRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        (this.selectedDiskDriver === "local" || this.selectedDiskDriver === "s3") &&
        permissions.value.includes("media-sources-update") &&
        !this.multiSelect
      );
    },

    /**
     * Unzip - menu item status - show or hide
     * @returns {boolean}
     */
    unzipRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return (
        this.selectedDiskDriver === "local" ||
        (this.selectedDiskDriver === "s3" &&
          !this.multiSelect &&
          this.firstItemType === "file" &&
          this.isZip(this.selectedItems[0].extension) &&
          permissions.value.includes("media-sources-update"))
      );
    },

    /**
     * Delete - menu item status - show or hide
     * @returns {boolean}
     */
    deleteRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return permissions.value.includes("media-sources-delete");
    },

    /**
     * Properties - menu item status - show or hide
     * @returns {boolean}
     */
    propertiesRule() {
      const { permissions } = storeToRefs(useAuthStore());
      return !this.multiSelect && permissions.value.includes("media-sources-read");
    },
  },
};
