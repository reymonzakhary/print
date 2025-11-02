export default {
  state: () => ({
    // selected disk
    selectedDisk: null,
    // selected directory
    selectedDirectory: null,
    // Directories in the selected directory
    directories: [],
    // files in the selected directory
    files: [],
    sizes: [],
    searchresults: [],
    // selected files and folders
    selected: {
      directories: [],
      files: [],
    },
    // sorting settings
    sort: {
      field: "name",
      direction: "up",
    },
    // history
    history: [null],
    // history pointer
    historyPointer: 0,
    // view type - table or grid - (default - table)
    viewType: "table",
    contentLoading: true,
    // uploads
    chunks: [],
    uploaded: 0,
    originalFile: {},
  }),
  mutations: {
    setDisk(state, disk) {
      state.selectedDisk = disk;
    },
    setDirectoryContent(state, data) {
      state.directories = data.directories;
      state.files = data.files;
      state.sizes = data.sizes;
    },
    setFiles(state, files) {
      state.files = files;
    },
    emptyFiles(state) {
      state.files = [];
    },
    setSearchResults(state, data) {
      state.searchresults = data;
    },
    setSelectedDirectory(state, directory) {
      state.selectedDirectory = directory;
    },
    setSelected(state, { type, path }) {
      // if path starts with / remove it
      const sanitizedPath = path.startsWith("/") ? path.slice(1) : path;
      if (state.selected[type].indexOf(sanitizedPath) === -1) {
        state.selected[type].push(sanitizedPath);
      }
    },
    removeSelected(state, { type, path }) {
      const sanitizedPath = path.replace(/^\//, "");
      const itemIndex = state.selected[type].indexOf(sanitizedPath);
      if (itemIndex !== -1) state.selected[type].splice(itemIndex, 1);
    },
    deleteItems(state, items) {
      items.forEach((item) => {
        if (item.type === "dir") {
          state.directories = state.directories.filter((el) => {
            return el.path !== item.path;
          });
        }
        if (item.type === "file") {
          state.files = state.files.filter((el) => {
            return el.path !== item.path;
          });
        }
      });
    },
    resetSelected(state) {
      state.selected = {
        directories: [],
        files: [],
      };
    },
    addNewFile(state, newFile) {
      state.files.push(newFile);
    },
    updateFile(state, file) {
      const itemIndex = state.files.findIndex((el) => el.basename === file.basename);
      if (itemIndex !== -1) state.files[itemIndex] = file;
    },
    addNewDirectory(state, newDirectory) {
      state.directories.push(newDirectory);
    },
    pointerBack(state) {
      state.historyPointer -= 1;
    },
    pointerForward(state) {
      state.historyPointer += 1;
    },
    addToHistory(state, path) {
      if (state.historyPointer < state.history.length - 1) {
        // erase next elements in the history
        state.history.splice(state.historyPointer + 1, Number.MAX_VALUE);
      }
      // add new path
      state.history.push(path);
      // change history pointer
      state.historyPointer += 1;
    },
    resetHistory(state) {
      state.history = [null];
      state.historyPointer = 0;
    },
    setView(state, type) {
      state.viewType = type;
    },
    setSortField(state, field) {
      state.sort.field = field;
    },
    setSortDirection(state, direction) {
      state.sort.direction = direction;
    },
    resetSortSettings(state) {
      state.sort.field = "name";
      state.sort.direction = "up";
    },
    sortByName(state) {
      if (state.sort.direction === "up") {
        state.directories.sort((a, b) => a.basename.localeCompare(b.basename));
        state.files.sort((a, b) => a.basename.localeCompare(b.basename));
      } else {
        state.directories.sort((a, b) => b.basename.localeCompare(a.basename));
        state.files.sort((a, b) => b.basename.localeCompare(a.basename));
      }
    },
    sortBySize(state) {
      if (state.sort.direction === "up") {
        state.files.sort((a, b) => a.size - b.size);
      } else {
        state.files.sort((a, b) => b.size - a.size);
      }
    },
    sortByType(state) {
      if (state.sort.direction === "up") {
        state.files.sort((a, b) => a.extension.localeCompare(b.extension));
      } else {
        state.files.sort((a, b) => b.extension.localeCompare(a.extension));
      }
    },
    sortByDate(state) {
      if (state.sort.direction === "up") {
        state.directories.sort((a, b) => a.timestamp - b.timestamp);
        state.files.sort((a, b) => a.timestamp - b.timestamp);
      } else {
        state.directories.sort((a, b) => b.timestamp - a.timestamp);
        state.files.sort((a, b) => b.timestamp - a.timestamp);
      }
    },
    toggleLoading(state, status) {
      state.contentLoading = status;
    },
    pushChunk(state, chunk) {
      state.chunks.push(chunk);
    },
    clearChunks(state) {
      state.chunks = [];
    },
    setOriginalFile(state, file) {
      state.originalFile = file;
    },
  },
  actions: {
    async refreshAll({ state, getters, dispatch, rootState }) {
      if (rootState.fm.settings.windowsConfig === 2) {
        // refresh tree
        await dispatch("fm/tree/initTree", state.selectedDisk, {
          root: true,
        })
          .then(
            () =>
              // Promise.all([
              // reopen folders if need
              dispatch("fm/tree/reopenPath", getters.selectedDirectory, {
                root: true,
              }),
            // refresh manager/s
            dispatch("refreshManagers"),
            // ])
          )
          .catch((error) => {
            dispatch("toast/handle_error", error, {
              root: true,
            });
          });
      }
      // refresh manager/s
      return dispatch("refreshManagers");
    },
    refreshManagers({ dispatch, rootState }) {
      // select what needs to be an updated
      if (rootState.fm.settings.windowsConfig === 3) {
        return Promise.all([
          // manager
          dispatch("refreshDirectory"),
        ]);
      }

      // only left manager
      return dispatch("refreshDirectory");
    },
    selectDirectory({ state, commit, dispatch, rootState }, { path, history }) {
      // start loaders
      // commit("toggleLoading", true);
      // reset content
      commit("setDirectoryContent", {
        directories: [],
        files: [],
      });

      // get content for the selected directory
      // let newPath = encodeURIComponent(path)
      const api = useAPI();
      api
        .get(`media-manager/file-manager/content?disk=${state.selectedDisk}&path=${path}`)
        .then((response) => {
          if (response.result.status === "success") {
            commit("resetSelected");
            commit("resetSortSettings");
            commit("setDirectoryContent", response);
            commit("setSelectedDirectory", path);
            if (history) commit("addToHistory", path);

            // if directories tree is shown, not main directory and directory have subdirectories
            if (rootState.fm.settings.windowsConfig === 2 && path && response.directories.length) {
              dispatch("fm/tree/showSubdirectories", path, {
                root: true,
              });
            }
          }
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async refreshDirectory({ state, commit, dispatch }) {
      // commit("toggleLoading", true);

      const api = useAPI();
      await api
        .get(
          `media-manager/file-manager/content?disk=${state.selectedDisk}&path=${state.selectedDirectory}`,
        )
        .then((response) => {
          commit("resetSelected");
          commit("resetSortSettings");
          commit("resetHistory");

          // add to history selected directory
          if (state.selectedDirectory) commit("addToHistory", state.selectedDirectory);
          if (response.result.status === "success") {
            commit("setDirectoryContent", response);
          } else if (response.result.status === "danger") {
            // If directory not found try to load main directory
            commit("setSelectedDirectory", null);
            dispatch("refreshDirectory");
          }
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    changeSelected({ commit }, { type, path }) {
      commit("resetSelected");
      setTimeout(() => {
        commit("setSelected", {
          type,
          path,
        });
      }, 50);
    },
    historyBack({ state, commit, dispatch }) {
      dispatch("selectDirectory", {
        path: state.history[state.historyPointer - 1],
        history: false,
      });
      commit("pointerBack");
    },
    historyForward({ state, commit, dispatch }) {
      dispatch("selectDirectory", {
        path: state.history[state.historyPointer + 1],
        history: false,
      });
      commit("pointerForward");
    },
    sortBy({ state, commit }, { field, direction }) {
      if (state.sort.field === field && !direction) {
        commit("setSortDirection", state.sort.direction === "up" ? "down" : "up");
      } else if (direction) {
        commit("setSortDirection", direction);
        commit("setSortField", field);
      } else {
        commit("setSortDirection", "up");
        commit("setSortField", field);
      }
      // sort by field type
      switch (field) {
        case "name":
          commit("sortByName");
          break;
        case "size":
          commit("sortBySize");
          break;
        case "type":
          commit("sortByType");
          break;
        case "date":
          commit("sortByDate");
          break;
        default:
          break;
      }
    },
    // Modal actions
    toClipboard({ state, commit, getters }, type) {
      // if files are selected
      if (getters[`selectedCount`]) {
        commit(
          "fm/filemanager/setClipboard",
          {
            type,
            disk: state.selectedDisk,
            directories: state.selected.directories.slice(0),
            files: state.selected.files.slice(0),
          },
          {
            root: true,
          },
        );
      }
    },
    zip({ state, getters, dispatch, commit }, name) {
      const api = useAPI();
      const selectedDirectory = getters.selectedDirectory;
      return api
        .post("media-manager/file-manager/zip", {
          disk: getters.selectedDisk,
          path: state.selected.directories[0],
          name,
          elements: state.selected,
        })
        .then((response) => {
          // if zipped successfully

          // &&   selectedDirectory === getters.selectedDirectory
          if (response.message) {
            commit(
              "toast/newMessage",
              {
                status: "green",
                text: response.message,
              },
              {
                root: true,
              },
            );
            //Refresh Directory
            dispatch("selectDirectory", {
              path: selectedDirectory,
            });

            // refresh content
            // dispatch("refreshManagers");
          }
          return response;
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    rename({ getters, dispatch }, { type, newName, oldName }) {
      const api = useAPI();
      const selectedDirectory = getters.selectedDirectory;
      return api
        .post("media-manager/file-manager/rename", {
          disk: getters.selectedDisk,
          newName,
          oldName,
        })
        .then((response) => {
          // refresh content
          dispatch("selectDirectory", {
            path: selectedDirectory,
          });
          dispatch("refreshManagers");
          //Refresh Directory
          // dispatch("selectDirectory", {
          //   path: getters.selectedDirectory,
          //   history: false,
          // });

          return response;
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    async upload({ getters, commit, dispatch }, { files, overwrite }) {
      // directory where files will be uploaded
      const selectedDirectory = getters.selectedDirectory;
      let responseObj = {};
      // create new form data
      const data = new FormData();
      data.append("disk", getters.selectedDisk);
      data.append("path", selectedDirectory || "");
      data.append("overwrite", overwrite);

      // add file or files
      for (let i = 0; i < files.length; i += 1) {
        data.append("files[]", files[i]);
      }

      // upload files
      const api = useAPI();
      const { handleError } = useMessageHandler();
      await api
        .uploadFile("media-manager/file-manager/upload", data, (progress) => {
          commit("fm/messages/setProgress", progress, {
            root: true,
          });
        })
        .then((response) => {
          commit("fm/messages/clearProgress", null, {
            root: true,
          });
          if (
            response.result.status === "success" &&
            selectedDirectory === getters.selectedDirectory
          ) {
            // refresh content
            dispatch("selectDirectory", {
              path: selectedDirectory,
            });
            dispatch("refreshManagers");
          }
          responseObj = response;
        })
        .catch((error) => {
          handleError(error);
          commit("fm/messages/clearProgress", {
            root: true,
          });
        });
      return responseObj;
    },
    async paste({ commit, getters, dispatch, rootState }) {
      const api = useAPI();
      const selectedDirectory = getters.selectedDirectory;
      await api
        .post("media-manager/file-manager/paste", {
          disk: getters.selectedDisk,
          path: getters.selectedDirectory,
          clipboard: rootState.fm.filemanager.clipboard,
        })
        .then(() => {
          //Refresh Directory
          dispatch("selectDirectory", {
            path: selectedDirectory,
          });
          // if action - cut - clear clipboard
          if (rootState.fm.filemanager.clipboard.type === "cut") {
            commit("fm/filemanager/resetClipboard", null, {
              root: true,
            });
          }
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    createDirectory({ getters, dispatch }, name) {
      // directory for new folder
      const selectedDirectory = getters.selectedDirectory;

      // create new directory, server side
      const api = useAPI();
      return api
        .post("media-manager/file-manager/create-directory", {
          disk: getters.selectedDisk,
          path: selectedDirectory,
          name,
        })
        .then((response) => {
          // update file list
          dispatch(
            "fm/filemanager/updateContent",
            {
              response,
              oldDir: selectedDirectory,
              commitName: "addNewDirectory",
              type: "directory",
            },
            {
              root: true,
            },
          );
          dispatch("fm/tree/initTree", getters.selectedDisk, {
            root: true,
          });

          // //Refresh Directory
          dispatch("selectDirectory", {
            path: selectedDirectory,
          });
          return response;
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    createFile({ getters, dispatch, commit }, fileName) {
      // directory for new file
      const selectedDirectory = getters.selectedDirectory;

      // create new file, server side
      const api = useAPI();
      api
        .post("media-manager/file-manager/create-file", {
          disk: getters.selectedDisk,
          path: selectedDirectory,
          fileName,
        })
        .then((response) => {
          // update file list
          dispatch("refreshManagers");
          // dispatch("toast/handle_success", response.result, { root: true });
          return response;
        })
        .catch((error) => {
          dispatch("toast/handle_error", error, {
            root: true,
          });
        });
    },
    attachTags({ getters, dispatch }, data) {
      const eventStore = useEventStore();
      const api = useAPI();
      const { addToast } = useToastStore();
      api
        .post("media-manager/file-manager/tags", {
          disk: getters.selectedDisk,
          // path: state.selected.directories[0],
          files: [
            {
              path: data.path,
              tags: data.tags,
            },
          ],
        })
        .then((response) => {
          addToast({
            text: response,
            type: "success",
          });
          //Refresh Directory
          dispatch("refreshManagers");
          eventStore.emit("tags-updated");
          return response;
        })
        .catch((error) => {
          addToast({
            text: error,
            type: "error",
          });
        });
    },
    delete({ state, getters, dispatch, rootState, commit }, items) {
      const selectedDirectory = getters.selectedDirectory;
      const { handleError, handleSuccess } = useMessageHandler();
      const api = useAPI();
      return api
        .post("media-manager/file-manager/delete", {
          disk: getters.selectedDisk,
          items,
        })
        .then((response) => {
          handleSuccess(response);
          // if all items deleted successfully
          dispatch("selectDirectory", {
            path: selectedDirectory,
          });
          dispatch("refreshManagers");

          // delete directories from tree
          if (rootState.fm.settings.windowsConfig === 2) {
            const onlyDir = items.filter((item) => item.type === "dir");
            dispatch("fm/tree/deleteFromTree", onlyDir, {
              root: true,
            });
          }
          dispatch("refreshAll");
          return response;
        })
        .catch((error) => {
          handleError(error);
        });
    },
    unzip({ getters, dispatch }, folder) {
      const selectedDirectory = getters.selectedDirectory;
      const { addToast } = useToastStore();
      const api = useAPI();
      return api
        .post("media-manager/file-manager/unzip", {
          disk: getters.selectedDisk,
          path: encodeURIComponent(getters.selectedList[0].path),
          folder,
        })
        .then((response) => {
          // if unzipped successfully
          if (response.status === 200 && selectedDirectory === getters.selectedDirectory) {
            // refresh
            dispatch("selectDirectory", {
              path: getters.selectedDirectory,
            });
          }
          dispatch("refreshAll");
          return response;
        })
        .catch((error) => {
          addToast({
            text: error,
            type: "error",
          });
        });
    },
  },
  getters: {
    files(state, getters, rootState) {
      if (rootState.fm.filemanager.hiddenFiles) {
        return state.files;
      }
      return state.files.filter((item) => item.basename?.match(new RegExp("^([^.]).*", "i")));
    },
    directories(state, getters, rootState) {
      if (rootState.fm.settings.hiddenFiles) {
        return state.directories;
      }
      return state.directories.filter((item) => item.basename.match(new RegExp("^([^.]).*", "i")));
    },
    filesCount(state, getters) {
      return getters.files.length;
    },
    directoriesCount(state, getters) {
      return getters.directories.length;
    },
    filesSize(state, getters) {
      if (getters.files.length) {
        return getters.files.reduce((previous, current) => previous + Number(current.size), 0);
      }
      return 0;
    },
    selectedCount(state, getters) {
      return getters.selectedList.length;
    },
    selectedFilesSize(state) {
      const selectedFiles = state.files.filter((file) => state.selected.files.includes(file.path));
      if (selectedFiles.length) {
        return selectedFiles.reduce((previous, current) => previous + Number(current.size), 0);
      }
      return 0;
    },
    selectedList(state) {
      const selectedDirectories = state.directories.filter((directory) =>
        state.selected.directories.includes(directory.path),
      );
      const selectedFiles = state.files.filter((file) => state.selected.files.includes(file.path));
      const combined = selectedDirectories.concat(selectedFiles);
      return combined;
    },
    breadcrumb(state) {
      if (state.selectedDirectory) {
        const arr = state.selectedDirectory.split("/");
        const finalArray = [];
        let path = [];
        for (let i = 0; i < arr.length; i++) {
          if (i === 0) {
            path = arr[i];
          } else {
            path = finalArray[i - 1]["path"] + "/" + arr[i];
          }
          finalArray.push({
            label: arr[i],
            path: path,
          });
        }
        return finalArray;
      }
      return null;
    },
    directoryExist: (state) => (basename) =>
      state.directories.some((el) => el.basename === basename),
    fileExist: (state) => (basename) => state.files.some((el) => el.basename === basename),
    selectedDisk(state) {
      return state.selectedDisk;
    },
    selectedDirectory(state) {
      return state.selectedDirectory;
    },
    selectedFiles(state) {
      return state.selected.files;
    },
  },
};
