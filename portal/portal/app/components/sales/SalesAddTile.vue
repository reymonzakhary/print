<template>
  <article class="h-full min-h-[781px] rounded bg-gray-200/75 p-4 text-center dark:bg-gray-900">
    <h1 class="mt-36 w-full text-xl font-bold text-gray-600">
      {{ $t("add another product from") }}
      <font-awesome-icon
        :icon="['fad', 'people-carry-box']"
        class="fa-4x mx-auto my-8 w-48 text-gray-600"
      />
    </h1>

    <div class="my-2 w-full">
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
        :icon="['fal', 'box-full']"
        class="mb-4"
        @click="emit('onAssortmentProduct')"
      >
        {{ $t("Add assortment product") }}
      </SalesAddButton>
      <SalesAddButton
        v-if="hasPermissionGroup(permissions.quotations.groups.addOpenProduct)"
        :icon="['fal', 'box-open']"
        @click="emit('onOpenProduct')"
      >
        {{ $t("Add open product") }}
      </SalesAddButton>
    </div>
  </article>
</template>

<script setup>
const emit = defineEmits(["onFinderProduct", "onAssortmentProduct", "onOpenProduct"]);

const { permissions, hasPermissionGroup } = usePermissions();
</script>
