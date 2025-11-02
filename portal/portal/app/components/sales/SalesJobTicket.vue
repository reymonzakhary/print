<template>
  <ConfirmationModal
    classes="w-10/12 lg:w-11/12 h-[90vh]"
    :cancel-button="false"
    :no-footer="true"
    @on-close="closeModal"
  >
    <template #modal-header>
      <span class="relative z-50 font-bold text-gray-400"> #{{ props.salesId }}</span>
    </template>

    <template #modal-body>
      <section class="relative w-full h-full -m-4">
        <MonacoEditor
          v-if="code && format === 'xml'"
          ref="editorRef"
          v-model="code"
          language="xml"
          class="w-full h-full"
          :options="{ theme: mode === 'dark' ? 'vs-dark' : 'vs', minimap: { enabled: false } }"
        />

        <div v-if="format === 'html'" class="z-0 p-2 bg-gray-100 dark:bg-gray-900" v-html="code" />

        <div class="!flex"></div>

        <div v-if="format === 'pdf'" style="height: 80vh; width: 90vw">
          <iframe :src="code" allowfullscreen height="100%" width="100%" />
        </div>
      </section>
    </template>
  </ConfirmationModal>
</template>

<script setup>
import { useStore } from "vuex";

const props = defineProps({
  salesId: {
    type: [String, Number],
    required: true,
  },
  format: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["onClose"]);

const { handleError } = useMessageHandler();
const store = useStore();
const api = useAPI();

const code = ref("");
const editorRef = ref(null);
const mode = computed(() => store.state.theme.active_theme);

const closeModal = () => {
  emit("onClose");
};

onMounted(async () => {
  try {
    const response = await api.post(
      `orders/${props.salesId}/jobtickets`,
      {
        format: props.format,
        iso: "nl",
      },
      { responseType: props.format === "pdf" ? "arrayBuffer" : "" },
    );

    if (props.format === "xml" || props.format === "html") {
      code.value = response;
    } else if (props.format === "pdf") {
      code.value = window.URL.createObjectURL(new Blob([response], { type: "application/pdf" }));
    }
  } catch (error) {
    handleError(error);
  }
});
</script>
