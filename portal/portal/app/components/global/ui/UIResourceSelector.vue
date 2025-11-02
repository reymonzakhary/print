<template>
  <div v-if="allOptions.length === 0" class="p-1 input">
    <UILoader class="text-base" />
  </div>
  <UISelector
    :name="name"
    :options="allOptions"
    :value="selectedOption"
    :rules="Yup.string().required()"
    @input="selectedOption = $event"
  />
</template>

<script setup>
import * as Yup from "yup";

const props = defineProps({
  value: {
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
const emit = defineEmits(["input"]);

const api = useAPI();

const allOptions = ref([]);
onMounted(async () => {
  try {
    const { data } = await api.get("/modules/cms/tree");
    allOptions.value = data.map((option) => {
      return {
        label: option.name,
        value: option.id,
      };
    });
  } catch (err) {
    console.error(err);
  }
});

const selectedOption = computed({
  get() {
    return props.value;
  },
  set(val) {
    emit("input", val);
  },
});
</script>
