<template>
  <v-select
    ref="boxSelector"
    v-model="selectedBox"
    placeholder="Select box"
    :loading="isRefreshing"
    :disabled="allBoxes.length === 0"
    :options="reducedBoxes"
    class="has-icon rounded border border-gray-200 bg-white transition-all duration-100 dark:border-gray-900 dark:bg-gray-700 dark:text-white"
  >
    <template #no-options="{ search, searching }">
      <div class="p-2">
        <template v-if="searching">
          <div class="p-2">
            {{ $t("No results found for") }}
            <em>{{ search }}</em>
            <div class="mt-2">
              <UIButton
                :icon="['fal', 'plus']"
                variant="success"
                class="block"
                @click="() => openCalculationRefModal(search)"
              >
                {{ $t("Create New Box") }}
              </UIButton>
              <UIButton
                variant="theme-light"
                class="mt-2 block"
                :icon="['fas', 'sync']"
                :loading="isRefreshing"
                @click="refresh"
              >
                {{ $t("Or retrieve the latest boxes") }}
              </UIButton>
            </div>
          </div>
        </template>
      </div>
    </template>
  </v-select>

  <ConfirmationModal v-if="boxRefModalOpen">
    <template #modal-header>{{ $t("Create New Box") }}</template>
    <template #modal-body>
      <div>
        {{ $t("What kind of Calculation Reference is") }}
        {{ newBoxName }}?
        <UISelector
          v-model="newBoxCalculationReference"
          :options="[
            { value: 'format', label: $t('Format') },
            { value: 'weight', label: $t('Weight') },
            { value: 'material', label: $t('Material') },
            { value: 'printing_colors', label: $t('Printing Colors') },
            { value: 'lamination', label: $t('Lamination') },
            { value: 'pages', label: $t('pages') },
            { value: 'cover', label: $t('cover') },
          ]"
          class="w-full"
          name="newBoxCalculationReference"
        />
      </div>
    </template>

    <template #confirm-button>
      <button
        class="mr-2 rounded-full bg-green-500 px-5 py-1 text-sm text-white transition-colors hover:bg-green-700"
        @click="handleCreateBox"
      >
        {{ $t("Add") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script setup>
const api = useAPI();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();
const { t: $t } = useI18n();

const boxSelector = ref(null);
const newBoxName = ref("");
const boxRefModalOpen = ref(false);
const newBoxCalculationReference = ref("format");

const selectedBox = defineModel({
  required: true,
  validator: (prop) => {
    return typeof prop === "object" && "label" in prop && "value" in prop;
  },
});

const { allBoxes } = storeToRefs(useSalesStore());

const { status, refresh } = await useLazyAPI("/finder/boxes/search?per_page=99999", {
  default: () => [],
  onResponse({ error, response }) {
    if (error) {
      handleError(error);
    } else {
      allBoxes.value = [...response._data.data];
    }
  },
});
const isRefreshing = computed(() => status.value === "pending");

const reducedBoxes = computed(() => {
  return allBoxes.value.map((box) => ({
    label: box.label ?? box.name,
    value: box.value ?? box.id,
    slug:
      box.slug ??
      (box.label
        ? box.label.toLowerCase().replace(/ /g, "-")
        : box.name.toLowerCase().replace(/ /g, "-")),
  }));
});

async function openCalculationRefModal(search) {
  newBoxName.value = search;
  boxRefModalOpen.value = true;
}

async function handleCreateBox() {
  const newBox = {
    name: newBoxName.value,
    published: true,
    system_key: newBoxName.value.toLowerCase().replace(/ /g, "-"),
    input_type: "checkbox",
    calc_ref: newBoxCalculationReference.value,
  };

  try {
    const response = await api.post("/boxes", newBox);
    boxRefModalOpen.value = false;
    const responseBox = {
      ...response.data,
      label: response.data.name,
      value: response.data.id,
      slug: response.data.slug,
    };
    selectedBox.value = responseBox;
    allBoxes.value = [...allBoxes.value, responseBox];

    addToast({
      type: "success",
      message: "Box created successfully",
    });
  } catch (err) {
    handleError(err);
    boxRefModalOpen.value = false;
  }
}
</script>
