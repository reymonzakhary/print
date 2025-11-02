<template>
  <div class="flex">
    <UIButton
      :icon="['fal', 'list-dropdown']"
      class="rounded-none rounded-l border-b border-l border-t border-gray-200 hover:border-theme-300"
      :class="{ 'border-theme-300 !bg-theme-200 text-white': props.activeView === 'list' }"
      @click="emits('update:active-view', 'list')"
    />
    <UIButton
      :icon="['fal', 'table-list']"
      class="rounded-none border-b border-l border-r border-t border-gray-200 hover:border-theme-300"
      :class="{ 'border-theme-300 !bg-theme-200 text-white': props.activeView === 'grid' }"
      @click="emits('update:active-view', 'grid')"
    />
    <UIButton
      :icon="['fal', 'bars']"
      class="rounded-none rounded-r border-b border-r border-t border-gray-200 hover:border-theme-300"
      :class="{ 'border-theme-300 !bg-theme-200 text-white': props.activeView === 'items-only' }"
      @click="emits('update:active-view', 'items-only')"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  activeView: {
    type: String,
    required: true,
  },
});

const emits = defineEmits(["update:active-view"]);
</script>
