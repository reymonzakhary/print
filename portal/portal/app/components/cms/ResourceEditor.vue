<template>
  <ResourceEditorSkeleton v-if="isLoading" />
  <div v-else class="flex flex-wrap gap-4">
    <div class="w-full md:w-[calc(82.35%_-_1rem)] full-height overflow-y-scroll">
      <template v-if="selectedResource">
        <CMSHeader
          class="sticky top-0 z-[39]"
          :title="editableResource.navigation.manager_title"
          :active="activeTab"
          @on-tab-switch="handleTabSwitch"
        />

        <ResourceLockedNotification
          v-if="
            editableResource.locked_by !== null &&
            editableResource.locked_by.id !== $store.state.settings.me.id
          "
          :locked_by="editableResource.locked_by"
        />

        <CMSSection :title="$t('Navigation')">
          <div class="grid grid-cols-[2fr_2fr_3fr] gap-4">
            <CMSSectionCard :title="$t('Manager Title')" icon="folder-tree">
              {{ $t("This title is used in the manager") }}
              <UIInputText
                v-model="editableResource.navigation.manager_title"
                name="manager_title"
                size="md"
                :disabled="editableResource.locked"
              />
            </CMSSectionCard>

            <CMSSectionCard title="Menu Title" icon="bars">
              {{ $t("This title is shown in the menu") }}
              <UIInputText
                v-model="editableResource.navigation.menu_title"
                name="menu_title"
                size="md"
                :disabled="editableResource.locked"
              />
            </CMSSectionCard>

            <CMSSectionCard title="URL of the page" icon="link">
              {{ $t("Exact page URL") }}
              <UIInputText
                v-model="editableResource.navigation.slug"
                name="url"
                :prefix="host + editableResource.navigation.url + '/'"
                size="md"
                :disabled="editableResource.locked"
              />
            </CMSSectionCard>
          </div>
        </CMSSection>

        <CMSSection title="SEO">
          <div class="">
            <CMSSectionCard :title="$t('SEO Title')" icon="window-maximize">
              {{ $t("This title is shown in the browser tab") }}

              <div class="flex">
                <div class="w-3/4">
                  <div class="flex items-center w-full gap-4">
                    <UIInputText
                      name="seo_title"
                      :disabled="editableResource.locked"
                      :prefix="['fas', 'earth-europe']"
                      placeholder="Resources | Prindustry Manager"
                      size="md"
                      :model-value="editableResource.seo.title"
                      class="grow"
                      @input="handleSeoTitleInput"
                    />
                    <span
                      :class="{
                        'text-orange-600':
                          editableResource.seo.title &&
                          editableResource.seo.title.length > 60 &&
                          editableResource.seo.title.length < 70,
                        'text-red-600':
                          editableResource.seo.title && editableResource.seo.title.length >= 70,
                      }"
                    >
                      {{ editableResource.seo.title ? editableResource.seo.title.length : "0" }}
                      / 70
                    </span>
                  </div>
                  <span class="text-sm italic text-gray-600">
                    {{ $t("Primary Keyword") }}
                    -
                    {{ $t("Secondary Keyword") }}
                    |
                    {{ $t("Brand Name") }}
                  </span>
                </div>
                <div class="flex items-start w-1/4">
                  <UIButton
                    :icon="['fas', 'magnifying-glass']"
                    class="flex ml-auto border border-theme-300"
                    @click="showSlideInModal = true"
                  >
                    {{ $t("SEO Preview") }}
                  </UIButton>
                </div>
              </div>
              <div class="flex gap-4 mt-4">
                <CMSSectionCard
                  :title="$t('Image')"
                  class="w-1/2 !bg-transparent !shadow-none"
                  icon="image"
                >
                  <UIImageSelector
                    :selected-image="editableResource.seo.image"
                    disk="assets"
                    @on-image-select="handleImageChanged"
                  />
                </CMSSectionCard>

                <CMSSectionCard
                  :title="$t('Description')"
                  class="w-1/2 !bg-transparent shadow-none h-min"
                  icon="file"
                >
                  <UITextArea
                    v-model="editableResource.seo.description"
                    name="seo_description"
                    max-length="255"
                    :disabled="editableResource.locked"
                    class="scrollbar-hide"
                  />
                </CMSSectionCard>
              </div>
            </CMSSectionCard>
          </div>
        </CMSSection>

        <CMSSection :title="$t('Content')">
          <div class="flex flex-wrap gap-4">
            <CMSSectionCard
              v-for="(variable, index) in editableResource.variables"
              :key="variable.id"
              :title="variable.label"
              class="h-auto px-2 mb-2"
              :class="{
                'w-full': variable.data_type == 'wysiwyg_editor',
                'w-[calc(50%_-_1rem)]': variable.data_type !== 'wysiwyg_editor',
              }"
            >
              <component
                :is="getComponentConfig(variable)"
                :placeholder="variable.label"
                :value="getDynamicValue(variable, index)"
                :model-value="getDynamicValue(variable, index)"
                :selected-image="getDynamicValue(variable, index)"
                :selected-images="getDynamicValue(variable, index)"
                :name="variable.name"
                @input="
                  handleDynamicInputChange(
                    $event.target && $event.target.value ? $event.target.value : $event,
                    variable,
                    index,
                  )
                "
                @on-image-select="
                  handleDynamicInputChange(
                    $event.target && $event.target.value ? $event.target.value : $event,
                    variable,
                    index,
                  )
                "
                @on-single-image-select="
                  handleDynamicInputChange(
                    $event.target && $event.target.value ? $event.target.value : $event,
                    variable,
                    index,
                    'gallery-add',
                  )
                "
                @on-single-image-remove="
                  handleDynamicInputChange(
                    $event.target && $event.target.value ? $event.target.value : $event,
                    variable,
                    index,
                    'gallery-remove',
                  )
                "
              />
            </CMSSectionCard>
          </div>
        </CMSSection>
      </template>
    </div>

    <div class="w-full md:w-[17.65%]">
      <ActionBar
        :template="editableResource.template"
        :resource-type="editableResource.resourceType"
        :visibility="editableResource.visibility"
        :published="editableResource.published"
        :locked="editableResource.locked"
        @on-template-change="handleTemplateChange"
        @on-resource-group-button-click="handleResourceGroupButtonClick"
        @on-visibility-change="handleVisibilityChange"
        @on-publication-change="handlePublicationChange"
        @on-resource-type-change="handleResourceTypeChange"
        @on-save-button-click="handleSaveButtonClick"
        @on-preview-button-click="handlePreviewButtonClick"
      />
    </div>

    <SEOPreviewModal
      :show="showSlideInModal"
      :title="`${editableResource.seo.title}`"
      :description="`${editableResource.seo.description}`"
      :url="`${host}/${editableResource.navigation.url}`"
      :image="editableResource.seo.image"
      @on-close="showSlideInModal = false"
      @on-backdrop-click="showSlideInModal = false"
    />

    <ResourceGroupsModal
      :show="showResourceGroupsModal"
      :resource="selectedResource"
      @on-close="showResourceGroupsModal = false"
      @on-backdrop-click="showResourceGroupsModal = false"
    />
  </div>
</template>

<script>
import {
  UIInputText,
  UIButton,
  UIImageSelector,
  UITextArea,
  UIRichEditor,
  UISwitch,
  UISelector,
  UIGallerySelector,
  UICategorySelector,
} from "#components";

export default {
  name: "ResourceEditor",
  props: {
    selectedResource: {
      type: [Number, String],
    },
    resource: {
      type: Object,
    },
    isLoading: {
      type: Boolean,
      default: true,
    },
  },
  emits: ["onResourceChange", "onResourceSave"],
  data() {
    return {
      showSlideInModal: false,
      showResourceGroupsModal: false,
      editableResource: {
        id: null,
        template: "0",
        resourceType: "0",
        published: false,
        visibility: false,
        navigation: {
          manager_title: "",
          menu_title: "",
          slug: "",
          url: "",
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
      activeTab: 0,
    };
  },
  computed: {
    host() {
      if (window) {
        return window.location.host;
      } else {
        return "prindustry.nl";
      }
    },
  },
  watch: {
    resource: {
      handler() {
        if (this.resource) {
          this.editableResource = this.resource;
        }
      },
      deep: true,
      immediate: true,
    },
    editableResource: {
      handler() {
        this.$emit("onResourceChange", this.editableResource);
      },
      deep: true,
    },
  },
  methods: {
    getDynamicValue(variable, index) {
      // console.log(this.editableResource.content[index]);
      if (this.editableResource.content[index] && this.editableResource.content[index].value) {
        return this.editableResource.content[index].value;
      } else if (variable.input_type == "checkbox") {
        return false;
      } else if (variable.input_type == "file" && variable.multi_select == 1) {
        return [];
      } else {
        return "";
      }
    },
    handleDynamicInputChange(e, variable, index, action) {
      const updatedContent = this.editableResource.variables.map((variable) => {
        const contentItem = this.editableResource.content.find(
          (item) => item.key === variable.name,
        );

        return {
          key: variable.name ?? variable.key,
          value: (() => {
            if (contentItem) {
              return contentItem.value;
            }
            switch (variable.input_type) {
              case "checkbox":
                return false;
              case "file":
              case "select":
                if (variable.multi_select == 1) {
                  return [];
                } else {
                  return "";
                }
              default:
                return "";
            }
          })(),
          type: variable.data_type,
        };
      });

      if (action === "gallery-add") {
        updatedContent[index] = {
          key: variable.name,
          value:
            this.editableResource.content[index] !== null
              ? [...this.editableResource.content[index].value, e]
              : [e],
          type: variable.data_type,
        };
        this.editableResource = {
          ...this.editableResource,
          content: updatedContent,
        };
        return;
      }

      if (action === "gallery-remove") {
        updatedContent[index] = {
          key: variable.name,
          value: this.editableResource.content[index].value.splice(e, 1),
          type: variable.data_type,
        };
        this.editableResource = {
          ...this.editableResource,
          content: updatedContent,
        };
        return;
      }

      updatedContent[index] = {
        key: variable.name ?? variable.key,
        value: e,
        type: variable.data_type,
      };

      this.editableResource = {
        ...this.editableResource,
        content: updatedContent,
      };
    },
    handleSeoTitleInput(event) {
      const value = event.target.value;
      if (value.length > 255) {
        this.editableResource = {
          ...this.editableResource,
          seo: {
            ...this.editableResource.seo,
            title: value.substring(0, 255),
          },
        };
      } else {
        this.editableResource = {
          ...this.editableResource,
          seo: {
            ...this.editableResource.seo,
            title: value,
          },
        };
      }
    },
    handleSaveButtonClick() {
      this.$emit("onResourceSave", this.editableResource);
    },
    handleBackdropClick() {
      this.showSlideInModal = false;
    },
    handleTabSwitch(id) {
      this.activeTab = id;
    },
    handleTemplateChange(templateId) {
      this.editableResource = {
        ...this.editableResource,
        template: templateId,
      };
    },
    handleResourceTypeChange(resourceType) {
      this.editableResource = {
        ...this.editableResource,
        resourceType,
      };
    },
    handleResourceGroupButtonClick() {
      this.showResourceGroupsModal = true;
    },
    handleVisibilityChange(value) {
      this.editableResource = {
        ...this.editableResource,
        visibility: value,
      };
    },
    handlePublicationChange(value) {
      this.editableResource = {
        ...this.editableResource,
        published: value,
      };
    },
    handleImageChanged(image) {
      this.editableResource = {
        ...this.editableResource,
        seo: {
          ...this.editableResource.seo,
          image,
        },
      };
    },
    handlePreviewButtonClick() {
      const [hostWithoutPort] = window.location.host.split(":");
      const finalHost = hostWithoutPort || window.location.host;
      const protocol = window.location.protocol;
      window.open(
        `${protocol}//` +
          finalHost +
          "/" +
          `${this.editableResource.navigation.url}/${this.editableResource.navigation.slug}`,
        "_blank",
      );
    },
    getComponentConfig(variable) {
      if (variable.input_type == "text") {
        return markRaw(UIInputText);
      } else if (variable.input_type == "textarea") {
        if (variable.data_type == "wysiwyg_editor") {
          return markRaw(UIRichEditor);
        } else {
          return markRaw(UITextArea);
        }
      } else if (variable.input_type == "file") {
        if (variable.multi_select == 1) {
          return markRaw(UIGallerySelector);
        } else {
          return markRaw(UIImageSelector);
        }
      } else if (variable.input_type == "checkbox") {
        return markRaw(UISwitch);
      } else if (variable.input_type == "select") {
        if (variable.type === "print_category") {
          return markRaw(UICategorySelector);
        } else {
          return markRaw(UISelector);
        }
      }
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
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}

.full-height {
  height: calc(100vh - 51px - 3rem);
}
</style>
