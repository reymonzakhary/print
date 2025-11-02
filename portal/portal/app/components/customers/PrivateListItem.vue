<template>
  <article>
    <UICard
      class="relative mt-2 cursor-pointer rounded text-sm transition-colors duration-75 hover:bg-theme-50 dark:hover:bg-gray-700"
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
        <font-awesome-icon :icon="verifiedIcon" :class="verifiedIconColor" />
      </div>
    </UICard>
  </article>
</template>

<script setup>
const { customer } = defineProps({
  customer: {
    type: Object,
    required: true,
  },
});

const verifiedIcon = computed(() => {
  return customer.verified ? ["fad", "user-check"] : ["fad", "triangle-exclamation"];
});

const verifiedIconColor = computed(() => {
  return customer.verified ? "text-green-700" : "text-orange-700";
});
</script>
