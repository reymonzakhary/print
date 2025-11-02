import { useStore } from "vuex";

export default {
  state: () => ({
    directories: [],
    settings: [],
    // directories id counter
    counter: 1,
    // directories array for deleting(indexes)
    tempIndexArray: [],
  }),
  mutations: {
    cleanTree(state) {
      state.directories = [];
      state.counter = 1;
    },
    addDirectories(state, { directories, parentId }) {
      directories.forEach((directory) => {
        // add properties to dir
        directory.id = state.counter;
        directory.parentId = parentId;
        directory.props.subdirectoriesLoaded = false;
        directory.props.showSubdirectories = false;
        state.counter += 1;
        state.directories.push(directory);
      });
    },
    replaceDirectories(state, directories) {
      state.directories = directories;
    },
    updateDirectoryProps(state, { index, props }) {
      for (const property in props) {
        if (Object.prototype.hasOwnProperty.call(props, property)) {
          state.directories[index].props[property] = props[property];
        }
      }
    },
    addToTempArray(state, index) {
      state.tempIndexArray.push(index);
    },
    clearTempArray(state) {
      state.tempIndexArray = [];
    },
  },
  actions: {
    initTree({ state, commit }, disk) {
      const api = useAPI();
      api.get(`media-manager/file-manager/tree?disk=${disk}`).then((response) => {
        // if the action was successful
        if (response.result.status === "success") {
          // clean the tree, if need
          if (state.directories) commit("cleanTree");

          // initialize directories tree
          commit("addDirectories", {
            parentId: 0,
            directories: response.directories,
          });
        }
      });
    },
    addToTree({ state, commit, getters }, { parentPath, newDirectory }) {
      // If this directory is not the root directory
      if (parentPath) {
        // find parent directory index
        const parentDirectoryIndex = getters.findDirectoryIndex(parentPath);
        if (parentDirectoryIndex !== -1) {
          // add a new directory
          commit("addDirectories", {
            directories: newDirectory,
            parentId: state.directories[parentDirectoryIndex].id,
          });

          // update parent directory property
          commit("updateDirectoryProps", {
            index: parentDirectoryIndex,
            props: {
              hasSubdirectories: true,
              showSubdirectories: true,
              subdirectoriesLoaded: true,
            },
          });
        } else {
          commit(
            "fm/messages/setError",
            {
              message: "Directory not found",
            },
            {
              root: true,
            },
          );
        }
      } else {
        // add a new directory to the root of the disk
        commit("addDirectories", {
          directories: newDirectory,
          parentId: 0,
        });
      }
    },
    deleteFromTree({ state, commit, getters, dispatch }, directories) {
      directories.forEach((item) => {
        // find this directory in the tree
        const directoryIndex = getters.findDirectoryIndex(item.path);
        if (directoryIndex !== -1) {
          // add directory index to array for deleting
          commit("addToTempArray", directoryIndex);

          // if directory has subdirectories
          if (state.directories[directoryIndex].props.hasSubdirectories) {
            // find subDirectories
            dispatch("subDirsFinder", state.directories[directoryIndex].id);
          }
        }
      });

      // filter directories
      const temp = state.directories.filter((item, index) => {
        if (state.tempIndexArray.indexOf(index) === -1) {
          return item;
        }
        return false;
      });

      // replace directories
      commit("replaceDirectories", temp);

      // clear temp array
      commit("clearTempArray");
    },
    subDirsFinder({ state, commit, dispatch }, parentId) {
      // find sub directories
      state.directories.forEach((item, index) => {
        if (item.parentId === parentId) {
          // add directory index to array
          commit("addToTempArray", index);

          // if directory has subdirectories
          if (item.props.hasSubdirectories) {
            // find subDirectories
            dispatch("subDirsFinder", item.id);
          }
        }
      });
    },
    getSubdirectories({ commit, rootGetters }, { path, parentId, parentIndex }) {
      const api = useAPI();
      const selectedDisk = rootGetters["fm/content/selectedDisk"];
      let newPath = encodeURIComponent(path);
      return api
        .get(`media-manager/file-manager/tree?path=${newPath}&disk=${selectedDisk}`)
        .then((response) => {
          // if the action was successful
          if (response.result.status === "success") {
            // add directories
            commit("addDirectories", {
              parentId,
              directories: response.directories,
            });

            // update properties at parent directory
            commit("updateDirectoryProps", {
              index: parentIndex,
              props: {
                subdirectoriesLoaded: true,
              },
            });
          }
        });
    },
    showSubdirectories({ state, commit, getters, dispatch }, path) {
      const promise = Promise.resolve();
      // find parent directory index
      const parentDirectoryIndex = getters.findDirectoryIndex(path);
      if (parentDirectoryIndex !== -1) {
        // Are the subdirectories loaded?
        if (state.directories[parentDirectoryIndex].props.subdirectoriesLoaded) {
          // update directory properties
          commit("updateDirectoryProps", {
            index: parentDirectoryIndex,
            props: {
              showSubdirectories: true,
            },
          });
        } else {
          // load subdirectories
          return dispatch("getSubdirectories", {
            path: state.directories[parentDirectoryIndex].path,
            parentId: state.directories[parentDirectoryIndex].id,
            parentIndex: parentDirectoryIndex,
          }).then(() => {
            // update properties in the parent directory
            commit("updateDirectoryProps", {
              index: parentDirectoryIndex,
              props: {
                showSubdirectories: true,
              },
            });
          });
        }
      } else {
        commit(
          "fm/messages/setError",
          {
            message: "Directory not found",
          },
          {
            root: true,
          },
        );
      }
      return promise;
    },
    hideSubdirectories({ commit, getters }, path) {
      // find parent directory index
      const parentDirectoryIndex = getters.findDirectoryIndex(path);
      if (parentDirectoryIndex !== -1) {
        // hide subdirectories
        commit("updateDirectoryProps", {
          index: parentDirectoryIndex,
          props: {
            showSubdirectories: false,
          },
        });
      } else {
        commit(
          "fm/messages/setError",
          {
            message: "Directory not found",
          },
          {
            root: true,
          },
        );
      }
    },
    reopenPath({ dispatch }, path) {
      let promises = Promise.resolve();
      if (path) {
        const splitPath = path.split("/");
        for (let i = 0; splitPath.length > i; i += 1) {
          promises = promises.then(() =>
            dispatch("showSubdirectories", splitPath.slice(0, i + 1).join("/")),
          );
        }
        return promises;
      }
      return promises;
    },
  },
  getters: {
    findDirectoryIndex: (state) => (path) => state.directories.findIndex((el) => el.path === path),
    directories(state) {
      if (state.settings.hiddenFiles) {
        return state.directories;
      }
      return state.directories.filter((item) => item.basename.match(new RegExp("^([^.]).*", "i")));
    },
  },
};
