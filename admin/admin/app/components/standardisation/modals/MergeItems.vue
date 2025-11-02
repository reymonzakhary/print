<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <font-awesome-icon :icon="['fal', 'code-merge']" class="mr-2" />
      Merge {{ type }}
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
        Mergin items. This can take several minutes... (could be 10 or more, please be patient)
      </section>
      <section v-else class="flex p-4 min-w-max">
        <div class="flex items-center my-8 text-sm min-w-max">
          <!-- box to add -->
          <div class="flex flex-col">
            <div v-for="item in multiselectArray" :key="item" style="min-width: 10rem">
              <b
                class="relative block p-1 mx-1 my-2 border rounded min-w-max"
                style="min-width: 10rem"
              >
                <small class="absolute top-0 left-0 -mt-4 font-normal text-gray-500">
                  selected
                  {{ type }}
                </small>
                {{ item.display_name.find((item) => item.iso === "en")?.display_name }}
              </b>
            </div>
          </div>
          <div class="mx-2">
            <p class="text-xs text-gray-500">merge into</p>
            <font-awesome-icon :icon="['fal', 'code-merge']" rotation="90" class="fa-lg" />
          </div>
          <span>
            <div class="relative flex items-center mb-4 text-sm">
              <div
                class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
                :class="[newname ? 'bg-blue-500' : 'bg-gray-300']"
              >
                <label
                  for="toggleNewName"
                  class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
                  :class="[
                    newname ? 'translate-x-6 border-blue-500' : 'translate-x-0 border-gray-300',
                  ]"
                />
                <input
                  id="toggleNewName"
                  v-model="newname"
                  type="checkbox"
                  name="toggleNewName"
                  class="w-full h-full appearance-none active:outline-none focus:outline-none"
                />
              </div>
              Create new {{ type }}?
            </div>
            <div class="">
              <b v-if="newname" class="relative p-1">
                <small class="absolute top-0 left-0 -mt-4 font-normal text-gray-500">
                  type new name
                </small>
                <input
                  v-model="name"
                  type="text"
                  class="inline-block px-2 py-1 text-sm border rounded min-w-max border-blue-400"
                />
              </b>
              <b v-else class="relative p-1 m-1">
                <small class="absolute top-0 left-1 -mt-5 font-normal text-gray-500">
                  select exisiting name
                </small>
                <v-select
                  v-model="name"
                  :options="multiselectArray"
                  label="name"
                  class="inline-block text-sm rounded min-w-max border border-blue-400"
                  style="min-width: 10rem"
                />
              </b>
            </div>
          </span>
        </div>
      </section>
    </template>
    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-blue-500 rounded-full hover:bg-blue-700"
        :disabled="loading"
        @click="merge()"
      >
        <font-awesome-icon :icon="['fal', 'code-merge']" rotate="270" class="mx-1" />
        Merge
        <font-awesome-icon
          v-if="loading"
          :icon="['fad', 'spinner-third']"
          class="text-theme-400 fa-spin"
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

const emit = defineEmits(["on-close", "on-merge-categories", "on-merge-boxes", "on-merge-items"]);

const newname = ref(false);
const name = ref("");
const loading = ref(false);

watch(newname, () => {
  name.value = "";
});

const merge = () => {
  // console.log(props.type, newname.value, name.value, props.multiselectArray)
  loading.value = true;
  let payload = {
    new: newname.value,
    name: name.value?.slug ? name.value.slug : name.value,
    categories: props.multiselectArray,
  };

  switch (props.type) {
    case "categories":
      // prefix = `categories`;
      standardizationRepository
        .mergeCategories(payload)
        .then((response) => {
          handleSuccess(response);
          emit("on-merge-categories", {
            newname: newname.value,
            name: name.value?.slug ? name.value.slug : name.value,
            categories: props.multiselectArray,
          });
        })
        .catch((error) => {
          handleError(error);
        })
        .finally(() => {
          closeModal();
        });
      break;
    case "boxes":
      payload = {
        new: newname.value,
        name: name.value?.slug ? name.value.slug : name.value,
        boxes: props.multiselectArray,
      };
      // prefix = `boxes`;
      standardizationRepository
        .mergeBoxes(payload)
        .then((response) => {
          handleSuccess(response);
          emit("on-merge-boxes", {
            newname: newname.value,
            name: name.value.slug,
            categories: props.multiselectArray,
          });
        })
        .catch((error) => {
          handleError(error);
        })
        .finally(() => {
          closeModal();
        });
      break;
    case "options":
      payload = {
        new: newname.value,
        name: name.value?.slug ? name.value.slug : name.value,
        options: props.multiselectArray,
      };
      standardizationRepository
        .mergeOptions(payload)
        .then((response) => {
          handleSuccess(response);
          emit("on-merge-items", {
            newname: newname.value,
            name: name.value,
            categories: props.multiselectArray,
          });
        })
        .catch((error) => {
          handleError(error);
        })
        .finally(() => {
          closeModal();
        });
  }

  //   axios
  //     .post(`merge/${prefix}?new=${newname.value}`, {
  //       name: name.value,
  //       boxes: props.multiselectArray,
  //     })
  //     .then((response) => {
  //       set_notification({
  //         text: response.data.message,
  //         status: "green",
  //       });

  //       switch (props.type) {
  //         case "category":
  //           obtain_categories({
  //             page: props.currentPage,
  //             per_page: props.perPage,
  //             filter: props.filter,
  //           });
  //           break;
  //         case "box":
  //           obtain_boxes({
  //             page: props.currentPage,
  //             per_page: props.perPage,
  //             filter: props.filter,
  //           });
  //           break;
  //       }

  //       loading.value = false;
  //       closeModal();
  //     })
  //     .catch((err) => {
  //       set_notification({
  //         text: err,
  //         status: "red",
  //       });
  //       loading.value = false;
  //       closeModal();
  //     });
};

const closeModal = () => {
  emit("on-close");
};
</script>

<style></style>
