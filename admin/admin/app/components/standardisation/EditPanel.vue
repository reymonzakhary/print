<template>
  <!-- <div> -->
  <UIModalSlideIn
    class="w-2/3"
    :icon="['fas', 'pencil']"
    :title="`Edit ${type}: ${item.name}`"
    :show="props.show"
    @on-close="emit('close', $event)"
    @on-backdrop-click="emit('close', $event)"
  >
    <div class="p-4 h-full w-full text-sm flex gap-4">
      <div class="relative block w-1/2 p-4 my-8 border dark:border-black rounded">
        <EditForm
          :selected="selected && Object.keys(selected)?.length > 0 ? selected : item"
          :type="type"
          :category="category"
          :box="box"
        />
      </div>

      <transition name="slide">
        <div
          v-if="responseMessage !== ''"
          :class="`bg-${responseStatusColor}-500 text-${responseStatusColor}-800 rounded p-2`"
        >
          {{ responseMessage }}
        </div>
      </transition>

      <!-- CONNECTED SUPPLIER ITEMS -->
      <section class="w-1/2 dark:text-white">
        <div class="mt-4 mb-2 text-sm font-bold tracking-wide uppercase">
          <font-awesome-icon :icon="['fal', 'link']" class="mr-1" />
          <font-awesome-icon :icon="['fal', 'parachute-box']" class="mr-1" />
          <span class="text-gray-500">Connected supplier </span>
          {{ type === "category" ? "categories" : type === "box" ? "boxes" : "options" }}
          <div v-if="selectedSuppliers.length > 0">
            <button
              class="flex items-center px-2 py-1 text-sm text-red-500 rounded hover:text-red-600 hover:bg-red-100"
              @click="detachSelectedSuppliers(selectedSuppliers)"
            >
              <font-awesome-icon :icon="['fal', 'unlink']" class="mr-2" />
              Detach {{selectedSuppliers.length}} selected
            </button>
          </div>
        </div>

        <ul class="divide-y dark:divide-black">
          <li
            v-for="(supplierItem, i) in item.suppliers"
            :key="supplierItem.id"
            class="p-2 my-2 transition-colors first:rounded-t last:rounded-b"
          >
            <div class="flex items-center justify-between">
              <span class="flex">
                <input
                  :id="`multiselect_${i}`"
                  :selected="selectedSuppliers.find((item) => item === supplierItem.id)"
                  type="checkbox"
                  :name="`multiselect_${i}`"
                  class="mr-4"
                  @change="toggleSupplierSelection(supplierItem)"
                />
                <span class="">
                  {{ supplierItem.tenant_name }}
                </span>
                's
                <span class="ml-2 font-bold">
                  {{ supplierItem.name }}
                </span>
              </span>
              <span class="flex">
                <!-- <button
                  v-if="editLinked !== supplierItem.slug"
                  class="flex items-center px-2 py-1 text-sm text-blue-500 rounded hover:text-blue-600 hover:bg-blue-100"
                  @click="editLinked = supplierItem.slug"
                >
                  <font-awesome-icon :icon="['fal', 'pencil-alt']" class="mr-2" />
                  Edit
                </button> -->
                <button
                  v-if="editLinked !== supplierItem.slug"
                  class="flex items-center px-2 py-1 text-sm text-red-500 rounded hover:text-red-600 hover:bg-red-100"
                  @click="detachSupplier(supplierItem)"
                >
                  <font-awesome-icon :icon="['fal', 'unlink']" class="mr-2" />
                  Detach
                </button>
                <button
                  v-else
                  class="flex items-center px-2 py-1 text-sm text-gray-500 rounded hover:text-gray-600"
                  @click="editLinked = false"
                >
                  <font-awesome-icon :icon="['fal', 'times-circle']" class="mr-2" />
                  Close
                </button>
              </span>
            </div>

            <!-- <transition-group name="fade">
              <MatchingSelect
                v-if="editLinked === supplierItem.slug"
                key="matching_select"
                :items="items"
                :match="supplierItem"
                :i="i"
                match-type="suppliers"
                class="w-full px-4 bg-gray-100"
              />
              <MatchingNew
                v-if="editLinked === supplierItem.slug"
                key="matching_new"
                :items="items"
                :match="supplierItem"
                :i="i"
                match-type="suppliers"
                class="w-full px-4 bg-gray-100"
              />
            </transition-group> -->
          </li>
        </ul>
      </section>
    </div>
  </UIModalSlideIn>
</template>

<script setup>
// import UIModalSlideIn from "../global/ui/modal/UIModalSlideIn.vue";
import EditForm from "./inputs/EditForm.vue";

const standardizationRepository = useStandardizationRepository();
const { handleError, handleSuccess } = useMessageHandler();

const props = defineProps({
  item: {
    type: Object,
    default: () => ({}),
  },
  items: {
    type: Array,
    default: () => [],
  },
  category: {
    type: Object,
    default: () => ({}),
  },
  box: {
    type: Object,
    default: () => ({}),
  },
  type: {
    type: String,
    default: "",
  },
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close", "detached", "on-detached"]);

const selected = ref(props.item);
const edit = ref(null);
const editLinked = ref(false);
const modal = ref("");
const responseMessage = ref("");
const responseStatusColor = ref("");

const onClose = () => {
  emit("close");
};

const toggleSupplierSelection = (supplierItem) => {
  const existingIndex = selectedSuppliers.value.findIndex((item) => item._id === supplierItem._id);
  console.log("existingIndex", existingIndex);
  if (existingIndex !== -1) {
    // If item exists in array, remove it
    selectedSuppliers.value.splice(existingIndex, 1);
  } else {
    // If item doesn't exist in array, add it
    selectedSuppliers.value.push(supplierItem);
  }
};
const selectedSuppliers = ref([]);

const detachSelectedSuppliers = () => {
  const selectedItems = Object.values(selectedSuppliers.value).filter((item) => item);
  if (selectedItems.length > 0) {
    selectedItems.forEach((supplierItem) => {
      detachSupplier(supplierItem, true);
    });
    selectedSuppliers.value = [];
    emit("on-detached");
  } else {
    handleError("No suppliers selected for detachment.");
  }
};

const detachSupplier = (supplierItem, multiple = false) => {
  const payload = {
    slug: supplierItem.slug ?? props.item.slug,
    tenant_id: supplierItem.tenant_id,
    type: "suppliers",
  };
  switch (props.type) {
    case "categories":
      standardizationRepository
        .detachCategory(props.item.slug, payload)
        .then((response) => {
          handleSuccess(response);
          if (!multiple) emit("on-detached");
        })
        .catch((error) => {
          handleError(error);
        });
      break;
    case "boxes":
      standardizationRepository
        .detachBox(props.item.slug, payload)
        .then((response) => {
          handleSuccess(response);
          if (!multiple) emit("on-detached");
        })
        .catch((error) => {
          handleError(error);
        });
      break;
    case "options":
      standardizationRepository
        .detachOption(props.item.slug, payload)
        .then((response) => {
          handleSuccess(response);
          if (!multiple) emit("on-detached");
        })
        .catch((error) => {
          handleError(error);
        });
      break;
  }
};
</script>

<style></style>
