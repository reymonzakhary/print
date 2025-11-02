export default {
  state: () => ({
    activeManager: "left",
    filesView: "list",
    viewFolders: false,
    PDFtoShow: null,
    /**
     * Clipboard
     * Operation type - copy || cut
     */
    clipboard: {
      type: null,
      disk: null,
      directories: [],
      files: [],
    },
    // available disks
    disks: [],
    // file callback for ckeditor, ...
    fileCallback: null,
    // full screen mode
    fullScreen: false,
  }),
  mutations: {
    setPDF(state, PDF) {
      state.PDFtoShow = PDF;
    },
    setDisks(state, disks) {
      state.disks = disks;
    },
    setView(state, view) {
      state.filesView = view;
    },
    setClipboard(state, { type, disk, directories, files }) {
      state.clipboard.type = type;
      state.clipboard.disk = disk;
      state.clipboard.directories = directories;
      state.clipboard.files = files;
    },
    truncateClipboard(state, { type, path }) {
      const itemIndex = state.clipboard[type].indexOf(path);
      if (itemIndex !== -1) state.clipboard[type].splice(itemIndex, 1);
      if (!state.clipboard.directories.length && !state.clipboard.files.length) {
        state.clipboard.type = null;
      }
    },
    resetClipboard(state) {
      state.clipboard.type = null;
      state.clipboard.disk = null;
      state.clipboard.directories = [];
      state.clipboard.files = [];
    },
    setActiveManager(state, managerName) {
      state.activeManager = managerName;
    },
    setFileCallBack(state, callback) {
      state.fileCallback = callback;
    },
    screenToggle(state) {
      state.fullScreen = !state.fullScreen;
    },
    toggleViewFolders(state, bool) {
      state.viewFolders = bool;
    },
    resetState(state) {
      state.activeManager = "left";
      state.clipboard = {
        type: null,
        disk: null,
        directories: [],
        files: [],
      };
      state.disks = [];
      state.fileCallback = null;
      state.fullScreen = false;
    },
  },
  actions: {
    initializeApp({ state, commit, getters, dispatch, rootState, rootGetters }) {
      const api = useAPI();
      api.get("media-manager/file-manager/initialize").then((response) => {
        if (response.result.status === "success") {
          commit("fm/settings/initSettings", response.config, {
            root: true,
          });
          commit("setDisks", response.config.disks);
          let leftDisk = response.config.leftDisk ? response.config.leftDisk : getters.diskList[0];
          let rightDisk = response.config.rightDisk
            ? response.config.rightDisk
            : getters.diskList[0];

          // paths
          let leftPath = response.config.leftPath;
          let rightPath = response.config.rightPath;

          // find disk and path settings in the URL
          if (window.location.search) {
            const params = new URLSearchParams(window.location.search);
            if (params.get("leftDisk")) {
              leftDisk = params.get("leftDisk");
            }
            if (params.get("rightDisk")) {
              rightDisk = params.get("rightDisk");
            }
            if (params.get("leftPath")) {
              leftPath = params.get("leftPath");
            }
            if (params.get("rightPath")) {
              rightPath = params.get("rightPath");
            }
          }
          const route = useRoute();
          if (route.query.disk) {
            commit("fm/content/setDisk", route.query.disk, {
              root: true,
            });
          } else {
            commit("fm/content/setDisk", "tenancy", {
              root: true,
            });
          }

          // if leftPath not null
          if (leftPath) {
            commit("left/setSelectedDirectory", leftPath);
            commit("left/addToHistory", leftPath);
          }
          dispatch("getLoadContent", {
            manager: "left",
            disk: leftDisk,
            path: leftPath,
          });

          // if selected left and right managers
          if (rootState.fm.settings.windowsConfig === 3) {
            commit("right/setDisk", rightDisk);

            // if rightPath not null
            if (rightPath) {
              commit("right/setSelectedDirectory", rightPath);
              commit("right/addToHistory", rightPath);
            }
            dispatch("getLoadContent", {
              manager: "right",
              disk: rightDisk,
              path: rightPath,
            });
          } else if (rootState.fm.settings.windowsConfig === 2) {
            // if selected left manager and directories tree
            // init directories tree
            const selectedDisk = rootGetters["fm/content/selectedDisk"];
            dispatch("fm/tree/initTree", selectedDisk, {
              root: true,
            }).then(() => {
              if (leftPath) {
                // reopen folders if path not null
                dispatch("tree/reopenPath", leftPath);
              }
            });
          }
        }
      });
    },
    getLoadContent(context, { disk }) {
      const api = useAPI();
      api.get(`media-manager/file-manager/content?disk=${disk}`).then((response) => {
        if (response.result && response.result.status === "success") {
          context.commit(`fm/content/setDirectoryContent`, response, {
            root: true,
          });
        }
      });
    },
    getSearchContent(context, { disk, search, page, per_page }) {
      const api = useAPI();
      api
        .get(
          `media-manager/file-manager/content/search?disk=${disk}&name=${search}&page=${page}&per_page=${per_page}`,
        )
        .then((response) => {
          // if (response.data.result && response.data.result.status === "success") {
          context.commit(`fm/content/setSearchResults`, response.data, {
            root: true,
          });
          context.commit(`pagination/set_pagination`, response.meta, {
            root: true,
          });
          // }
        });
    },
    selectDisk({ commit, dispatch }, { disk }) {
      const api = useAPI();
      api.get(`media-manager/file-manager/select-disk?disk=${disk}`).then((response) => {
        // if disk exist => change disk
        if (response.result.status === "success") {
          // set disk name
          commit(`fm/content/setDisk`, disk, {
            root: true,
          });

          // reset history
          // commit("/fm/content/resetHistory", { root: true });

          // reinitialize tree if directories tree is shown
          // if (rootState.settings.windowsConfig === 2) {
          dispatch("fm/tree/initTree", disk, {
            root: true,
          });
          // }

          // download content for root path
          dispatch(
            `fm/content/selectDirectory`,
            {
              path: "/",
              history: false,
            },
            {
              root: true,
            },
          );
        }
      });
    },
    getFile(context, { disk, path }) {
      // return GET.getFile(disk, path);
      let newPath = encodeURIComponent(path);
      const api = useAPI();
      return api.get(`media-manager/file-manager/download?disk=${disk}&path=${newPath}`);
    },
    updateFile({ getters, dispatch }, formData) {
      const api = useAPI();
      return api
        .post("media-manager/file-manager/update-file", formData, {
          isFormData: true,
        })
        .then((response) => {
          // update file list
          dispatch("updateContent", {
            response,
            oldDir: getters.selectedDirectory,
            commitName: "updateFile",
            type: "file",
          });
          return response;
        });
    },
    url(store, { disk, path }) {
      let newPath = encodeURIComponent(path);
      const api = useAPI();
      return api.get(`media-manager/file-manager/url?disk=${disk}&path=${newPath}`);
    },
    repeatSort({ state, dispatch, rootState }) {
      dispatch(
        `fm/content/sortBy`,
        {
          field: rootState.content.sort.field,
          direction: rootState.content.sort.direction,
        },
        {
          root: true,
        },
      );
    },
    updateContent(
      { state, commit, getters, dispatch, rootState },
      { response, oldDir, commitName, type },
    ) {
      // if operation success
      if (
        response.result.status === "success"
        // && oldDir === getters.selectedDirectory
      ) {
        // add/update file/folder in to the files/folders list
        commit(`fm/content/${commitName}`, response[type], {
          root: true,
        });

        // repeat sort
        // dispatch("repeatSort", state.activeManager);

        // if tree module is showing
        if (type === "directory" && rootState.fm.settings.windowsConfig === 2) {
          // update tree module
          dispatch(
            "fm/tree/addToTree",
            {
              parentPath: oldDir,
              newDirectory: response.tree,
            },
            {
              root: true,
            },
          );

          // if both managers show the same folder
        } else if (
          rootState.fm.settings.windowsConfig === 3 &&
          rootState.fm.left.selectedDirectory === state.right.selectedDirectory &&
          rootState.fm.left.selectedDisk === state.right.selectedDisk
        ) {
          // add/update file/folder in to the files/folders list (inactive manager)
          commit(`${getters.inactiveManager}/${commitName}`, response.data[type]);
          // repeat sort
          dispatch("repeatSort", getters.inactiveManager);
        }
      }
    },
    resetState({ state, commit }) {
      // left manager
      commit("left/setDisk", null);
      commit("left/setSelectedDirectory", null);
      commit("left/setDirectoryContent", {
        directories: [],
        files: [],
      });
      commit("left/resetSelected");
      commit("left/resetSortSettings");
      commit("left/resetHistory");
      commit("left/setView", "table");
      // modals
      commit("modal/clearModal");
      // messages
      commit("messages/clearActionResult");
      commit("messages/clearProgress");
      commit("messages/clearLoading");
      commit("messages/clearErrors");
      if (state.settings.windowsConfig === 3) {
        // right manager
        commit("right/setDisk", null);
        commit("right/setSelectedDirectory", null);
        commit("right/setDirectoryContent", {
          directories: [],
          files: [],
        });
        commit("right/resetSelected");
        commit("right/resetSortSettings");
        commit("right/resetHistory");
        commit("right/setView", "table");
      } else if (state.settings.windowsConfig === 2) {
        // tree
        commit("tree/cleanTree");
        commit("tree/clearTempArray");
      }
      commit("resetState");
    },
  },
  getters: {
    diskList(state) {
      return Object.keys(state.disks);
    },
    inactiveManager(state) {
      return state.activeManager === "left" ? "right" : "left";
    },
  },
};
