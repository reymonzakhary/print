<template>
  <div
    v-if="permissions.includes('design-providers-templates-access')"
    class="flex flex-wrap h-full lg:flex-no-wrap"
  >
    <section class="w-full p-4 rounded">
      <div
        class="sticky flex items-center justify-between p-2 text-xs font-bold uppercase rounded-t text-themecontrast-400 bg-theme-400"
      >
        <span class="flex items-center">
          <font-awesome-icon :icon="['fal', 'brush']" class="mr-1" />
          {{ $t("design templates") }}
        </span>

        <div class="relative flex">
          <input
            ref="filter"
            v-model="filter"
            type="text"
            class="w-full px-2 py-1 text-black bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
            placeholder="filter"
          />
          <font-awesome-icon
            class="absolute right-0 mt-2 mr-4 text-gray-600"
            :icon="['fal', 'filter']"
          />
        </div>

        <button
          v-if="permissions.includes('design-providers-templates-create')"
          class="flex items-center px-2 py-1 bg-white rounded-full text-theme-500 hover:bg-theme-100"
          @click="set_modal_name('NewDesignTemplate')"
        >
          <font-awesome-icon :icon="['fad', 'brush']" class="mr-1" />
          <font-awesome-icon :icon="['fal', 'plus']" class="mr-1 text-xs" />
          {{ $t("create template") }}
        </button>
      </div>

      <div class="h-full overflow-y-auto" style="max-height: calc(100vh - 10rem)">
        <div
          class="sticky top-0 flex items-center py-1 rounded-b shadow-md backdrop-blur-md shadow-gray-200 dark:shadow-gray-900 bg-white/80 dark:bg-gray-900/80"
        >
          <div
            class="flex-auto px-2 ml-12 overflow-hidden text-xs font-bold tracking-wide uppercase"
          >
            {{ $t("name") }}
          </div>

          <div class="flex-auto hidden px-2 text-xs font-bold tracking-wide uppercase lg:flex">
            {{ $t("created") }}
          </div>
        </div>
        <transition-group name="slide">
          <div
            v-for="template in filtered_templates"
            :key="template.id"
            class="my-2 rounded"
            :class="template_details.id === template.id ? 'border border-theme-500' : ''"
          >
            <div
              class="flex items-center justify-between text-sm transition-colors duration-75 bg-white shadow-md cursor-pointer shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700"
              :class="[
                {
                  'text-theme-500 bg-theme-100 hover:bg-theme-100 dark:bg-theme-900 font-bold dark:hover:bg-theme-900':
                    selected_template && selected_template.id === template.id,
                },
                template_details.id === template.id ? 'rounded-t' : 'rounded',
              ]"
              @click="(set_selected_template(template), get_single_template(template.id))"
            >
              <div class="flex-none w-8 h-8 m-2 overflow-hidden rounded-full">
                <img
                  v-if="template.image"
                  class="object-cover w-full h-full"
                  :src="template.image"
                />
              </div>

              <div
                class="flex-auto w-full px-2 overflow-hidden capitalize truncate"
                :title="template.name"
              >
                {{ template.name }}
              </div>

              <div class="flex-auto hidden w-full px-2 text-xs lg:flex">
                {{ moment(template.created_at).format("ddd DD MMM YYYY HH:mm") }}
              </div>
              <div>
                <ItemMenu
                  :menu-items="menuItems"
                  menu-icon="ellipsis-h"
                  menu-class="w-8 h-8 rounded-full hover:bg-gray-100"
                  dropdown-class="right-0 border w-36"
                  @item-clicked="menuItemClicked($event)"
                />
              </div>
            </div>

            <template v-if="template_details.id === template.id">
              <div
                v-for="(asset, i) in template_details.assets"
                :key="'details_' + (template.id + i)"
                class="flex items-center justify-between px-4 py-1 text-sm transition-colors duration-75 bg-white border-b last:rounded-b text-themecontrast-100"
              >
                <div
                  class="flex-shrink w-full px-2 ml-8 overflow-hidden capitalize truncate"
                  :title="asset.name"
                >
                  {{ asset.name }}
                </div>
                <!-- {{asset}} -->

                <div
                  v-tooltip="asset.path"
                  class="flex-auto w-full overflow-hidden text-gray-500 truncate"
                >
                  {{ asset.path }}
                </div>

                <div class="w-full font-mono text-right" :title="asset.size">
                  {{ bytesToHuman(asset.size) }}
                </div>

                <button
                  class="flex-auto px-2 mx-1 transition rounded-full shrink-0 text-theme-500 hover:bg-theme-100"
                  @click="goToMediaManager(asset.path)"
                >
                  {{ $t("view in folder") }}
                </button>

                <button
                  class="flex-auto px-2 mx-1 transition rounded-full shrink-0 text-theme-500 hover:bg-theme-100"
                  @click="viewEdit(asset.path, asset.name, asset.ext, $event)"
                >
                  {{ $t("view") }} / {{ $t("edit contents") }}
                </button>
              </div>
            </template>
          </div>
        </transition-group>
      </div>
    </section>

    <transition name="fade">
      <component
        :is="modal_name"
        :template="selected_template"
        :classes="modal_name === 'EditDesignTemplate' ? 'w-11/12 sm:w-2/3 lg:w-2/3 xl:w-1/2' : ''"
        @on-close="closeModal"
      />
    </transition>
  </div>
</template>

<script>
// modal views
import NewDesignTemplate from "~/components/designs/modalviews/NewDesignTemplate.vue";
import EditDesignTemplate from "~/components/designs/modalviews/EditDesignTemplate.vue";
import RemoveModal from "~/components/designs/modalviews/RemoveModal.vue";
import Preview from "~/components/filemanager/views/Preview.vue";
import TextEdit from "~/components/filemanager/views/TextEdit.vue";
import AudioPlayer from "~/components/filemanager/views/AudioPlayer.vue";
import PDFViewer from "~/components/filemanager/views/PDFViewer.vue";
import VideoPlayer from "~/components/filemanager/views/VideoPlayer.vue";
import PreviewLoader from "~/components/filemanager/views/PreviewLoader.vue";

// external
import moment from "moment";

import { mapState, mapMutations, mapActions } from "vuex";

import managerhelper from "~/components/filemanager/mixins/managerhelper";
import helper from "~/components/filemanager/mixins/filemanagerHelper";

export default {
  components: {
    NewDesignTemplate,
    EditDesignTemplate,
    RemoveModal,
    Preview,
    TextEdit,
    AudioPlayer,
    PDFViewer,
    VideoPlayer,
    PreviewLoader,
  },
  mixins: [managerhelper, helper],
  props: {
    type: String,
  },
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    return { permissions };
  },
  data() {
    return {
      checked: true,
      selectedTemplate: null,
      filter: "",
      moment: moment,
      menuItems: [
        {
          items: [
            {
              action: "edit",
              icon: "pencil",
              title: this.$t("edit template"),
              classes: "text-theme-500 hover:text-theme-600",
              show: null,
            },
            {
              action: "delete",
              icon: "trash-can",
              title: this.$t("delete template"),
              classes: "text-red-500 hover:text-red-600",
              show: null,
            },
          ],
        },
      ],
    };
  },
  computed: {
    ...mapState({
      templates: (state) => state.design.templates,
      selected_template: (state) => state.design.selected_template,
      template_details: (state) => state.design.template_details,
      selected_directory: (state) => state.fm.content.selectedDirectory,
      selected: (state) => state.fm.content.selected,
    }),
    selectedItem() {
      return this.$store.getters["fm/content/selectedList"][0];
    },
    filtered_templates() {
      if (this.filter.length > 0) {
        return this.templates.filter((template) => {
          return Object.values(template).some((val) => {
            if (val !== null) {
              return val.toString().toLowerCase().includes(this.filter.toLowerCase());
            }
          });
        });
      }
      return this.templates;
    },
    modal_name() {
      if (this.$store.state.fm.modal.modalName) {
        return this.$store.state.fm.modal.modalName;
      }
      return this.$store.state.design.modal_name;
    },
  },
  watch: {
    templates: {
      immediate: true,
      deep: true,
      handler(newValue) {
        return newValue;
      },
    },
    template_details: {
      immediate: true,
      deep: true,
      handler(newValue) {
        return newValue;
      },
    },
    modal_name: {
      immediate: true,
      deep: true,
      handler(newValue) {
        return newValue;
      },
    },
  },
  async created() {
    if (this.$store.state.fm.content.selectedDisk === null) {
      await this.$store.dispatch("fm/filemanager/initializeApp");
    }
  },
  mounted() {
    this.showSettings();
  },
  methods: {
    ...mapMutations({
      set_modal_name: "design/set_modal_name",
      set_selected_template: "design/set_selected_template",
    }),
    ...mapActions({
      get_single_template: "design/get_single_template",
      initialize_app: "fm/filemanager/initializeApp",
    }),

    closeModal() {
      this.set_modal_name(null);
    },
    goToMediaManager(path) {
      if (this.selected_directory) {
        this.selectDirectory(path);

        this.$router.push("/filemanager");
      } else {
        this.initialize_app();

        setTimeout(() => {
          this.selectDirectory(path);

          this.$router.push("/filemanager");
        }, 300);
      }
    },

    viewEdit(path, name, ext, e) {
      this.selectDirectory(path);

      setTimeout(() => {
        this.selectItem("files", `${path}/${name}`, e);
      }, 500);

      setTimeout(() => {
        this.selectAction(`${path}/${name}`, ext);
      }, 800);
    },

    showSettings() {
      if (this.permissions.includes("design-providers-templates-update")) {
        this.menuItems[0].items[0].show = true;
      }
      if (this.permissions.includes("design-providers-templates-delete")) {
        this.menuItems[0].items[1].show = true;
      }
    },

    menuItemClicked(event) {
      switch (event) {
        case "edit":
          this.set_modal_name("EditDesignTemplate");
          break;

        case "delete":
          this.set_modal_name("RemoveModal");
          break;

        default:
          break;
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.scroll-container {
  max-height: calc(50vh - 8rem);
  // overflow-y: scroll !important;
}
.address-scroll-container {
  max-height: calc(100vh - 8rem);
  // overflow-y: scroll !important;
}
</style>
