<template>
  <article>
    <UICard class="backdrop-blur">
      <div class="grid grid-cols-4 py-2 px-3 text-xs font-bold uppercase">
        <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
          {{ $t("name") }}
        </div>
        <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
          {{ $t("email") }}
        </div>
        <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
          {{ $t("phone number") }}
        </div>
        <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
          {{ $t("Teams") }}
        </div>
      </div>
    </UICard>
    <Nuxt-Link
      v-for="employee in employees"
      :key="employee"
      :to="`/customers/business/${businessId}/employee/${employee.id}`"
    >
      <BusinessEmployeeListItem :employee="employee" />
    </Nuxt-Link>
  </article>
</template>

<script setup>
const { businessId, employees } = defineProps({
  businessId: {
    type: [String, Number],
    required: true,
  },
  employees: {
    type: Array,
    required: true,
  },
});
</script>
