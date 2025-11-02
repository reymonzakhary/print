<template>
  <div class="h-full flex flex-col">
    <div
      class="absolute top-0 px-2 -mt-3 tracking-widest text-gray-400 bg-white dark:bg-gray-800 left-2"
    >
      Edit fields - <span class="text-blue-400 font-bold">Prindustry Standard</span>
    </div>

    <span class="relative block mt-4">
      <label
        for="name"
        class="absolute top-0 px-1 -mt-3 text-sm tracking-widest text-blue-500 dark:text-blue-400 bg-white dark:bg-gray-800 left-2"
      >
        Name
      </label>
      <div class="flex items-center">
        <input
          v-model="selected.name"
          type="text"
          name="name"
          class="w-full p-2 bg-white border-2 border-blue-500 rounded-l dark:border-gray-900 dark:bg-gray-800 dark:text-white focus:outline-none focus:shadow-outline focus:border-blue-300"
        />
        <UIButton
          :icon="['fal', !translate ? 'language' : 'times-circle']"
          variant="theme"
          class="!text-lg rounded-none rounded-r !h-10"
          @click="translate = !translate"
        />
      </div>
    </span>

    <section v-if="translate" class="h-full overflow-y-auto p-4 border dark:border-black">
      <div class="flex z-10 bg-white dark:bg-gray-800 dark:text-white sticky top-0">
        <input
          v-model="filter"
          class="w-full px-2 py-1 bg-white border-2 border-blue-400 rounded shadow-md dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
          type="text"
          placeholder="Filter translations by ISO (eg: 'en')"
        />
        <font-awesome-icon
          v-if="!filter"
          :icon="['fal', 'search']"
          class="absolute right-0 mt-2 mr-4 text-gray-600"
        />
        <UIButton
          v-if="filter"
          class="absolute right-0 mt-1 mr-2 !bg-transparent hover:bg-transparent"
          @click="filter = ''"
        >
          <font-awesome-icon :icon="['fal', 'times-circle']" />
        </UIButton>
      </div>

      <div v-for="item in filterTranslations" :key="item.iso" class="space-y-6 relative">
        <label
          for="name"
          class="absolute top-6 px-1 -mt-3 text-sm tracking-widest text-gray-500 bg-white dark:bg-gray-800 left-2"
        >
          {{ item.iso }}
        </label>
        <input
          v-model="item.display_name"
          type="text"
          name="slug"
          class="w-full p-2 bg-white border-2 rounded dark:border-gray-900 dark:text-white dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
        />
      </div>
    </section>
    <span class="relative block mt-6">
      <label
        for="name"
        class="absolute top-0 px-1 -mt-3 text-sm tracking-widest text-blue-500 bg-white dark:bg-gray-800 dark:text-blue-400 left-2"
      >
        Description
      </label>
      <textarea
        v-model="selected.description"
        name="description"
        class="w-full p-2 bg-white border-2 border-blue-500 rounded dark:border-gray-900 dark:bg-gray-800 dark:text-white focus:outline-none focus:shadow-outline focus:border-blue-300"
      />
    </span>

    <span v-if="type === 'options'" class="relative block mt-6">
      <label
        for="calc_ref"
        class="absolute top-0 px-1 -mt-3 text-sm tracking-widest text-blue-500 bg-white dark:bg-gray-800 dark:text-blue-400 left-2"
      >
        Calculation reference
      </label>

      <select
        id="input_type"
        v-model="calc_ref"
        name="input_type"
        class="w-full p-2 bg-white border-2 border-blue-500 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
      >
        <option value="">None</option>
        <option value="format">Format</option>
        <option value="material">Material</option>
        <option value="weight">Weight</option>
        <option value="printing_colors">Printing colors</option>
      </select>
    </span>

    <div class="relative flex items-center mt-4 dark:text-white">
      <font-awesome-icon
        :icon="['fal', 'heart-rate']"
        class="mr-2 text-blue-500 dark:text-blue-400"
      />
      Published
      <div
        class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
        :class="[selected.published ? 'bg-blue-500' : 'bg-gray-300']"
      >
        <label
          for="published"
          class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
          :class="[
            selected.published ? 'translate-x-6 border-blue-500' : 'translate-x-0 border-gray-300',
          ]"
        />
        <input
          id="published"
          v-model="selected.published"
          type="checkbox"
          name="published"
          class="w-full h-full appearance-none active:outline-none focus:outline-none"
        />
      </div>
    </div>

    <div v-if="type === 'box'" class="relative flex items-center mt-4">
      <font-awesome-icon :icon="['fal', 'vector-square']" class="mr-2 text-blue-500" />
      m2
      <div
        class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
        :class="[selected.sqm ? 'bg-blue-500' : 'bg-gray-300']"
      >
        <label
          for="sqm"
          class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
          :class="[
            selected.sqm ? 'translate-x-6 border-blue-500' : 'translate-x-0 border-gray-300',
          ]"
        />
        <input
          id="sqm"
          v-model="selected.sqm"
          type="checkbox"
          name="sqm"
          class="w-full h-full appearance-none active:outline-none focus:outline-none"
        />
      </div>
    </div>

    <span v-if="type === 'box' || type === 'option'" class="relative block my-6">
      <label
        for="input_type"
        class="absolute top-0 px-1 -mt-3 text-sm tracking-widest text-blue-500 bg-white left-2"
      >
        Input type
      </label>
      <select
        id="input_type"
        v-model="selected.input_type"
        name="input_type"
        class="w-full p-2 bg-white border-2 border-blue-500 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
      >
        <option v-if="type === 'box'" value="multi_select">Multiselect</option>
        <option v-if="type === 'box'" value="single_select">Single select</option>
        <option v-if="type === 'option'" value="radio">Radio</option>
        <option v-if="type === 'option'" value="checkbox">Checkbox</option>
        <option v-if="type === 'option'" value="text">Text</option>
        <option v-if="type === 'option'" value="number">Number</option>
        <option v-if="type === 'option'" value="select">Select</option>
      </select>
    </span>

    <span v-if="type === 'option'" class="relative block my-6">
      <label
        for="incremented_by"
        class="absolute top-0 px-1 -mt-3 text-sm tracking-widest text-blue-500 bg-white left-2"
      >
        Incremented by
      </label>
      <input
        v-model="selected.incremental_by"
        type="number"
        name="incremented_by"
        class="w-full p-2 bg-white border-2 border-blue-500 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
      />
    </span>

    <div class="flex items-center justify-end">
      <!-- <button
                class="flex items-center px-2 py-1 ml-4 text-gray-500 border border-gray-500 rounded hover:bg-gray-100"
                @click="$parent.edit = false"
            >
                <font-awesome-icon :icon="['fal', 'times-circle']" class="mr-2" />
                Cancel
            </button> -->
      <button
        class="flex items-center px-2 py-1 ml-4 text-green-500 border border-green-500 rounded hover:bg-green-100"
        @click="updateItem()"
      >
        <font-awesome-icon :icon="['fal', 'check']" class="mr-2" />
        Save
      </button>
    </div>
  </div>
</template>

<script setup>
const standardizationRepository = useStandardizationRepository();
const { handleError, handleSuccess } = useMessageHandler();

const props = defineProps({
  selected: {
    type: Object,
    default: () => ({}),
  },
  type: {
    type: String,
    default: "",
  },
  category: {
    type: Object,
    default: () => ({}),
  },
  box: {
    type: Object,
    default: () => ({}),
  },
});

const translate = ref(false);
const filter = ref("");

const filterTranslations = computed(() => {
  if (!filter.value) return props.selected.display_name;
  return props.selected.display_name.filter((item) =>
    item.iso.toLowerCase().includes(filter.value.toLowerCase()),
  );
});

const calc_ref = ref(props.selected.additional?.calc_ref || "");

const updateItem = () => {
  const object = {
    name: props.selected.name,
    display_name: props.selected.display_name,
    description: props.selected.description,
    published: props.selected.published,
    sqm: props.selected.sqm,
    input_type: props.selected.input_type,
    incremental_by: props.selected.incremental_by,
    checked: props.selected.checked,
    additional: {
      calc_ref: calc_ref.value,
    },
  };

  switch (props.type) {
    case "categories":
      standardizationRepository
        .updateCategory(props.selected.slug, object)
        .then((response) => {
          handleSuccess(response);
        })
        .catch((error) => {
          handleError(error);
        });
      break;

    case "boxes":
      standardizationRepository
        .updateBox(props.selected.slug, object)
        .then((response) => {
          handleSuccess(response);
        })
        .catch((error) => {
          handleError(error);
        });
      break;

    case "options":
      standardizationRepository
        .updateOption(props.selected.slug, object)
        .then((response) => {
          handleSuccess(response);
        })
        .catch((error) => {
          handleError(error);
        });
      break;

    default:
      break;
  }
};
</script>

<style></style>
