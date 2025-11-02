<template>
  <div class="modal-content fm-modal-clipboard">
    <div class="modal-header">
      <h5 class="modal-title">{{ lang.clipboard.title }}</h5>
      <button type="button" class="close" aria-label="Close" @click="hideModal">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body">
      <template v-if="clipboard.type">
        <div class="d-flex justify-content-between">
          <div class="w-75 text-truncate">
            <span> <i class="far fa-hard-drive" />{{ clipboard.disk }} </span>
          </div>
          <div class="text-muted text-right">
            <span :title="`${lang.clipboard.actionType} - ${lang.clipboard[clipboard.type]}`">
              <i v-if="clipboard.type === 'copy'" class="fas fa-copy" />
              <i v-else class="fas fa-cut" />
            </span>
          </div>
        </div>
        <hr />
        <div
          v-for="(dir, index) in directories"
          :key="`d-${index}`"
          class="d-flex justify-content-between"
        >
          <div class="w-75 text-truncate">
            <span> <i class="far fa-folder" />{{ dir.name }} </span>
          </div>
          <div class="text-right">
            <button
              type="button"
              class="close"
              :title="lang.btn.delete"
              @click="deleteItem('directories', dir.path)"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
        <div
          v-for="(file, index) in files"
          :key="`f-${index}`"
          class="d-flex justify-content-between"
        >
          <div class="w-75 text-truncate">
            <span> <i class="far" :class="file.icon" />{{ file.name }} </span>
          </div>
          <div class="text-right">
            <button
              type="button"
              class="close"
              :title="lang.btn.delete"
              @click="deleteItem('files', file.path)"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
      </template>
      <template v-else>
        <span>{{ lang.clipboard.none }}</span>
      </template>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger" :disabled="!clipboard.type" @click="resetClipboard">
        {{ lang.btn.clear }}
      </button>
      <button class="btn btn-light" @click="hideModal">
        {{ lang.btn.cancel }}
      </button>
    </div>
  </div>
</template>

<script>
// import translate from './../../../mixins/translate';
import helper from "~/components/filemanager/mixins/filemanagerHelper";

export default {
  name: "Clipboard",
  mixins: [helper],
  computed: {
    /**
     * Clipboard state
     * @returns {*}
     */
    clipboard() {
      return this.$store.state.fm.clipboard;
    },

    /**
     * Paths and names for directories
     * @returns {{path: *, name: *}[]}
     */
    directories() {
      return this.$store.state.fm.clipboard.directories.map((item) => ({
        path: item,
        name: item.split("/").slice(-1)[0],
      }));
    },

    /**
     * File names, paths and icons
     * @returns {{path: *, name: *, icon: *}[]}
     */
    files() {
      return this.$store.state.fm.clipboard.files.map((item) => {
        const name = item.split("/").slice(-1)[0];
        return {
          path: item,
          name,
          icon: this.extensionToIcon(name.split(".").slice(-1)[0]),
        };
      });
    },
  },
  methods: {
    /**
     * Delete item from clipboard
     * @param type
     * @param path
     */
    deleteItem(type, path) {
      this.$store.commit("fm/truncateClipboard", { type, path });
    },

    /**
     * Reset clipboard
     */
    resetClipboard() {
      this.$store.commit("fm/resetClipboard");
    },
  },
};
</script>

<style lang="scss">
.fm-modal-clipboard {
  .modal-body .far {
    padding-right: 0.5rem;
  }
}
</style>
