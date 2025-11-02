<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <span class="capitalize">{{ $t("import") }} </span>
    </template>

    <template #modal-body>
      <div style="min-height: 30vh; min-width: 20vw" class="flex flex-col justify-between">
        <section v-if="newCat === false" class="flex flex-wrap w-full max-w-lg py-4">
          <p class="text-sm font-bold tracking-wide uppercase">select category</p>
          <v-select
            v-if="myCats"
            v-model="selectedCat"
            class="p-0 input"
            :value="selectedCat"
            label="display_name?.display_name"
            :options="myCats"
            @input="setCat"
          >
            <template #option="cat">
              <div class="w-full h-full py-1 overflow-auto text-sm" style="height">
                <p v-if="cat.display_name" class="font-bold text-gray-700">
                  {{ $display_name(cat.display_name) }}
                </p>
              </div>
            </template>
            <template #selected-option="cat">
              <p v-if="cat.display_name">
                {{ $display_name(cat.display_name) }}
              </p>
            </template>
          </v-select>

          <div
            v-if="Object.keys(selectedCat).length"
            class="flex items-center justify-between w-full p-2 mt-4 bg-gray-100 rounded"
          >
            <div class="flex flex-col">
              <span class="text-xs font-bold tracking-wide uppercase">
                {{ selectedCat.tenant_name }}'s
              </span>
              <span class="text-lg">
                {{ $display_name(selectedCat.display_name) }}
              </span>
            </div>

            <span class="flex items-center">
              <font-awesome-icon class="mr-2 text-theme-500 fa-3x" :icon="['fad', 'link']" />
              {{ $t("linked to") }}
            </span>

            <div class="flex flex-col">
              <span class="text-xs font-bold tracking-wide uppercase">Prindustry's</span>
              <span class="text-lg">{{ selectedCat.name }}</span>
            </div>
          </div>

          <div v-if="Object.keys(selectedCat).length === 0" class="italic text-gray-500">
            {{ $t("select category above") }}
          </div>
        </section>

        <section
          v-if="Object.keys(selectedCat).length"
          class="flex flex-wrap items-center justify-between max-w-lg py-4 border-b"
        >
          <label for="PrintProductImport" class="text-sm font-bold tracking-wide uppercase">
            {{ $t("import type") }}
          </label>
          <ValueSwitch
            name="importType"
            left-value="collection"
            right-value="runs"
            class="flex justify-between"
            :set-checked="runs"
            @checked-value="runs = !runs"
          />
        </section>
        <section
          v-if="Object.keys(selectedCat).length"
          class="flex flex-wrap items-center justify-between max-w-lg"
        >
          <label for="PrintProductImport" class="text-sm font-bold tracking-wide uppercase">
            {{ $t("update with") }}
          </label>
          <button
            class="px-4 py-1 text-sm transition border rounded-full border-theme-500 text-theme-500 hover:bg-theme-100"
            @click="showFileManagerSelectPanel = true"
          >
            <font-awesome-icon :icon="['fal', 'file-spreadsheet']" />
            {{ $t("select file") }}
          </button>

          <FileManagerSelectPanel
            v-if="showFileManagerSelectPanel === true"
            class=""
            @update-value="importFile"
            @parent-model-data="parentModelData"
          />
        </section>
      </div>
    </template>

    <template #confirm-button> </template>
    <template #cancel-button>
      <div class="w-1 h-1 rounded-full"></div>
    </template>
  </ConfirmationModal>
</template>

<script>
import FileManagerSelectPanel from "~/components/filemanager/FileManagerSelectPanel";

export default {
  components: { FileManagerSelectPanel },
  props: {
    myCats: {
      type: Array,
      required: true,
    },
  },
  emits: ["onClose"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      newCat: false,
      selectedCat: {},
      showFileManagerSelectPanel: false,
      runs: false,
    };
  },
  watch: {
    myCats: {
      immediate: true,
    },
  },
  mounted() {
    if (this.$store.state.product.active_category && this.myCats.length > 0) {
      this.selectedCat = this.myCats.filter(
        (cat) => cat.id === this.$store.state.product.active_category[0],
      )[0];
    }
  },
  methods: {
    setCat(e) {
      this.selectedCat = e;
    },
    importFile(e) {
      this.api
        .post(`categories/${this.selectedCat.slug}/products/import?runs=${this.runs}`, {
          path: e.path,
        })
        .then((response) => {
          this.handleSuccess(response);

          this.closeModal();
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    closeModal() {
      this.$emit("onClose");
    },
    parentModelData(val) {
      this.showFileManagerSelectPanel = val;
    },
  },
};
</script>
