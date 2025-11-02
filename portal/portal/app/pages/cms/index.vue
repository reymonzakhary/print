<template>
  <div v-if="permissions.includes('cms-access')" class="h-full">
    <ResourceFormModal
      v-if="showResourceFormModal"
      @on-create-resource="handleCreateResource"
      @close-modal="toggleResourceFormModal"
    />
    <main class="grid grid-cols-1 md:grid-cols-[16.65%_82.35%] h-full p-4 gap-4">
      <ResourcesList
        v-if="permissions.includes('cms-list')"
        key="resources-list"
        :show-resource-i-ds="showResourceIDs"
        :resources="resources"
        :selected-resource="selectedResource"
        :is-bin="isBin"
        :is-loading="isLoading"
        class="!h-40 overflow-scroll md:!h-full scrollbar-hide"
        @on-item-delete="handleDeleteResource"
        @on-item-select="handleSelectResource"
        @on-reorder="handleReorder"
        @on-open-bin="handleOpenBin"
        @on-close-bin="handleCloseBin"
        @on-empty-bin="handleEmptyBin"
        @on-item-restore="handleRestoreResource"
        @on-new="toggleResourceFormModal"
      />
      <div v-else>
        {{ $t("You do not have permission to view a list of resources") }}
      </div>

      <div v-if="resources && resources.length === 0">
        <section
          class="flex flex-col flex-wrap items-center justify-center w-full h-full font-bold text-center text-gray-300"
        >
          <div class="grid grid-rows-1 mb-2">
            <font-awesome-icon
              :icon="['fad', 'slash']"
              class="col-start-1 col-end-1 row-start-1 row-end-1 mx-auto fa-6x"
            />
            <font-awesome-icon
              :icon="['fad', 'pager']"
              class="col-start-1 col-end-1 row-start-1 row-end-1 mx-auto fa-6x"
            />
          </div>
          <p class="text-xl">
            {{ $t("It seems like you do not have any resources yet.") }}
          </p>
        </section>
      </div>

      <div v-else-if="resourceNotFound">
        <section
          class="flex flex-col flex-wrap items-center justify-center w-full h-full font-bold text-center text-gray-300"
        >
          <div class="grid grid-rows-1 mb-2">
            <font-awesome-icon
              :icon="['fad', 'slash']"
              class="col-start-1 col-end-1 row-start-1 row-end-1 mx-auto fa-6x"
            />
            <font-awesome-icon
              :icon="['fad', 'pager']"
              class="col-start-1 col-end-1 row-start-1 row-end-1 mx-auto fa-6x"
            />
          </div>
          <p class="text-xl">
            {{ $t("It seems like this resource does not exist") }}
          </p>
        </section>
      </div>

      <ResourceEditor
        v-else-if="permissions.includes('cms-update')"
        :selected-resource="selectedResource"
        :resource="editableResource"
        :is-loading="singleResourceFetching"
        @on-resource-change="handleResourceChange"
        @on-resource-save="handleResourceSave"
      />
      <div v-else>
        {{ $t("You do not have permission to update a resource") }}
      </div>
    </main>
  </div>
  <div v-else>{{ $t("You do not have permission to view the CMS") }}</div>
</template>

<script>
import { useStore } from "vuex";

export default {
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    const api = useAPI();
    const { addToast } = useToastStore();
    const { handleError, handleSuccess } = useMessageHandler();
    const route = useRoute();
    const router = useRouter();
    const store = useStore();
    return {
      permissions,
      api,
      addToast,
      route,
      router,
      store,
      handleError,
      handleSuccess,
    };
  },
  data() {
    return {
      menuType: "resource", // 'resource' or 'bin'
      selectedResource: null, // ID (number) of the resource
      resources: null,
      isLoading: false,
      singleResourceFetching: true,
      resourceNotFound: false,
      showResourceFormModal: false,
      isCreatingResource: false,
      editableResource: {
        id: null,
        template: "0",
        resourceType: "document", // 'document' or 'product'
        published: false,
        visibility: false,
        navigation: {
          manager_title: "",
          menu_title: "",
          url: "",
          slug: "",
        },
        seo: {
          title: "",
          image: null,
          description: "",
        },
        content: [],
        locked: false,
        variables: [],
      },
    };
  },
  head() {
    return {
      title: `${this.$t("Resources")} | Prindustry Manager`,
    };
  },
  computed: {
    isBin() {
      return this.menuType === "bin";
    },
    showResourceIDs() {
      return this.store.getters["usersettings/showResourceIDs"];
    },
  },
  watch: {
    menuType() {
      if (this.menuType === "bin") {
        this.fetchBin();
      } else {
        this.fetchResources();
      }
    },
    selectedResource() {
      this.router.push({ query: { resource: this.selectedResource } });
      this.fetchResource(this.selectedResource);
    },
    resources() {
      if (this.resources && this.resources.length > 0 && !this.selectedResource) {
        this.selectedResource = this.resources[0].id;
      }
    },
  },
  beforeMount() {
    const selectedResourceId = this.route.query.resource;
    if (selectedResourceId && this.selectedResource !== selectedResourceId) {
      this.selectedResource = selectedResourceId;
    }
  },
  async mounted() {
    await this.fetchResources();
  },
  methods: {
    handleOpenBin() {
      this.menuType = "bin";
    },
    handleCloseBin() {
      this.menuType = "resource";
    },
    async handleEmptyBin() {
      try {
        await this.api.delete(`modules/cms/tree/trash`);
        this.handleSuccess(this.$t("Bin emptied"));
      } catch (err) {
        this.handleError(err);
      } finally {
        this.resources = [];
      }
    },
    handleSelectResource(resourceId) {
      this.selectedResource = resourceId;
    },
    async handleRestoreResource(resourceId) {
      try {
        await this.api.put(`modules/cms/tree/trash/restore/${resourceId}`);
        this.addToast({
          message: this.$t("Resource restored"),
          type: "success",
        });
      } catch (err) {
        this.handleError(err);
      } finally {
        this.resources = this.removeItemFromTree(this.resources, resourceId);
      }
    },
    async handleDeleteResource(resourceId) {
      try {
        await this.api.delete(`modules/cms/tree/${resourceId}`);
        this.addToast({
          message: this.$t("Resource deleted"),
          type: "error",
        });
      } catch (err) {
        this.handleError(err);
      } finally {
        this.resources = this.removeItemFromTree(this.resources, resourceId);
      }
    },
    handleResourceSave(value) {
      this.postResource(value);
    },
    handleResourceChange(value) {
      this.editableResource = value;
    },
    async handleReorder(reorderedList) {
      await this.updateResourcesOrder(reorderedList);
      this.resources = reorderedList;
      setTimeout(() => {
        this.fetchResource(this.selectedResource);
      }, 2000);
    },
    sortResources(resources) {
      return resources.sort((a, b) => {
        if (a.sort > b.sort) {
          return 1;
        }
        if (a.sort < b.sort) {
          return -1;
        }
        return 0;
      });
    },
    buildTree(items, parentId) {
      return items
        .map((item) => {
          // Check if parent exists
          const parentExists = items.some((_item) => _item.id === item.parent_id);
          if (!parentExists) {
            // If parent does not exist, set parent_id to null
            item.parent_id = null;
          }
          return item;
        })
        .filter((item) => item.parent_id === parentId)
        .map((item) => {
          const node = {
            id: item.id,
            title: item.title,
            // folder: item.isfolder,
            // TODO: Discuss with back-end to correctly use isFolder in database
            folder: items.some((_item) => _item.parent_id === item.id),
            resources: [],
          };

          if (node.folder) {
            node.resources = this.buildTree(items, item.id);
          }

          return node;
        });
    },
    flattenTree(items, parentId) {
      let result = [];

      items.forEach((item) => {
        const newItem = {
          id: item.id,
          title: item.title,
          parent_id: parentId,
          resource_id: item.id, // Assuming resource_id is the same as id
          language: "en", // Default value, modify as needed
          sort: 0, // Placeholder, set your sorting logic here
          isfolder: item.folder,
          published: true, // Default value, modify as needed
          hidden: false, // Default value, modify as needed
          hide_children_in_tree: false, // Default value, modify as needed
        };

        result.push(newItem);

        if (item.resources && item.resources.length > 0) {
          result = result.concat(this.flattenTree(item.resources, item.id));
        }
      });

      return result;
    },
    hydrateSort(flattenedTree) {
      return flattenedTree.map((item, index) => {
        item.sort = index + 1;
        return item;
      });
    },
    normalizeResourceForAPI(editableResource) {
      return {
        id: editableResource.id,
        template_id: Number(editableResource.template),
        resource_type_id: Number(editableResource.resourceType),
        published: editableResource.published,
        hidden: !editableResource.visibility,
        title: editableResource.navigation.manager_title,
        menu_title: editableResource.navigation.menu_title,
        slug: `/${editableResource.navigation.slug}`,
        long_title: editableResource.seo.title,
        image: {
          path: editableResource.seo.image,
          disk: "assets",
        },
        description: editableResource.seo.description,
        content: editableResource.content,
      };
    },
    normalizeResource(resource) {
      const uriParts = resource.uri.split("/");
      const lastUriPart = uriParts.pop();
      const normalizedUri = uriParts.join("/");

      return {
        id: resource.id,
        template: `${resource.template}`,
        resourceType: resource.resource_type?.id ?? "0",
        published: resource.published,
        visibility: !resource.hidden,
        navigation: {
          manager_title: resource.title,
          menu_title: resource.menu_title,
          url: normalizedUri,
          slug: lastUriPart,
        },
        seo: {
          title: resource.long_title,
          image:
            resource.image && resource.image.path && resource.image.path.length > 0
              ? resource.image.path + "/" + resource.image.name
              : resource.image && resource.image.name
                ? resource.image.name
                : "",
          description: resource.description,
        },
        content: resource.content,
        locked_by: resource.locked_by,
        variables: resource.variables,
      };
    },
    async fetchResource(resourceId) {
      this.singleResourceFetching = true;
      this.resourceNotFound = false;
      try {
        const response = await this.api.get(`modules/cms/resources/${resourceId}`);
        const resource = response.data;
        this.editableResource = this.normalizeResource(resource);
        this.singleResourceFetching = false;
      } catch (err) {
        this.handleError(err);
        this.resourceNotFound = true;
      }
    },
    async fetchBin() {
      try {
        this.isLoading = true;
        const _response = await this.api.get("modules/cms/tree/trash");
        const resources = _response.data;
        const sortedResources = this.sortResources(resources);
        const tree = this.buildTree(sortedResources, null);
        this.resources = tree;
        this.isLoading = false;
      } catch (error) {
        this.handleError(error);
      }
    },
    async fetchResources() {
      try {
        this.isLoading = true;
        const _response = await this.api.get("modules/cms/tree");
        const resources = _response.data;
        const sortedResources = this.sortResources(resources);
        const tree = this.buildTree(sortedResources, null);
        this.resources = tree;
        this.isLoading = false;
      } catch (error) {
        this.handleError(error);
      }
    },
    async updateResourcesOrder(tree) {
      const flattenedResources = this.flattenTree(tree, null);
      const normalizedTree = {
        sort: flattenedResources.map((resource) => ({
          id: resource.id,
          parent_id: resource.parent_id,
        })),
      };
      try {
        await this.api.put(`modules/cms/tree`, normalizedTree);
        this.addToast({
          message: `${this.$t("Resources Reordered")}!`,
          type: "success",
        });
      } catch (err) {
        this.handleError(err);
      }
    },
    async handleCreateResource(data) {
      if (this.isCreatingResource) return;
      this.isCreatingResource = true;
      try {
        await this.api.post(`modules/cms/resources`, data);
        this.showResourceFormModal = false;
        this.isLoading = true;
        setTimeout(() => {
          this.fetchResources();
        }, 2000);
      } catch (err) {
        this.handleError(err);
      } finally {
        this.isCreatingResource = false;
      }
    },
    toggleResourceFormModal() {
      this.showResourceFormModal = !this.showResourceFormModal;
    },
    async postResource(resource) {
      try {
        const normalizedResource = this.normalizeResourceForAPI(resource);
        await this.api.put(`modules/cms/resources/${this.selectedResource}`, normalizedResource);
        this.addToast({ message: this.$t("Resource saved"), type: "success" });
      } catch (err) {
        this.handleError(err);
      } finally {
        this.resources = this.updateTitleInTree(
          this.resources,
          resource.id,
          resource.navigation.manager_title,
        );
      }
    },
    updateTitleInTree(tree, id, title) {
      return tree.map((item) => {
        if (item.id === id) {
          return {
            ...item,
            title: title,
          };
        } else if (item.resources && item.resources.length > 0) {
          return {
            ...item,
            resources: this.updateTitleInTree(item.resources, id, title),
          };
        } else {
          return item;
        }
      });
    },
    removeItemFromTree(tree, id) {
      return tree
        .map((item) => {
          if (item.id === id) {
            return null;
          } else if (item.resources && item.resources.length > 0) {
            return {
              ...item,
              resources: this.removeItemFromTree(item.resources, id),
            };
          } else {
            return item;
          }
        })
        .filter((item) => item !== null);
    },
  },
};
</script>

<style scoped>
/* For Webkit-based browsers (Chrome, Safari and Opera) */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* For IE, Edge and Firefox */
.scrollbar-hide {
  -ms-overflow-style: none;
  /* IE and Edge */
  scrollbar-width: none;
  /* Firefox */
}
</style>
