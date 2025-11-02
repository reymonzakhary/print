<template>
  <div v-if="allCountries.length === 0" class="p-1 input">
    <UILoader class="text-base" />
  </div>
  <UISelector
    v-else
    :name="name"
    :options="allCountries"
    :value="modelValue"
    :rules="Yup.string().required()"
    @input="emit('update:modelValue', $event)"
  />
</template>

<script setup>
import * as Yup from "yup";
const { handleError } = useMessageHandler();
const { $api } = useNuxtApp();

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  invalid: {
    type: Boolean,
    default: false,
  },
  name: {
    type: String,
    required: true,
  },
});
const emit = defineEmits(["update:modelValue"]);

const allCountries = ref([]);
onMounted(async () => {
  try {
    const { data } = await $api("/countries");
    allCountries.value = data.map((country) => {
      return {
        label: country.name,
        value: country.id,
      };
    });
  } catch (err) {
    handleError(err);
  }
});
</script>
