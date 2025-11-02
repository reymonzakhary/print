<template>
  <div class="flex">
    <AssortmentSettingsNav class="hidden w-1/5 md:block" />

    <div class="mt-2 w-full p-4 md:w-4/5">
      <UICardHeader class="sticky top-0 z-30 max-h-[42px] rounded backdrop-blur">
        <template #left>
          <UICardHeaderTitle :icon="['fal', icon]" :title="title" />
        </template>

        <template #center>
          <div class="flex">
            <div class="mx-auto flex">
              <UICardHeaderTab
                v-if="permissions.includes('print-assortments-machines-access')"
                :label="$t('Machines')"
                :active="active === 'machines'"
                @click="switchTab('machines')"
              />
              <UICardHeaderTab
                v-if="
                  permissions.includes('print-assortments-catalogues-access') ||
                  permissions.includes('print-assortments-system-catalogues-access')
                "
                :label="$t('Catalogue')"
                :active="active === 'catalog'"
                @click="switchTab('catalog')"
              />
            </div>
          </div>
        </template>

        <template #right>
          <div class="ml-4 min-w-40">
            <UIButton
              v-if="
                active === 'machines' && permissions.includes('print-assortments-machines-create')
              "
              :icon="['fad', buttonIcon]"
              class="capitalize"
              :disabled="editablePaper !== null"
              @click="addMachine()"
            >
              {{ buttonTitle }}
            </UIButton>
          </div>
        </template>
      </UICardHeader>

      <Machines
        v-if="active === 'machines'"
        :machines="machines"
        :printing-methods="printingMethods"
        :colors="colors"
        :machine-to-edit="machineToEdit"
        :finishings="finishings"
        @on-change-edit="machineToEdit = $event"
        @on-save-machine="handleSaveMachine"
        @on-delete-machine="handleDeleteMachine"
      />

      <Catalog
        v-if="active === 'catalog'"
        :catalogue="catalogue"
        :editable="editablePaper"
        :new-paper="newPaper"
        @on-copy-catalog="addPaper"
        @on-add-catalog="handleAddCatalogue"
        @on-add-single-material="addPaper"
        @on-save-catalog="handleSaveCatalogue"
        @on-delete-catalog="handleDeleteCatalogue"
        @on-edit-catalog="((editablePaper = $event), (newPaper = null))"
        @on-cancel-catalog="((editablePaper = null), catalogue.shift(), (newPaper = null))"
        @on-cancel-catalog-edit="editablePaper = null"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useAPI } from "@/composables/useAPI";
import { useAuthStore } from "@/stores/auth";
import { useToastStore } from "@/stores/toast";
import { useMessageHandler } from "@/composables/useMessageHandler";

const { t } = useI18n();
const api = useAPI();
const { permissions } = storeToRefs(useAuthStore());
const { addToast } = useToastStore();
const { handleError, handleSuccess } = useMessageHandler();

const active = ref("machines");
const machineToEdit = ref(false);
const icon = ref("print");
const title = ref(useI18n().t("Machines"));
const buttonIcon = ref("print");
const buttonTitle = ref(useI18n().t("new machine"));

const machines = ref([]);
const catalogue = ref([]);
const printingMethods = ref([]);
const colors = ref([]);
const finishings = ref([]);
const editablePaper = ref(null);
const newPaper = ref(null);

const switchTab = async (tab) => {
  if (tab === "machines") {
    await fetchMachines();
    active.value = "machines";
    icon.value = "print";
    title.value = t("Machines");
    buttonIcon.value = "print";
    buttonTitle.value = t("new machine");
  }
  if (tab === "catalog") {
    await fetchCatalog();
    active.value = "catalog";
    icon.value = "layer-group";
    title.value = t("Catalogue");
    buttonIcon.value = "layer-plus";
    buttonTitle.value = t("new paper");
  }
};

const fetchCatalog = async () => {
  try {
    const response = await api.get("/catalogues");
    catalogue.value = response.data;
  } catch (error) {
    handleError(error);
  }
};

const fetchMachines = async () => {
  try {
    const response = await api.get("/machines");
    machines.value = response.data;
  } catch (error) {
    handleError(error);
  }
};

const fetchPrintingMethods = async () => {
  try {
    const response = await api.get("printing-methods");
    printingMethods.value = response.data;
  } catch (error) {
    handleError(error);
  }
};

const fetchColors = async () => {
  try {
    const response = await api.get("options?ref=printing_colors&per_page=999999");
    colors.value = response.data;
  } catch (error) {
    handleError(error);
  }
};

const fetchFinishings = async () => {
  try {
    const response = await api.get("options?ref=finishing&per_page=999999");
    finishings.value = response.data;
  } catch (error) {
    handleError(error);
  }
};

const addPaper = (original_paper) => {
  const paper = {
    art_nr: null,
    supplier: null,
    material: "",
    material_link: null,
    sheet: true,
    width: 0,
    height: 0,
    length: 0,
    density: 0,
    grs: "",
    grs_link: null,
    price: 0,
    ean: null,
    calc_type: "kg",
  };

  catalogue.value.unshift(original_paper ?? paper);
  editablePaper.value = 0;
  newPaper.value = 0;
};

const addMachine = async () => {
  const machine = {
    name: "new machine",
    description: "my new machine",
    type: "printing",
    fed: "sheet",
    width: 297,
    height: 420,
    unit: "mm",
    ean: Math.floor(Math.random() * 1000000000000),
    pm: "all",
    spm: 6000,
    start_cost: 0,
    price: 0,
    spoilage: 0,
    wf: 0,
    cooling_time: 0,
    cooling_time_per: 0,
    mpm: 0,
    min_gsm: 60,
    max_gsm: 300,
    divide_start_cost: false,
  };

  try {
    const response = await api.post("/machines", machine);
    handleSuccess(response);
    machines.value.unshift(response.data);
  } catch (error) {
    handleError(error);
  }
};

const handleSaveMachine = async (machine) => {
  try {
    const response = await api.put(`/machines/${machine.id}`, machine);
    await fetchMachines();
    handleSuccess(response);
    machineToEdit.value = false;
  } catch (error) {
    handleError(error);
  }
};

const handleAddCatalogue = async (paper) => {
  try {
    const response = await api.post("/catalogues", paper);
    await fetchCatalog();
    handleSuccess(response);
    fetchCatalog();
    editablePaper.value = null;
  } catch (error) {
    handleError(error);
  }
};

const handleSaveCatalogue = async (paper) => {
  try {
    const response = await api.put(`/catalogues/${paper.id}`, paper);
    await fetchCatalog();
    handleSuccess(response);
    editablePaper.value = null;
  } catch (error) {
    handleError(error);
  }
};

const handleDeleteCatalogue = async (e) => {
  try {
    const response = await api.delete(`/catalogues/${e.paper.id}`);
    catalogue.value.splice(e.index, 1);
    editablePaper.value = null;
    handleSuccess(response);
  } catch (error) {
    handleError(error);
  }
};

const handleDeleteMachine = async (e) => {
  try {
    const response = await api.delete(`/machines/${e.internalMachine.id}`);
    fetchMachines();
    handleSuccess(response);
  } catch (error) {
    handleError(error);
  }
};

onMounted(() => {
  fetchMachines();
  fetchPrintingMethods();
  fetchColors();
  fetchFinishings();
});
</script>
