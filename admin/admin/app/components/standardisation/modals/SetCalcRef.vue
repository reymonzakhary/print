<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <font-awesome-icon :icon="['fal', 'star-of-life']" class="mr-2" />
      Update {{ type }} with calculation reference
    </template>

    <template #modal-body>
      <section v-if="loading" class="flex items-center justify-center h-64">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 120">
          <!-- Background -->
          <rect width="240" height="120" fill="transparent" />

          <!-- Left item (minimalist trapezoid) -->
          <path d="M30,75 L55,45 L75,45 L50,75 Z" fill="#2196F3" opacity="0.9">
            <animate
              attributeName="d"
              values="M30,75 L55,45 L75,45 L50,75 Z;
              M80,75 L105,45 L125,45 L100,75 Z;
              M80,75 L105,45 L125,45 L100,75 Z;
              M30,75 L55,45 L75,45 L50,75 Z;
              M30,75 L55,45 L75,45 L50,75 Z"
              dur="2.5s"
              repeatCount="indefinite"
              begin="0s"
              calcMode="spline"
              keySplines="0.4 0 0.2 1; 0 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1"
            />
            <animate
              attributeName="opacity"
              values="0.9;0.9;0.9;0.7;0.2;0;0;0.2;0.7;0.9"
              dur="2.5s"
              repeatCount="indefinite"
              begin="0s"
              calcMode="spline"
              keySplines="0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1"
            />
          </path>

          <!-- Right item (abstract shape) -->
          <path d="M190,45 L160,45 L160,65 L170,75 L190,75 Z" fill="#0D47A1" opacity="0.9">
            <animate
              attributeName="d"
              values="M190,45 L160,45 L160,65 L170,75 L190,75 Z;
              M140,45 L110,45 L110,65 L120,75 L140,75 Z;
              M140,45 L110,45 L110,65 L120,75 L140,75 Z;
              M190,45 L160,45 L160,65 L170,75 L190,75 Z;
              M190,45 L160,45 L160,65 L170,75 L190,75 Z"
              dur="2.5s"
              repeatCount="indefinite"
              begin="0s"
              calcMode="spline"
              keySplines="0.4 0 0.2 1; 0 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1"
            />
            <animate
              attributeName="opacity"
              values="0.9;0.9;0.9;0.7;0.2;0;0;0.2;0.7;0.9"
              dur="2.5s"
              repeatCount="indefinite"
              begin="0s"
              calcMode="spline"
              keySplines="0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1"
            />
          </path>

          <!-- Merged item (geometric shape that combines elements of both) -->
          <path d="M110,60 L110,60 Z" fill="#4FC3F7" opacity="0">
            <animate
              attributeName="d"
              values="M110,60 L110,60 Z;
              M110,60 L110,60 Z;
              M100,75 L115,45 L125,45 L140,65 L130,75 Z;
              M100,75 L115,45 L125,45 L140,65 L130,75 Z;
              M110,60 L110,60 Z"
              dur="2.5s"
              repeatCount="indefinite"
              begin="0s"
              calcMode="spline"
              keySplines="0.4 0 0.2 1; 0 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1"
            />
            <animate
              attributeName="opacity"
              values="0;0;0;0.5;0.95;0.95;0.95;0.5;0;0"
              dur="2.5s"
              repeatCount="indefinite"
              begin="0s"
              calcMode="spline"
              keySplines="0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1"
            />
          </path>

          <!-- Subtle horizontal line -->
          <line x1="70" y1="90" x2="170" y2="90" stroke="#E3F2FD" stroke-width="1.5" />
        </svg>
        Updating items....
      </section>

      <section v-else class="flex p-4 min-w-max">
        <div class="flex items-center my-8 text-sm min-w-max">
          <!-- box to add -->
          <div class="flex flex-col">
            <div v-for="(item, i) in multiselectArray" :key="item" style="min-width: 10rem">
              <div class="relative block p-1 mx-1 font-bold min-w-max" style="min-width: 10rem">
                <small v-if="i === 0" class="absolute top-0 left-0 -mt-4 font-normal text-gray-500">
                  selected
                  {{ type }}
                </small>
                {{ item.name }}
              </div>
            </div>
          </div>

          <div class="mx-2 flex items-center">
            <font-awesome-icon :icon="['fal', 'chevron-right']" class="fa-lg mr-2" />
            <p class="text-sm text-gray-500">set calculation reference</p>
            <font-awesome-icon :icon="['fal', 'star-of-life']" class="fa-lg ml-2" />
          </div>

          <div class="">
            <small class="absolute top-0 left-0 -mt-4 font-normal text-gray-500">
              select calculation reference
            </small>
            <UISelector
              v-model="calc_ref"
              :options="calc_ref_options"
              label="calc_ref"
              class="inline-block text-sm rounded min-w-max border border-blue-400"
              style="min-width: 10rem"
            />
          </div>
        </div>
      </section>
    </template>
    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-blue-500 rounded-full hover:bg-blue-700"
        :disabled="loading"
        @click="updateCalcRef()"
      >
        <font-awesome-icon :icon="['fal', 'star-of-life']" class="mx-1" />
        update calculation reference
        <font-awesome-icon
          v-if="loading"
          :icon="['fad', 'spinner-third']"
          class="text-theme-500 fa-spin"
        />
      </button>
    </template>
  </ConfirmationModal>
</template>

<script setup>
const standardizationRepository = useStandardizationRepository();
const { handleError, handleSuccess } = useMessageHandler();

const props = defineProps({
  multiselectArray: {
    type: Array,
    default: () => [],
  },
  type: {
    type: String,
    default: "box",
  },
});

const emit = defineEmits(["on-close", "on-updated"]);

const calc_ref = ref("none");
const calc_ref_options = [
  { value: "", label: "none" },
  { value: "format", label: "Format" },
  { value: "material", label: "Material" },
  { value: "weight", label: "Weight" },
  { value: "printing_colors", label: "Printing colors" },
];
const loading = ref(false);

const updateCalcRef = () => {
  loading.value = true;

  props.multiselectArray.forEach((item) => {
    const payload = {
      ...item,
      additional: {
        calc_ref: calc_ref.value,
      },
    };

    standardizationRepository
      .updateOption(item.slug, payload)
      .then((response) => {
        handleSuccess(response);
      })
      .catch((error) => {
        handleError(error);
      })
      .finally(() => {
        closeModal();
      });
  });
  emit("on-updated");
  loading.value = false;
};

const closeModal = () => {
  emit("on-close");
};
</script>

<style></style>
