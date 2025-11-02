<template>
  <div>
    <SidePanel @on-close="close">
      <template #side-panel-header>
        <h2 class="p-4 font-bold tracking-wide text-blue-900 uppercase">
          <font-awesome-icon :icon="['fal', 'pencil']" class="mr-2 text-sm" /><span
            class="text-gray-500"
            >{{ $t("edit") }}</span
          >
          {{ type }}
          {{ item.name }}
        </h2>
      </template>

      <template #side-panel-content>
        <div class="p-4">
          <div class="w-1/2 mx-auto my-2">
            <label for="name" class="text-sm font-bold tracking-wide uppercase">
              {{ $t("name") }}
            </label>
            <input
              id="name"
              v-model="newItem.name"
              name="name"
              type="text"
              placeholder="name"
              class="block w-full px-2 py-1 mb-4 text-black bg-white border rounded dark:border-gray-900 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
            />

            <label for="description" class="text-sm font-bold tracking-wide uppercase">
              {{ $t("description") }}
            </label>
            <input
              id="description"
              v-model="newItem.description"
              name="description"
              type="text"
              placeholder="description"
              class="block w-full px-2 py-1 mb-4 text-black bg-white border rounded dark:border-gray-900 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
            />
            <!-- {{newItem.folder}} -->
            <label for="folder" class="text-sm font-bold tracking-wide uppercase">
              {{ $t("folder") }}
            </label>

            <select
              id="folder"
              v-model="newItem.folder_id"
              name="folder"
              class="block w-full px-2 py-1 mb-4 text-black bg-white border rounded dark:border-gray-900 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
            >
              <option :value="null"></option>
              <template v-for="folder in folders" :key="'folder_' + folder.id">
                <option :value="folder.id">
                  {{ folder.name }}
                </option>
              </template>
            </select>

            <label for="sort" class="text-sm font-bold tracking-wide uppercase">
              {{ $t("sort") }}
            </label>

            <select
              id="sort"
              v-model="newItem.sort"
              name="sort"
              class="block w-full px-2 py-1 mb-4 text-black bg-white border rounded dark:border-gray-900 dark:text-white dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
            >
              <option :value="null"></option>
              <template v-for="sort in 100" :key="'sort_' + sort">
                <option :value="sort">
                  {{ sort }}
                </option>
              </template>
            </select>

            <div class="flex">
              <button
                class="flex px-2 py-1 ml-auto mr-1 mr-2 text-gray-500 border border-gray-500 rounded-full justify-self-end hover:bg-gray-100"
                @click="close()"
              >
                {{ $t("cancel") }}
              </button>
              <button
                class="flex px-2 py-1 ml-auto mr-1 text-green-500 border border-green-500 rounded-full justify-self-end hover:bg-green-100"
                @click="update(newItem)"
              >
                {{ $t("save") }}
              </button>
            </div>
          </div>
        </div>
      </template>
    </SidePanel>
  </div>
</template>

<script>
import { mapActions } from "vuex";
export default {
  name: "Edititem",
  props: {
    item: Object,
    type: String,
    folders: Array,
  },
  emits: ["on-close"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      newItem: {},
    };
  },
  mounted() {
    this.newItem = { ...this.item };
  },
  methods: {
    ...mapActions({
      get_templates: "templates/get_templates",
      get_chunks: "templates/get_chunks",
      get_snippets: "templates/get_snippets",
      update_item: "templates/update_item",
    }),
    update(item) {
      switch (this.type) {
        case "template":
          this.api.put(`modules/cms/templates/${item.id}`, item).then((response) => {
            this.handleSuccess(response);
            this.get_templates();
            this.close();
          });
          break;

        case "chunk":
          this.api.put(`modules/cms/chunks/${item.id}`, item).then((response) => {
            this.handleSuccess(response);
            this.get_chunks();
            this.close();
          });
          break;

        case "snippet":
          this.api.put(`modules/cms/snippets/${item.id}`, item).then((response) => {
            this.handleSuccess(response);
            this.get_snippets();
            this.close();
          });
          break;

        default:
          break;
      }
    },
    close() {
      this.$emit("on-close");
    },
  },
};
</script>
