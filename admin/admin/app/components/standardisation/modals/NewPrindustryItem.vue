<template>
  <confirmation-modal @on-close="closeModal">
    <template #modal-header>
      <font-awesome-icon :icon="['fal', 'box-open']" />
      Add standardized Prindustry {{ type }}
    </template>

    <template #modal-body>
      <section v-if="loading" class="flex items-center justify-center h-64">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 120">
          <!-- Background -->
          <rect width="240" height="120" fill="transparent" />

          <!-- Original shape -->
          <path d="M60,60 L80,40 L100,60 L80,80 Z" fill="#0D47A1" opacity="0.9">
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

          <!-- Translation lines -->
          <line
            x1="105"
            y1="60"
            x2="135"
            y2="60"
            stroke="#4FC3F7"
            stroke-width="1.5"
            stroke-dasharray="2,2"
            opacity="0"
          >
            <animate
              attributeName="opacity"
              values="0;0;0.9;0.9;0;0;0;0;0;0"
              dur="2.5s"
              repeatCount="indefinite"
              begin="0s"
              calcMode="spline"
              keySplines="0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1"
            />
          </line>

          <!-- Transformation arrows -->
          <path
            d="M120,57 L127,60 L120,63"
            fill="none"
            stroke="#4FC3F7"
            stroke-width="1.5"
            opacity="0"
          >
            <animate
              attributeName="opacity"
              values="0;0;0.9;0.9;0;0;0;0;0;0"
              dur="2.5s"
              repeatCount="indefinite"
              begin="0s"
              calcMode="spline"
              keySplines="0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1; 0.4 0 0.2 1"
            />
          </path>

          <!-- Translated shape -->
          <path d="M140,60 L160,40 L180,60 L160,80 Z" fill="#2196F3" opacity="0">
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
        Translating in 129 languages. This might take 5 minutes...
      </section>
      <section v-else class="flex flex-col flex-wrap max-w-xl p-8">
        <div class="text-sm">
          Add standardized Prindustry {{ type }}

          <!-- box to add -->
          <!-- if relational view -->
          <span v-if="box && Object.keys(box).length > 0">
            to
            <b class="relative mx-1">
              <small class="absolute top-0 left-0 -mt-4 font-normal text-gray-500"> box </small>
              {{ box.name }}
            </b>
          </span>

          <!-- category to add to -->
          <span v-if="category && Object.keys(category).length > 0">
            in
            <b class="relative mx-1">
              <small class="absolute top-0 left-0 -mt-4 font-normal text-gray-500">category</small>
              {{ category.name }}
            </b>
          </span>
        </div>
        <div class="relative mt-4">
          <div class="text-sm font-bold tracking-wide uppercase">
            <font-awesome-icon :icon="['fal', 'box-open']" />
            <font-awesome-icon :icon="['fal', 'tag']" class="mr-1" />
            {{ type }} name:
          </div>

          <input
            ref="newcatinput"
            v-model="filter"
            type="text"
            class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
            @focus="magic_flag = true"
          />
          <ul
            v-show="magic_flag && result && result.length > 0"
            class="absolute p-4 overflow-y-auto bg-gray-100 rounded-b dark:bg-gray-700 max-h-64"
          >
            <li
              v-for="item in result"
              :key="item.slug"
              :value="item.slug"
              class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
              @click="((filter = item.name), (magic_flag = false))"
            >
              {{ item.name }}
            </li>
          </ul>
        </div>

        <div v-if="type === 'box' || type === 'option'" class="mt-2">
          <div class="text-sm font-bold tracking-wide uppercase">
            <font-awesome-icon :icon="['fal', 'info']" class="mr-1" />
            {{ type }} info:
          </div>
          <textarea
            id="info"
            v-model="newItem.info"
            name="info"
            rows="3"
            class="w-full px-2 py-1 bg-white border border-blue-400 rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
          />
        </div>

        <transition name="slide">
          <div v-if="error" class="w-full p-2 mt-2 text-white bg-yellow-500 rounded shadow">
            {{ error }}
          </div>
        </transition>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-blue-500 rounded-full hover:bg-blue-700"
        :disabled="loading"
        @click="add()"
      >
        <font-awesome-icon :icon="['fal', 'plus']" />
        Add {{ type }}
        <font-awesome-icon v-if="loading" :icon="['fal', 'spinner-third']" spin />
      </button>
    </template>
  </confirmation-modal>
</template>

<script setup>
import { debounce } from "lodash";
import { handleError } from "vue";
const standardizationRepository = useStandardizationRepository();
const { handleSuccess } = useMessageHandler();

const props = defineProps({
  type: {
    type: String,
    default: "",
  },
  item: {
    type: [String, Object],
    default: "",
  },
});

const emit = defineEmits(["close", "on-add", "on-close"]);

const newItem = ref({});
const error = ref("");
const new_cat = ref({});
const new_box = ref({});
const magic_flag = ref(false);
const filter = ref("");
const result = ref([]);
const loading = ref(false);

watch(
  () => filter.value,
  debounce((val) => {
    newItem.value.name = filter.value;
    switch (props.type) {
      // case "category":
      //   standardizationRepository.getCategories(filter.value).then((response) => {
      //     result.value = response.data;
      //   });
      //   break;
      case "box":
        standardizationRepository.getBoxes(filter.value).then((response) => {
          result.value = response.data;
        });
        break;
      case "option":
        standardizationRepository.getOptions(filter.value).then((response) => {
          result.value = response.data;
        });
        break;
    }
    //  standardizationRepository.getCategories;
    //  axios.get(`/${prefix}?per_page=99999999999&page=1&filter=${filter.value}`).then((response) => {
    //    result.value = response.data;
    //  });
  }, 300),
);

onMounted(() => {
  new_cat.value = props.category;
  new_box.value = props.box;
});

const add = () => {
  loading.value = true;
  switch (props.type) {
    case "categories":
      standardizationRepository
        .newCategory(newItem.value)
        .then((response) => {
          handleSuccess(response);
          standardizationRepository.addSingleCategory(response);
          closeModal();
        })
        .catch((error) => {
          handleError(error);
        })
        .finally(() => {
          loading.value = false;
          closeModal();
        });
      break;
    case "boxes":
      standardizationRepository
        .newBox(newItem.value)
        .then((response) => {
          handleSuccess(response);
          standardizationRepository.addSingleBox(response);
          closeModal();
        })
        .catch((error) => {
          handleError(error);
        })
        .finally(() => {
          loading.value = false;
          closeModal();
        });
      break;
    case "options":
      standardizationRepository
        .newOption(newItem.value)
        .then((response) => {
          handleSuccess(response);
          standardizationRepository.addSingleOption(response);
          closeModal();
        })
        .catch((error) => {
          handleError(error);
        })
        .finally(() => {
          loading.value = false;
          closeModal();
        });
      break;

    default:
      break;
  }
};

const closeModal = () => {
  emit("on-close");
};
</script>

<style></style>
