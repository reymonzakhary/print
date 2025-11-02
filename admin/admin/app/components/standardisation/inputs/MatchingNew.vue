<template>
  <div class="">
    <div class="text-xs font-bold tracking-wide text-gray-500 dark:text-gray-400 uppercase">
      Add {{ match.tenant_name }}'s
    </div>
    <section class="flex items-end mt-2 dark:text-white">
      <span class="font-bold text-2xl mr-4">{{ match.name ? match.name : match.object.name }}</span>
      as
      <div class="flex items-center justify-between ml-4">
        <input
          v-model="new_name"
          type="text"
          class="w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-blue-300"
        />
        <button
          class="w-24 px-2 py-1 ml-2 font-bold text-blue-500 border border-blue-500 dark:border-blue-300 dark:text-blue-300 rounded hover:bg-blue-100"
          @click="add()"
        >
          ADD!
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
const standardizationRepository = useStandardizationRepository();
const { handleError, handleSuccess } = useMessageHandler();

const props = defineProps({
  match: { type: Object, default: () => ({}) },
  type: { type: String, default: "" },
  matchType: { type: String, default: "" },
});

const emit = defineEmits(["on-add"]);

const new_name = ref(props.match.name ? props.match.name : props.match.object.name);

const add = () => {
   const payload = {
    slug: props.match.slug,
    tenant_id: props.match.tenant_id,
    type: props.matchType,
  };
  switch (props.type) {
    case "categories":
      standardizationRepository
        .newCategory({
          name: new_name.value,
          tenant_id: props.match.id,
        })
        .then((response) => {
          handleSuccess(response);
          standardizationRepository
            .attachCategory(response.data.slug, payload)
            .then((response) => {
              handleSuccess(response);
              emit(`on-add`);
            })
            .catch((error) => {
              handleError(error);
            });
        })
        .catch((error) => {
          handleError(error);
        });
      break;

    case "boxes":
      standardizationRepository
        .newBox({
          name: new_name.value,
          tenant_id: props.match.id,
        })
        .then((response) => {
          handleSuccess(response);
          standardizationRepository
            .attachBox(response.data.slug, payload)
            .then((response) => {
              handleSuccess(response);
              emit(`on-add`);
            })
            .catch((error) => {
              handleError(error);
            });
        })
        .catch((error) => {
          handleError(error);
        });
      break;

    case "options":
      standardizationRepository
        .newOption({
          name: new_name.value,
          tenant_id: props.match.id,
        })
        .then((response) => {
          handleSuccess(response);
          standardizationRepository
            .attachOption(response.data.slug, payload)
            .then((response) => {
              handleSuccess(response);
              emit(`on-add`);
            })
            .catch((error) => {
              handleError(error);
            });
        })
        .catch((error) => {
          handleError(error);
        });
      break;

    default:
      break;
  }
};
</script>
