<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <font-awesome-icon :icon="['fal', 'trash']" class="mr-2" />
      Delete {{ item.name }}
    </template>

    <template #modal-body>
      <form class="flex flex-col flex-wrap p-8">
        <span>
          <font-awesome-icon :icon="['fal', 'trash']" class="mr-2" />
          Delete {{ type }} <span class="font-bold">{{ item.name }}</span>
        </span>

        <fieldset class="my-4 text-sm">
          <input id="force_delete" type="checkbox" name="force_delete" @change="force = !force" />
          <label for="force_delete">
            force delete?
            <span class="ml-2 text-gray-500">
              unlinks all related
              {{ type === "category" ? "boxes" : "options" }}
            </span>
          </label>
        </fieldset>

        <fieldset v-if="force" class="my-4">
          <div class="text-yellow-600">
            <font-awesome-icon :icon="['fal', 'exclamation-triangle']" class="mr-2" />
            Warning, this will unlink ALL
            {{ type === "category" ? "boxes" : "options" }} and DELETE the {{ type }}. This cannot
            be undone!
          </div>
        </fieldset>
      </form>
    </template>

    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-red-600 rounded-full hover:bg-red-800"
        :class="{ 'border-2 border-yellow-400': force }"
        :disabled="loading"
        @click="deleteItem(item)"
      >
        <font-awesome-icon :icon="['fal', 'trash']" />
        {{ force ? "Force" : "" }} Delete
      </button>
    </template>
  </ConfirmationModal>
</template>

<script setup>
const standardizationRepository = useStandardizationRepository();
const { handleSuccess, handleError } = useMessageHandler();

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  type: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["close", "on-force-delete", "on-delete", "on-deleted"]);
const force = ref(false);

const deleteItem = (e) => {
  switch (props.type) {
    case "categories":
      standardizationRepository
        .deleteCategory(e, force.value)
        .then((response) => {
          handleSuccess(response);
          emit("on-deleted");
        })
        .catch((error) => {
          handleError(error);
        })
        .finally(() => {
          closeModal();
        });
      break;
    case "boxes":
      standardizationRepository
        .deleteBox(e, force.value)
        .then((response) => {
          handleSuccess(response);
          emit("on-deleted");
        })
        .catch((error) => {
          handleError(error);
        })
        .finally(() => {
          closeModal();
        });
      break;
    case "options":
      standardizationRepository
        .deleteOption(e, force.value)
        .then((response) => {
          handleSuccess(response);
          emit("on-deleted");
        })
        .catch((error) => {
          handleError(error);
        })
        .finally(() => {
          closeModal();
        });
      break;

    default:
      break;
  }
};

const closeModal = () => {
  emit("close");
};
</script>
