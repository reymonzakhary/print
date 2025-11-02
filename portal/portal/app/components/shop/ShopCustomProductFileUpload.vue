<template>
  <div class="flex w-full items-center">
    <input
      :id="'fileUpload_' + product.id + index"
      hidden
      type="file"
      :name="'fileUpload_' + product.id + index"
      @change.prevent="fileChanged"
    />

    <div
      v-if="!file"
      class="mb-1 ml-2 flex w-full items-center text-center text-sm italic text-orange-500"
    >
      <font-awesome-icon :icon="['fad', 'triangle-exclamation']" class="mr-1" />
      {{ $t("no file added") }}
    </div>

    <div
      v-else
      v-tooltip="file.name"
      class="mb-1 ml-2 flex w-full items-center truncate text-center text-sm italic text-theme-500"
    >
      {{ file.name }}
    </div>

    <button
      class="ml-2 min-w-fit rounded-full border bg-gray-200 px-2 py-1 text-sm transition-colors duration-75 hover:bg-gray-300 dark:border-gray-900 dark:bg-gray-800 dark:hover:bg-black"
      @click.prevent="chooseFiles"
    >
      <font-awesome-icon :icon="['fal', 'upload']" class="mr-1" />
      {{ !file ? "Upload file" : "Change file" }}
      <font-awesome-icon
        v-if="loading"
        :icon="['fad', 'spinner-third']"
        class="fa-spin text-theme-500"
      />
    </button>
  </div>
</template>

<script>
import { mapMutations } from "vuex";

export default {
  props: {
    product: Object,
    validation: Object,
    index: Number,
  },
  emits: ["update:file"],
  data() {
    return {
      file: "",
      loading: false,
    };
  },
  methods: {
    ...mapMutations({
      change_item: "orders/change_item",
      update_item: "orders/update_item",
      change_single_item: "orders/change_single_item",
      update_fileList: "orders/update_fileList",
    }),
    chooseFiles() {
      document.getElementById("fileUpload_" + this.product.id + this.index).click(function (event) {
        event.stopPropagation();
      });
    },
    fileChanged(event) {
      this.loading = true;
      const files = event.target.files || event.dataTransfer.files;
      if (!files.length) {
        return;
      }
      this.createFile(files[0]);
    },

    createFile(file) {
      this.file = file;

      this.$emit("update:file", {
        key: this.validation.key,
        file: file,
      });
      // this.$parent.$parent.sendObject.set(`${this.validation.key}`, file);

      this.loading = false;
    },
  },
};
</script>
