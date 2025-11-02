export default {
  methods: {
    /**
     * Open folder
     */
    openAction() {
      // select directory
      this.$store.dispatch(`fm/content/selectDirectory`, {
        path: this.selectedItems[0].path,
        history: true,
      });
    },

    /**
     * Play music or video
     */
    audioPlayAction() {
      // show player modal
      this.$store.commit("fm/modal/setModalState", {
        modalName: "AudioPlayer",
        show: true,
      });
    },

    /**
     * Play music or video
     */
    videoPlayAction() {
      // show player modal
      this.$store.commit("fm/modal/setModalState", {
        modalName: "VideoPlayer",
        show: true,
      });
    },

    /**
     * View file
     */
    viewAction() {
      // show image in viewer (preview mode)
      this.$store.commit("fm/modal/setModalState", {
        modalName: "ImageViewer",
        show: true,
      });
    },

    /**
     * Edit image
     */
    editImageAction() {
      // show image in editor
      this.$store.commit("fm/modal/setModalState", {
        modalName: "Preview",
        show: true,
      });
    },

    /**
     * Edit file
     */
    editAction() {
      // show text file
      this.$store.commit("fm/modal/setModalState", {
        modalName: "TextEdit",
        show: true,
      });
    },

    /**
     * Select file
     */
    selectAction() {
      // file callback
      this.$store
        .dispatch("fm/url", {
          disk: this.selectedDisk,
          path: this.selectedItems[0].path,
        })
        .then((response) => {
          if (response.data.result.status === "success") {
            this.$store.state.fm.fileCallback(response.data.url);
          }
        });
    },

    /**
     * Download file
     */
    downloadAction() {
      const api = useAPI();

      api
        .get(
          `media-manager/file-manager/download?disk=${this.selectedDisk}&path=${this.selectedItems[0].path}`,
          { responseType: "arrayBuffer" },
        )
        .then((response) => {
          const tempLink = document.createElement("a");
          tempLink.style.display = "none";
          tempLink.setAttribute("download", this.selectedItems[0].basename);
          tempLink.href = window.URL.createObjectURL(
            new Blob([response], { type: response.headers }),
          );
          document.body.appendChild(tempLink);
          tempLink.click();
          document.body.removeChild(tempLink);
        });
    },

    /**
     * Copy selected items
     */
    copyAction() {
      // add selected items to the clipboard
      this.$store.dispatch("fm/content/toClipboard", "copy", { root: true });
    },

    /**
     * Cut selected items
     */
    cutAction() {
      // add selected items to the clipboard
      this.$store.dispatch("fm/content/toClipboard", "cut");
    },

    /**
     * Rename selected item
     */
    renameAction() {
      // show modal - rename
      this.$store.commit("fm/modal/setModalState", {
        modalName: "Rename",
        show: true,
      });
    },

    /**
     * Paste copied or cut items
     */
    pasteAction() {
      // paste items in the selected folder
      this.$store.dispatch("fm/content/paste");
    },

    /**
     * Zip selected files
     */
    zipAction() {
      // show modal - Zip
      this.$store.commit("fm/modal/setModalState", {
        modalName: "Zip",
        show: true,
      });
    },

    /**
     * Unzip selected archive
     */
    unzipAction() {
      // show modal - Unzip
      this.$store.commit("fm/modal/setModalState", {
        modalName: "Unzip",
        show: true,
      });
    },

    /**
     * Delete selected items
     */
    deleteAction() {
      // show modal - delete
      this.$store.commit("fm/modal/setModalState", {
        modalName: "Delete",
        show: true,
      });
    },

    /**
     * Show properties for selected items
     */
    propertiesAction() {
      // show modal - properties
      this.$store.commit("fm/modal/setModalState", {
        modalName: "Properties",
        show: true,
      });
    },
    openPDF(context, { disk, path }) {
      const win = window.open();

      GET.getFileArrayBuffer(disk, path).then((response) => {
        const blob = new Blob([response], { type: "application/pdf" });

        win.document.write(
          `<iframe src="${URL.createObjectURL(
            blob,
          )}" allowfullscreen height="100%" width="100%"></iframe>`,
        );
      });
    },
  },
};
