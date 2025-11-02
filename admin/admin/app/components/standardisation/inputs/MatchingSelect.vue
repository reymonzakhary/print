<template>
  <div class="">
    <div class="text-xs font-bold tracking-wide text-gray-500 dark:text-gray-400 uppercase">
      Attach to <span class="text-blue-500 dark:text-blue-300">standardized {{ type }}</span>
    </div>

    <div class="flex flex-col mt-4 z-50">
      <div class="relative flex">
        <input
          v-model="filter"
          class="w-full px-2 py-1 pl-8 dark:text-white mr-2 bg-white border border-blue-300 rounded shadow-md dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
          type="text"
          :placeholder="`Search ${type}`"
          @focus="selected = []"
        />
        <font-awesome-icon
          v-if="loading"
          :icon="['fad', 'spinner-third']"
          class="absolute left-2 mt-2 mr-4 text-theme-600"
          spin
        />
        <font-awesome-icon
          :icon="['fal', searchitems.length === 0 ? 'filter' : 'times-circle']"
          class="absolute right-0 mt-2 mr-4 text-gray-600"
          @click="(searchitems.length > 0 ? (searchitems = []) : '', (loading = false))"
        />

        <div
          v-if="searchitems?.length > 0"
          class="z-50 absolute w-full -mt-1 overflow-y-auto bg-white dark:bg-gray-700 dark:text-white border rounded-b shadow-md top-9 max-h-80"
        >
          <ul class="divide-y">
            <li
              v-for="(item, i) in searchitems"
              :key="'searchitem_' + i"
              class="block p-4 transition-colors cursor-pointer hover:bg-gray-100 hover:text-blue-500"
              @click="((selected = item), (filter = ''), (searchitems = []))"
            >
              {{ item.name }}
            </li>
          </ul>
        </div>
      </div>

      <!-- <div v-if="selected.length > 0">{{ selected[i].name }}</div> -->
      <section class="z-10 flex items-center">
        <div class="flex flex-col w-3/6 pb-10">
          <section
            class="bg-white dark:bg-gray-700 mt-12 p-4"
            style="clip-path: polygon(0 100%, 0 0, 84% 0, 100% 100%)"
          >
            <p class="text-xs font-bold tracking-wide text-gray-500 dark:text-gray-300 uppercase">
              {{ match.tenant_name }}
            </p>
            <p class="font-bold text-2xl dark:text-white">
              {{ match.name }}
            </p>
          </section>
          <section
            class="bg-blue-100 dark:bg-blue-800 p-4"
            style="clip-path: polygon(0 0, 100% 0, 0 670%)"
          >
            <p class="text-xs font-bold tracking-wide text-blue-500 dark:text-blue-400 uppercase">
              Prindustry
            </p>
            <p v-if="Object.keys(selected).length > 0" class="font-bold text-2xl dark:text-white">
              {{ selected?.name }}
            </p>
            <p v-else class="dark:text-white">
              <font-awesome-icon :icon="['fal', 'circle-info']" /> Select a {{ type }} using the
              searchfield above...
            </p>
          </section>
        </div>
        <UIButton
          v-if="selected"
          :disabled="Object.keys(selected).length === 0"
          class="!w-24 !px-2 !py-1 ml-8 font-bold text-blue-500 border !text-base border-blue-500 !rounded hover:bg-blue-100 uppercase"
          @click="attach(selected.slug, match)"
        >
          ATTACH!
        </UIButton>
      </section>
    </div>
  </div>
</template>

<script setup>
import _ from "lodash";

const standardizationRepository = useStandardizationRepository();
const { handleError, handleSuccess } = useMessageHandler();

// const store = useStore();
const props = defineProps({
  match: {
    type: Object,
    required: true,
  },
  item: {
    type: Object,
    default: null,
  },
  box: {
    type: Object,
    default: null,
  },
  i: {
    type: Number,
    required: true,
  },
  type: {
    type: String,
    required: true,
  },
  matchType: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["close"]);

const selected = ref({});
const filter = ref("");
const searchitems = ref([]);
const loading = ref(false);

watch(
  () => filter.value,
  _.debounce((val) => {
    loading.value = true;
    if (val.length > 0) {
      switch (props.type) {
        case "categories":
          standardizationRepository
            .getCategories({
              perPage: 99999999,
              page: 1,
              filter: val,
            })
            .then((response) => {
              searchitems.value = response.data;
            })
            .catch((error) => {
              handleError(error);
            })
            .finally(() => {
              loading.value = false;
            });
          break;

        case "boxes":
          standardizationRepository
            .getBoxes({
              perPage: 99999999,
              page: 1,
              filter: val,
            })
            .then((response) => {
              searchitems.value = response.data;
            })
            .catch((error) => {
              handleError(error);
            })
            .finally(() => {
              loading.value = false;
            });
          break;

        case "options":
          standardizationRepository
            .getOptions({
              perPage: 99999999,
              page: 1,
              filter: val,
            })
            .then((response) => {
              searchitems.value = response.data;
            })
            .catch((error) => {
              handleError(error);
            })
            .finally(() => {
              loading.value = false;
            });
          break;

        default:
          break;
      }
    }
  }, 300),
);

// methods
const attach = (slug, data) => {
  const payload = {
    slug: data.slug,
    tenant_id: data.tenant_id,
    type: props.matchType,
  };
  switch (props.type) {
    case "categories": {
      standardizationRepository
        .attachCategory(slug, payload)
        .then((response) => {
          handleSuccess(response);
          emit("close");
        })
        .catch((error) => {
          handleError(error);
        });
      break;
    }
    case "boxes": {
      standardizationRepository
        .attachBox(slug, payload)
        .then((response) => {
          handleSuccess(response);
          emit("close");
        })
        .catch((error) => {
          handleError(error);
        });
      break;
    }
    case "options": {
      standardizationRepository
        .attachOption(slug, payload)
        .then((response) => {
          handleSuccess(response);
          emit("close");
        })
        .catch((error) => {
          handleError(error);
        });
      break;
    }
    default:
      break;
  }
};
</script>
