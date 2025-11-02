<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      {{ $t("create") }} {{ $t("new") }} {{ $t("resource") }}
    </template>

    <template #modal-body>
      <!-- name -->
      <div class="my-4">
        <label
          for="template-name"
          class="text-xs font-bold tracking-wide uppercase"
        >
          <font-awesome-icon :icon="['fal', 'folder-tree']" />
          {{ $t("title") }}</label
        >

        <input
          id="template-name"
          ref="templateName"
          v-model="title"
          type="text"
          class="relative z-10 w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
        />
      </div>

      <!-- template -->
      <div v-if="templates.length > 0" class="my-4">
        <label
          for="template-name"
          class="text-xs font-bold tracking-wide uppercase"
        >
          <font-awesome-icon :icon="['fal', 'window']" />
          {{ $t("select") }}
          {{ $t("template") }}
        </label>
        <select
          id="template"
          v-model="template"
          name="template"
          class="relative z-10 w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
        >
          <option value="null"></option>
          <option
            v-for="template in templates"
            :key="template.id"
            :value="template.id"
          >
            {{ template.name }}
          </option>
        </select>
      </div>

      <!-- parent -->
      <div v-if="tree.length > 0" class="my-4">
        <label for="parent" class="text-xs font-bold tracking-wide uppercase">
          <font-awesome-icon :icon="['fal', 'folder-tree']" />
          {{ $t("select") }}
          {{ $t("parent") }}
          <span class="text-sm italic text-gray-500">optional</span>
        </label>
        <select
          id="parent"
          v-model="parent"
          name="parent"
          class="relative z-10 w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
        >
          <option value="null"></option>
          <option
            v-for="resource in tree"
            :key="resource.id"
            :value="resource.id"
          >
            {{ resource.title }}
          </option>
        </select>
      </div>
    </template>

    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-green-500 rounded-full hover:bg-green-600"
        @click="addItem()"
      >
        {{ $t("create") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapMutations } from "vuex";

export default {
  props: {
    templates: Array,
    tree: Array,
  },
  emits: ["onClose"],
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      // name for new item
      title: "",
      template: "",
      parent: "",
    };
  },
  mounted() {
    this.focusInput();
  },
  methods: {
    ...mapMutations({
      add_to_tree: "resources/add_to_tree",
    }),
    /**
     * Create new item
     */
    addItem() {
      this.api
        .post("modules/cms/tree", {
          title: this.title,
          template_id: this.template,
          parent_id: this.parent,
        })
        .then((response) => {
          this.add_to_tree(response.data);
          this.closeModal();
        })
        .catch((error) => {
          this.handleError(error);
        });
    },

    focusInput() {
      setTimeout(() => {
        this.$refs.templateName.focus();
      }, 100);
    },

    closeModal() {
      this.$parent.newResource = false;
      this.$emit("onClose");
    },
  },
};
</script>
