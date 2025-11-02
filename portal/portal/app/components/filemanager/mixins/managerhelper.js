// Event bus
// import EventBus from './../../../eventBus';

export default {
  data() {
    return {
      downloadprogress: [],
    };
  },
  computed: {
    /**
     * Selected disk for this manager
     * @returns {default.computed.selectedDisk|(function())|default.selectedDisk|null}
     */
    selectedDisk() {
      return this.$store.state.fm.content.selectedDisk;
    },

    /**
     * Selected directory for this manager
     * @returns {default.computed.selectedDirectory|(function())|default.selectedDirectory|null}
     */
    selectedDirectory() {
      return this.$store.state.fm.content.selectedDirectory;
    },

    /**
     * Files list for selected directory
     * @returns {*}
     */
    files() {
      return this.$store.getters[`fm/content/files`];
    },

    /**
     * Directories list for selected directory
     * @returns {*}
     */
    directories() {
      return this.$store.getters[`fm/content/directories`];
    },

    sizes() {
      return this.$store.state.fm.content.sizes;
    },

    /**
     * Selected files and folders
     * @returns {default.computed.selected|(function())|selected|{directories, files}|string|*|boolean}
     */
    selected() {
      return this.$store.state.fm.content.selected;
    },

    /**
     * ACL On/Off
     */
    acl() {
      return this.$store.state.fm.settings.acl;
    },

    /**
     * Check if current path is at root level
     * @return {boolean}
     */
    isRootPath() {
      return this.$store.state.fm.content.selectedDirectory === null;
    },
  },
  watch: {
    downloadprogress: {
      deep: true,
      immediate: true,
      handler(v) {
        return v;
      },
    },
  },
  methods: {
    /**
     * Load selected directory and show files
     * @param path
     */
    selectDirectory(path) {
      if (path === 0) {
        path = "/";
      }
      this.$store.dispatch(`fm/content/selectDirectory`, {
        path,
        history: true,
      });
    },

    /**
     * Level up directory
     */
    levelUp() {
      // if this a not root directory
      if (this.selectedDirectory) {
        // calculate up directory path
        const pathUp = this.selectedDirectory.split("/").slice(0, -1).join("/");

        // load directory
        this.$store.dispatch(`fm/content/selectDirectory`, {
          path: pathUp || null,
          history: true,
        });
      }
    },

    /**
     * Check item - selected
     * @param type
     * @param path
     */
    checkSelect(type, path) {
      return this.selected[type].includes(path);
    },

    /**
     * Select items in list (files + folders)
     * @param type
     * @param path
     * @param event
     */
    selectItem(type, path, event) {
      // search in selected array
      const alreadySelected = this.selected[type].includes(path);

      // if pressed Ctrl -> multi select
      if (event?.ctrlKey || event == "add") {
        if (!alreadySelected) {
          // add new selected item
          this.$store.commit(`fm/content/setSelected`, { type, path });
        } else {
          // remove selected item
          this.$store.commit(`fm/content/removeSelected`, { type, path });
        }
      }

      // single select
      if (!event.ctrlKey && event !== "add" && !alreadySelected) {
        this.$store.dispatch(`fm/content/changeSelected`, { type, path });
      } else {
        this.$store.dispatch(`fm/content/changeSelected`, { type, path });
      }
    },

    /**
     * Show context menu
     * @param item
     * @param event
     */
    contextMenu(item, event) {
      const eventStore = useEventStore();

      // el type
      const type = item.type === "dir" ? "directories" : "files";
      // search in selected array
      const alreadySelected = this.selected[type].includes(item.path);

      // select this element
      if (!alreadySelected) {
        // select item
        this.$store.dispatch(`fm/content/changeSelected`, {
          type,
          path: item.path,
        });
      }

      // create event
      eventStore.emit("contextMenu", event);
    },
    pasteMenu(event) {
      const eventStore = useEventStore();
      this.$store.dispatch(`fm/content/changeSelected`, {
        type: "directories",
        path: this.$store.state.fm.content.selectedDirectory,
      });
      eventStore.emit("pasteMenu", event);
    },

    /**
     * Create a correct path from different pathtypesreturned from backend
     * @param {*} file
     * @returns
     */
    createPath(file) {
      let path;
      if (file.path.startsWith("//")) {
        path = file.path.substring(2);
      } else if (file.path.includes(file.name)) {
        path = file.path;
      } else if (file.path.endsWith("/")) {
        path = file.path + file.name;
      } else {
        path = file.path + "/" + file.name;
      }
      return path;
    },

    /**
     * Select and Action
     * @param path
     * @param extension
     * @param {*} disk
     * @param {*} i
     */
    selectAction(path, extension, disk) {
      const api = useAPI();
      const { handleError } = useMessageHandler();
      const { addToast } = useToastStore();

      this.$store.commit("fm/modal/setModalState", {
        modalName: "PreviewLoader",
        show: true,
      });

      // if is set fileCallback
      if (this.$store.state.fm.fileCallback) {
        this.$store
          .dispatch("fm/filemanager/url", {
            disk: disk ? disk : this.selectedDisk,
            path,
          })
          .then((response) => {
            if (response.data.result.status === "success") {
              this.$store.state.fm.fileCallback(response.data.url);
            }
          });

        return;
      }

      // if extension not defined
      if (!extension) {
        this.$store.commit("fm/modal/setModalState", {
          modalName: "",
          show: false,
        });
      }

      this.$store.commit("fm/content/setSelected", { type: "files", path });
      this.$store.commit("fm/content/setDisk", disk ? disk : this.selectedDisk);
      // show, play..
      if (this.$store.state.fm.settings.imageExtensions.includes(extension.toLowerCase())) {
        // Ensure the file is selected before opening ImageViewer
        // Use commit for immediate synchronous update instead of dispatch
        this.$store.commit("fm/content/resetSelected");
        this.$store.commit("fm/content/setSelected", { type: "files", path });

        // show image in viewer (preview mode)
        this.$store.commit("fm/modal/setModalState", {
          modalName: "ImageViewer",
          show: true,
        });
      } else if (
        Object.keys(this.$store.state.fm.settings.textExtensions).includes(extension.toLowerCase())
      ) {
        // show text file
        this.$store.commit("fm/modal/setModalState", {
          modalName: "TextEdit",
          show: true,
        });
      } else if (this.$store.state.fm.settings.audioExtensions.includes(extension.toLowerCase())) {
        // show player modal
        this.$store.commit("fm/modal/setModalState", {
          modalName: "AudioPlayer",
          show: true,
        });
      } else if (this.$store.state.fm.settings.videoExtensions.includes(extension.toLowerCase())) {
        // show player modal
        this.$store.commit("fm/modal/setModalState", {
          modalName: "VideoPlayer",
          show: true,
        });
      } else if (extension.toLowerCase() === "pdf") {
        api
          .get(
            `media-manager/file-manager/download?disk=${disk ? disk : this.selectedDisk}&path=${path} `,
            {
              responseType: "arrayBuffer",
              onDownloadProgress: (percentCompleted) => {
                this.downloadprogress.i = percentCompleted;
              },
            },
          )
          .then((res) => {
            const fileURL = window.URL.createObjectURL(
              new Blob([res], { type: "application/pdf" }),
            );

            this.$store.commit("fm/filemanager/setPDF", fileURL);
            this.downloadprogress.i = null;

            // show PDF modal
            this.$store.commit("fm/modal/setModalState", {
              modalName: "PDFViewer",
              show: true,
            });
          })
          .catch((error) => console.error(error));
      } else if (extension.toLowerCase() === "zip") {
        // show PDF modal
        this.$store.commit("fm/modal/setModalState", {
          modalName: "",
          show: false,
        });

        api
          .get(
            `media-manager/file-manager/download?disk=${disk ? disk : this.selectedDisk}&path=${path}`,
            {
              responseType: "arrayBuffer",
              onDownloadProgress: (percentCompleted) => {
                this.downloadprogress.i = percentCompleted;
              },
            },
          )
          .then((response) => {
            const tempLink = document.createElement("a");
            tempLink.style.display = "none";
            tempLink.setAttribute("download", path);
            const objectUrl = window.URL.createObjectURL(
              new Blob([response], { type: response.headers }),
            );
            tempLink.href = objectUrl;
            document.body.appendChild(tempLink);
            tempLink.click();
            document.body.removeChild(tempLink);
            this.downloadprogress.i = null;
            window.URL.revokeObjectURL(objectUrl);
            addToast({
              message: this.$t("The file is downloaded to your device."),
              type: "info",
            });
            this.$store.commit("fm/modal/setModalState", {
              modalName: "",
              show: false,
            });
          })
          .catch((error) => handleError(error));
      } else {
        api
          .get(
            `media-manager/file-manager/download?disk=${disk ? disk : this.selectedDisk}&path=${path}`,
            {
              responseType: "arrayBuffer",
              onDownloadProgress: (percentCompleted) => {
                this.downloadprogress.i = percentCompleted;
              },
            },
          )
          .then((response) => {
            const tempLink = document.createElement("a");
            tempLink.style.display = "none";
            tempLink.setAttribute("download", path);
            const objectUrl = window.URL.createObjectURL(
              new Blob([response], { type: response.headers }),
            );
            tempLink.href = objectUrl;
            document.body.appendChild(tempLink);
            tempLink.click();
            document.body.removeChild(tempLink);
            window.URL.revokeObjectURL(objectUrl);
            this.downloadprogress.i = null;
            addToast({
              message: this.$t("The file is downloaded to your device."),
              type: "info",
            });
            this.$store.commit("fm/modal/setModalState", {
              modalName: "",
              show: false,
            });
          })
          .catch((error) => handleError(error));
      }
    },
  },
};
