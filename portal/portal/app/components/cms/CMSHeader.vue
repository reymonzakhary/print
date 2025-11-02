<template>
  <UICardHeader class="overflow-hidden">
    <template #left>
      <UICardHeaderTitle :title="title" :icon="['fal', 'folder-tree']" />
    </template>
    <template #right>
      <div class="flex">
        <div class="grid items-center px-2 text-theme-50">
          <font-awesome-icon :icon="['fal', 'earth-europe']" />
        </div>

        <UICardHeaderTab label="English" :active="active === 0" @click="$emit('onTabSwitch', 0)" />
        <UICardHeaderTab
          label="Dutch"
          :active="active === 1"
          disabled
          @click="$emit('onTabSwitch', 1)"
        />
        <UICardHeaderTab
          label="French"
          :active="active === 2"
          disabled
          @click="$emit('onTabSwitch', 2)"
        />
      </div>
    </template>
  </UICardHeader>
</template>

<script>
export default {
  name: "CMSHeader",
  props: {
    title: {
      type: String,
      default: "Resource Title",
    },
    active: {
      type: Number,
      default: 0,
    },
  },
  emits: ["onTabSwitch"],
};
</script>
