<template>
  <article>
    <UICard
      class="relative mt-2 rounded text-sm transition-colors duration-75 dark:hover:bg-gray-700"
    >
      <div class="grid grid-cols-4 items-center p-2">
        <div class="grid grid-cols-[30px_,_1fr] items-center gap-2">
          <div class="aspect-square w-full rounded bg-white p-1 text-theme-200">
            <font-awesome-icon :icon="['fa', 'user-circle']" class="w-full text-xl" />
          </div>
          <div>
            <h1 class="font-bold text-gray-700 dark:text-theme-50">{{ customer.name }}</h1>
          </div>
        </div>
        <span>{{ customer.email }}</span>
        <span>{{ customer.orders }} orders</span>
        <span class="flex">
          <button
            class="mt-2 rounded-full border border-red-500 px-2 py-1 text-sm text-red-500 transition-colors hover:bg-red-100"
            :title="$t('Restore')"
            @click="restore(customer.id)"
          >
            <font-awesome-icon :icon="verifiedIcon" class="text-red-700" />
          </button>
        </span>
      </div>
    </UICard>
  </article>
</template>

<script setup>
const { customer, getTrashed } = defineProps({
  customer: {
    type: Object,
    required: true,
  },
  getTrashed: {
    type: Function,
    required: true,
  },
});

const api = useAPI();
const { handleError, handleSuccess } = useMessageHandler();

const verifiedIcon = computed(() => {
  return ["fad", "trash-undo"];
});

const restore = async (id) => {
  try {
    const response = await api.post(`/members/${id}/restore`, {});
    handleSuccess(response);
    getTrashed("trashed");
  } catch (error) {
    handleError(error);
  }
};

// const verifiedIconColor = computed(() => {
//   return customer.verified ? "text-green-700" : "text-orange-700";
// });
</script>
