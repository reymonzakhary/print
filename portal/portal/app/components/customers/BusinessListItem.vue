<template>
  <article>
    <UICard
      class="relative mt-2 text-sm rounded transition-colors duration-75 cursor-pointer hover:bg-theme-50 dark:hover:bg-gray-700"
    >
      <div class="grid grid-cols-5 items-center p-2">
        <div class="grid grid-cols-[30px_,_1fr] gap-2 items-center">
          <div class="p-1 w-full bg-white rounded aspect-square text-theme-200">
            <img
              v-if="customer.type === 'business'"
              id="prindustry-logo"
              :src="customer.logo"
              alt="Prindustry Logo"
              class="object-cover w-full rounded shadow-sm aspect-square"
            />
            <font-awesome-icon v-else :icon="['fa', 'user-circle']" class="w-full text-xl" />
          </div>
          <div>
            <h1 class="font-bold text-gray-700">{{ customer.name }}</h1>
          </div>
        </div>
        <span>{{ customer.email }}</span>
        <span>{{ customer.phone }}</span>
        <span>{{ customer.orders }} orders</span>
        <span>{{ getCurrencySymbol() }} {{ customer.revenue }}</span>
      </div>
    </UICard>
  </article>
</template>

<script setup>
const { customer } = defineProps({
  customer: {
    type: Object,
    required: true,
    validator: (value) => {
      const requiredList = ["id", "name", "email", "phone", "orders", "revenue"];
      if (value.type === "business") requiredList.push("logo");
      return requiredList.every((key) => key in value);
    },
  },
});

const { getCurrencySymbol } = useMoney();
</script>
