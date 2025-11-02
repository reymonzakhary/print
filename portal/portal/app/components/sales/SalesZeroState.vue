<template>
  <article>
    <div class="text-center">
      <h1 class="text-xl font-bold text-gray-400">
        {{ $t("You did not add any items yet!") }}
      </h1>
      <div class="my-4 -ml-10 flex justify-center">
        <font-awesome-icon :icon="['fal', 'clouds']" class="fa-3x m-4 text-gray-300" />
        <font-awesome-icon :icon="['fad', 'cactus']" class="fa-5x my-4 text-gray-400" />
        <font-awesome-icon :icon="['fal', 'clouds']" class="fa-2x my-4 text-gray-300" />
      </div>
      <div v-if="hasPermission('quotations-items-create')" class="flex flex-col">
        <SalesAddButton
          v-if="hasPermissionGroup(permissions.quotations.groups.addFinderProduct)"
          :icon="['fal', 'radar']"
          class="mb-4"
          @click="emit('onFinderProduct')"
        >
          {{ $t("Add finder product") }}
        </SalesAddButton>
        <SalesAddButton
          v-if="hasPermissionGroup(permissions.quotations.groups.addAssortmentProduct)"
          :icon="['fal', 'radar']"
          class="mb-4"
          @click="emit('onAssortmentProduct')"
        >
          {{ $t("Add assortment product") }}
        </SalesAddButton>
        <SalesAddButton
          v-if="hasPermissionGroup(permissions.quotations.groups.addOpenProduct)"
          :icon="['fal', 'radar']"
          class="mb-4"
          @click="emit('onOpenProduct')"
        >
          {{ $t("Add open product") }}
        </SalesAddButton>
      </div>
    </div>
  </article>
</template>

<script setup>
defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["onOpenProduct", "onAssortmentProduct", "onFinderProduct"]);

const { hasPermissionGroup, hasPermission, permissions } = usePermissions();
</script>
