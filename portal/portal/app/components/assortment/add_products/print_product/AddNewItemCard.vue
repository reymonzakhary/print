<template>
  <div class="my-10 w-full rounded border-2 bg-white p-4 text-center dark:bg-gray-700">
    <!-- Dynamic header based on context -->
    <div class="w-full text-center">
      <template v-if="hasResults">
        <p class="font-bold">Not found what you were looking for?</p>
        <p class="text-gray-500">Add a new {{ $t(type) }}</p>
      </template>
      <template v-else>
        <b>{{ searchTerm }}</b> {{ $t("not found") }}...
      </template>
    </div>

    <!-- Form section for detailed input (when hasResults is true) -->
    <ProductNameForm
      v-if="hasResults"
      :display-value="displayValue"
      :name-value="nameValue"
      :button-text="`${$t('add')} ${type}`"
      @update-display-name="$emit('update-display-name', $event)"
      @update-name="$emit('update-name', $event)"
      @submit="handleSubmit"
    />

    <!-- Simple button for no results case -->
    <button
      v-else
      class="my-4 rounded bg-theme-400 px-2 py-1 text-themecontrast-400"
      @click="handleSubmit"
    >
      {{ $t("add as new") }} {{ type }}
    </button>

    <!-- Warning message (always shown) -->
    <div
      class="mx-auto w-1/2 rounded border border-orange-500 bg-orange-100 p-2 text-left text-orange-500"
    >
      <p>
        <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="mr-2" />
        <b class="text-sm tracking-wide">{{ $t("WARNING") }}</b> <br />
        {{
          $t(
            "item will not be visible in finder and is not producable by external producer until approved by Prindustry",
          )
        }}
      </p>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  type: {
    type: String,
    required: true,
  },
  hasResults: {
    type: Boolean,
    default: false,
  },
  searchTerm: {
    type: String,
    default: "",
  },
  displayValue: {
    type: String,
    default: "",
  },
  nameValue: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update-display-name", "update-name", "add-new", "clear-selection"]);

const { t: $t } = useI18n();

const handleSubmit = () => {
  console.log("AddNewItemCard - handleSubmit called, emitting events");
  console.log("Props:", {
    type: props.type,
    hasResults: props.hasResults,
    searchTerm: props.searchTerm,
  });
  emit("clear-selection");
  emit("add-new");
};
</script>
