<template>
  <ConfirmationModal :cancel-button="false" @on-close="closeModal">
    <template #modal-header> {{ $t("new") }} {{ $t("template") }} </template>

    <template #modal-body>
      <template v-if="!created">
        <section class="flex flex-col flex-wrap w-full p-4">
          <!-- Name -->
          <div class="py-4">
            <span class="flex items-center justify-between">
              <label
                for="name"
                class="block w-1/2 text-xs font-bold tracking-wide uppercase"
              >
                {{ $t("name") }}
              </label>
              <span class="relative w-1/2">
                <input
                  ref="name"
                  v-model="templateName"
                  type="input"
                  class="relative z-10 w-full py-1 pl-8 pr-2 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
                  placeholder="name"
                  :class="{
                    'border-orange-500': required && templateName.length === 0,
                  }"
                />
                <font-awesome-icon
                  class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-500"
                  :icon="['fal', 'brush']"
                />
                <span
                  v-if="required && templateName.length === 0"
                  class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
                >
                  {{ $t("required") }}
                </span>
              </span>
            </span>
          </div>

          <div class="">
            <span class="flex items-center justify-between">
              <label
                for="provider"
                class="block w-1/2 text-xs font-bold tracking-wide uppercase"
              >
                {{ $t("provider") }}
              </label>
              <span class="relative w-1/2">
                {{ selected_provider.name }}
              </span>
            </span>
          </div>

          <div>
            <span class="flex items-center justify-between">
              <label
                for="template"
                class="block w-1/2 text-xs font-bold tracking-wide uppercase"
              >
                {{ $t("template") }}
              </label>
              <span
                class="relative w-1/2 p-2 border rounded"
                :class="{
                  'border-orange-500': required && newFiles.length === 0,
                }"
              >
                <div
                  v-show="!progressBar"
                  class="relative flex flex-col items-center justify-center border-2 border-gray-400 border-dashed"
                >
                  <input
                    type="file"
                    multiple
                    name="myfile"
                    class="relative z-50 block w-full h-full p-16 opacity-0 cursor-pointer"
                    @change="selectFiles($event)"
                  />
                  <div
                    class="absolute top-0 left-0 right-0 p-10 m-auto text-center"
                  >
                    <h4>
                      {{ $t("drag files here to upload") }}
                      <br />{{ $t("or") }}
                    </h4>
                    <p class="">
                      {{ $t("select files") }}
                    </p>
                  </div>
                </div>

                <div v-if="countFiles" class="py-4">
                  <div class="overflow-auto" style="max-height: 150px">
                    <div
                      v-for="(item, index) in newFiles"
                      :key="index"
                      class="flex justify-between"
                    >
                      <div class="mr-4 w-75 text-truncate">
                        <font-awesome-icon
                          :icon="['fal', mimeToIcon(item.type)]"
                          class="mr-1 text-theme-500"
                        />
                        {{ item.name }}
                      </div>
                      <div class="text-right">
                        {{ bytesToHuman(item.size) }}
                      </div>
                    </div>
                  </div>

                  <div
                    class="flex justify-between py-1 my-1 capitalize border-t border-b"
                  >
                    <div>
                      <strong>{{ $t("selected") }}:</strong>
                      {{ newFiles.length }}
                    </div>
                    <div class="text-right">
                      <strong>{{ $t("size") }} </strong>
                      {{ allFilesSize }}
                    </div>
                  </div>

                  <div class="flex justify-between">
                    <div>
                      <strong>{{ $t("if the file already exists") }} </strong>
                    </div>
                    <div class="form-check form-check-inline">
                      <input
                        id="uploadRadio1"
                        v-model="overwrite"
                        class="form-check-input"
                        type="radio"
                        name="uploadOptions"
                        value="0"
                        checked
                      />
                      <label class="form-check-label" for="uploadRadio1">
                        {{ $t("skip") }}
                      </label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input
                        id="uploadRadio2"
                        v-model="overwrite"
                        class="form-check-input"
                        type="radio"
                        name="uploadOptions"
                        value="1"
                        checked
                      />
                      <label class="form-check-label" for="uploadRadio2">
                        {{ $t("overwrite") }}
                      </label>
                    </div>
                  </div>
                </div>

                <div v-else class="pt-4 text-center">
                  <font-awesome-icon
                    :icon="['fad', 'transporter-empty']"
                    class="text-theme-500 fa-2x"
                  />

                  <p>{{ $t("no files") }}</p>
                </div>
                <!-- Progress Bar -->
                <div
                  v-show="countFiles"
                  v-if="progressBar"
                  class="w-full rounded-full shadow bg-grey-light"
                >
                  <div
                    class="pr-2 text-xs leading-none text-right rounded-full text-themecontrast-400 bg-theme-400"
                    :aria-valuenow="progressBar"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    :style="{ width: progressBar + '%' }"
                  >
                    {{ progressBar }}%
                  </div>
                </div>

                <span
                  v-if="required && newFiles.length === 0"
                  class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
                >
                  {{ $t("required") }}
                </span>
              </span>
            </span>
          </div>
        </section>
      </template>

      <div v-if="created" class="p-4">
        <div class="text-lg font-bold">
          {{ $t("template created succesfully") }}
        </div>

        <div class="font-bold">
          <span class="text-gray-500"> # {{ createdTemplate.id }} </span>
          {{ createdTemplate.name }}
        </div>
      </div>
    </template>

    <template #confirm-button>
      <button
        v-show="!created"
        class="px-2 py-1 mx-1 ml-auto text-sm text-white transition-colors duration-150 bg-green-500 rounded-full hover:bg-green-600"
        @click="createDesignTemplate()"
      >
        <font-awesome-icon class="fa-sm" :icon="['fal', 'right']" />
        {{ $t("create") }}
      </button>
      <button
        v-if="created"
        class="w-1/4 px-2 py-1 mx-1 ml-auto text-sm text-white transition-colors duration-150 bg-green-500 rounded-full hover:bg-green-600"
        @click="set_modal_name('')"
      >
        <font-awesome-icon class="fa-sm" :icon="['fal', 'thumbs-up']" />
        {{ $t("thank you") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import helper from "~/components/filemanager/mixins/filemanagerHelper";

export default {
  name: "NewDesignTemplate",
  mixins: [helper],
  emits: ["on-close"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      required: false,
      view: false,
      message: "",
      modalName: "Upload",
      created: false,

      templateName: "",

      createdTemplate: {},

      file: "",
      newFiles: [],
      overwrite: 1,
    };
  },

  computed: {
    ...mapState({
      selected_provider: (state) => state.design.selected_provider,
    }),

    progressBar() {
      return this.$store.state.fm.messages.actionProgress;
    },
    countFiles() {
      return this.newFiles.length;
    },
    allFilesSize() {
      let size = 0;

      for (let i = 0; i < this.newFiles.length; i += 1) {
        size += this.newFiles[i].size;
      }

      return this.bytesToHuman(size);
    },
  },
  watch: {
    created(v) {
      if (v === true) {
        setTimeout(() => {
          this.closeModal();
        }, 2000);
      }
    },
  },
  methods: {
    ...mapActions({
      get_users: "users/get_users",
    }),
    ...mapMutations({
      set_modal_name: "design/set_modal_name",
      add_template: "design/add_template",
    }),
    selectFiles(event) {
      // files selected?
      if (event.target.files.length === 0) {
        // no file selected
        this.newFiles = [];
      } else {
        // we have file or files
        this.newFiles = event.target.files;
      }
    },

    async createDesignTemplate() {
      const data = new FormData();

      // add file
      data.append("design_provider_id", this.selected_provider.id);
      data.append("name", this.templateName);
      data.append("template", this.newFiles[0]);

      const config = {
        isFormData: true,
      };
      await this.api
        .post("/design/provider/templates", data, config)
        .then((response) => {
          this.createdTemplate = response.data;
          this.add_template(response.data);
          this.created = true;
        })
        .catch((error) => {
          this.required = true;
          this.handleError(error);
        });
    },
    closeModal() {
      this.$emit("on-close");
    },
  },
};
</script>
