<template>
  <div class="flex">
    <div class="flex flex-col w-3/6 pb-10">
      <section
        class="bg-white dark:bg-gray-700 -mr-10 mt-12 p-4"
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
        class="bg-blue-100 dark:bg-blue-800 -mr-10 p-4"
        style="clip-path: polygon(0 0, 100% 0, 0 670%)"
      >
        <p class="text-xs font-bold tracking-wide text-blue-500 dark:text-blue-400 uppercase">
          Prindustry
        </p>
        <p class="font-bold text-2xl dark:text-white">
          {{
            match.category
              ? match.category[0].name
              : match.box
                ? match.box[0].name
                : match.option[0].name
          }}
        </p>
      </section>
    </div>

    <div class="flex items-center w-3/6">
      <div class="ml-20 items-center flex">
        <small class="text-xs text-gray-500 dark:text-gray-400 mr-2">similarity</small>
        <span
          class="px-2 py-1 font-bold rounded text-white"
          :class="{
            'bg-gradient-to-r from-pink-500 via-purple-500 to-cyan-500 inline-block':
              match.percentage && match.percentage >= 85,
            'bg-gradient-to-r from-pink-500 via-purple-500 to-yellow-500 inline-block ':
              match.percentage && match.percentage <= 85,
          }"
        >
          {{ Math.round((match.percentage + Number.EPSILON) * 100) / 100 }}%
        </span>
      </div>
      <font-awesome-icon :icon="['fal', 'arrow-right']" class="mx-4 dark:text-white" />
      <button
        class="w-24 px-2 py-1 font-bold text-blue-500 border border-blue-500 dark:border-blue-300 dark:text-blue-300 rounded hover:bg-blue-100"
        @click="
          attach(
            match.category
              ? match.category[0].slug
              : match.box
                ? match.box[0].slug
                : match.option[0].slug,
            match,
          )
        "
      >
        ATTACH!
      </button>
    </div>
  </div>
</template>

<script setup>
const standardizationRepository = useStandardizationRepository();
const { handleError, handleSuccess } = useMessageHandler();

// component state
const props = defineProps({
  match: {
    type: Object,
    required: true,
  },
  item: {
    type: Object,
    required: true,
  },
  type: {
    type: String,
    default: "",
  },
  matchType: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["close"]);

// methods
const attach = (slug, data) => {
  const payload = {
    slug: data.slug,
    tenant_id: data.tenant_id,
    type: props.matchType,
  };
  switch (props.type) {
    case "categories":
      standardizationRepository
        .attachCategory(slug, payload)
        .then((response) => {
          emit("close");
          handleSuccess(response);
        })
        .catch((error) => {
          handleError(error);
        });
      break;

    case "boxes":
      standardizationRepository
        .attachBox(slug, payload)
        .then((response) => {
          emit("close");
          handleSuccess(response);
        })
        .catch((error) => {
          handleError(error);
        });
      break;

    case "options":
      standardizationRepository
        .attachOption(slug, payload)
        .then((response) => {
          emit("close");
          handleSuccess(response);
        })
        .catch((error) => {
          handleError(error);
        });
      break;

    default:
      // Default handling if needed
      break;
  }
};

// computed properties
</script>
