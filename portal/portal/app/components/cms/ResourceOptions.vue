<template>
  <div>
    <section
      class="flex flex-col justify-between w-full p-4 mt-4 rounded bg-theme-400"
      style="height: 91vh"
    >
      <div>
        <div v-if="templates.length > 0" class="w-full">
          <h3 class="py-1 text-xs font-bold tracking-wide text-white uppercase">
            <font-awesome-icon :icon="['fal', 'window-restore']" />
            {{ $t("choose template") }}
          </h3>
          <select
            class="px-2 py-1 text-sm input"
            @change="updateTemplate($event)"
          >
            <option value="null"></option>
            <option
              v-for="template in templates"
              :key="template.id"
              :value="template.id"
              :selected="editableResource.template === template.id"
            >
              {{ template.name }}
            </option>
          </select>
          <span class="px-1 text-xs text-gray-200">
            {{ $t("template for webpage") }}
          </span>
        </div>

        <div class="w-full mt-4 text-xs text-white">
          <font-awesome-icon :icon="['fal', 'window-restore']" />
          <button class="px-2 underline" @click="showResourceGroups = true">
            {{ $t("resource groups") }}
          </button>
        </div>

        <ResourceGroups
          v-if="showResourceGroups"
          :editable-resource="editableResource"
        ></ResourceGroups>

        <div class="w-full mt-8">
          <h3 class="py-1 text-xs font-bold tracking-wide text-white uppercase">
            <font-awesome-icon :icon="['fal', 'eye']" />
            {{ $t("visibility") }}
          </h3>
          <span class="px-1 text-xs text-gray-200">
            {{ $t("Will the page be visible in the menu on the webshop ") }}
          </span>
          <div class="flex justify-between mt-2">
            <span class="text-sm text-white">{{ $t("visible") }} </span>
            <div
              class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
              :class="[
                !editableResource.hidden ? 'bg-green-500' : 'bg-gray-300',
              ]"
              @click="
                (editableResource.hidden = !editableResource.hidden),
                  set_resource(editableResource)
              "
            >
              <label
                class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
                :class="[
                  !editableResource.hidden
                    ? 'translate-x-6 border-green-500'
                    : 'translate-x-0 border-gray-300',
                ]"
              >
                <input
                  v-model="editableResource.hidden"
                  type="checkbox"
                  hidden
                  class="w-full h-full appearance-none active:outline-none focus:outline-none"
                />
              </label>
            </div>
          </div>
        </div>

        <div class="w-full my-8">
          <h3 class="py-1 text-xs font-bold tracking-wide text-white uppercase">
            <font-awesome-icon :icon="['fal', 'book-open']" />
            {{ $t("publication") }}
          </h3>
          <span class="px-1 text-xs text-gray-200">
            {{ $t("Will the page be visible in the menu on the webshop ") }}
          </span>
          <div class="flex items-center justify-between mt-2">
            <span class="text-sm text-white">{{ $t("publish") }} </span>
            <div
              class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
              :class="[
                editableResource.published ? 'bg-green-500' : 'bg-gray-300',
              ]"
              @click="
                (editableResource.published = !editableResource.published),
                  set_resource(editableResource)
              "
            >
              <label
                class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
                :class="[
                  editableResource.published
                    ? 'translate-x-6 border-green-500'
                    : 'translate-x-0 border-gray-300',
                ]"
              >
                <input
                  v-model="editableResource.published"
                  type="checkbox"
                  hidden
                  class="w-full h-full appearance-none active:outline-none focus:outline-none"
                />
              </label>
            </div>
          </div>
        </div>

        <button
          class="px-2 py-1 mt-4 text-sm text-red-500 bg-white rounded-full hover:bg-red-100"
          @click="$parent.showRemoveItem = true"
        >
          <font-awesome-icon :icon="['fal', 'trash-can']" />
          Remove
        </button>
      </div>

      <div
        v-if="
          editableResource.locked_by &&
          editableResource.locked_by.id !== $store.state.settings.me.id
        "
        class="p-2 mt-auto mb-4 font-bold text-center text-yellow-600 bg-yellow-100 border border-yellow-500 rounded"
      >
        <font-awesome-icon :icon="['fal', 'lock']" class="fa-lg" />
        being edited by: {{ editableResource.locked_by["email"] }}
        <br />
        <small class="font-normal text-theme-900"
          >To avoid conflict it is currently impossible to save your
          edits</small
        >
      </div>

      <!-- action buttons  -->
      <div class="grid items-center grid-cols-2 gap-3">
        <a
          class="flex items-center justify-center py-2 text-xs text-center bg-gray-100 rounded-2xl hover:bg-gray-200 text-theme-900"
          :href="url"
          target="_blank"
        >
          <font-awesome-icon :icon="['fal', 'eye']" class="mr-1" />
          {{ $t("view") }}
        </a>

        <button
          v-if="
            !editableResource.locked_by ||
            (editableResource.locked_by &&
              editableResource.locked_by.id === $store.state.settings.me.id)
          "
          class="py-2 text-xs text-white bg-green-500 rounded-2xl hover:bg-green-400"
          @click="update_resource()"
        >
          Save Changes
        </button>
      </div>
    </section>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";

export default {
  props: {
    url: String,
  },
  data() {
    return {
      visibleChecked: false,
      publishChecked: false,
      editableResource: {},
      showResourceGroups: false,
    };
  },
  mounted() {
    if (this.resource) {
      this.editableResource = { ...this.resource };
    }
  },
  computed: {
    ...mapState({
      templates: (state) => state.templates.templates,
      resource: (state) => state.resources.resource,
    }),
  },
  watch: {
    selected_item() {
      this.get_resource(this.selected_item.id);
    },
    resource(newVal) {
      this.editableResource = { ...newVal };
    },
  },
  methods: {
    ...mapActions({
      update_resource: "resources/update_resource",
    }),
    ...mapMutations({
      set_resource: "resources/set_resource",
    }),
    updateTemplate(e) {
      const id = e.target.value;
      this.editableResource.template = id;
      this.set_resource(this.editableResource);
    },
  },
};
</script>

<style></style>
