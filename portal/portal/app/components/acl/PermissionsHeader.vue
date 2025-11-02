<template>
  <UICardHeader>
    <template #left>
      <div class="flex items-center">
        <UICardHeaderTitle
          :title="$display_name(selectedRole.display_name)"
          :icon="['fal', 'tag']"
        />
        <span class="ml-2 font-normal lowercase tracking-normal">
          {{ selectedRole.description }}
        </span>
        <PermissionsInfoDropdown />
      </div>
    </template>
    <template #right>
      <div class="flex items-center justify-end">
        <transition name="list">
          <div v-if="hasChanges && canUpdate" class="text-sm text-themecontrast-500">
            <font-awesome-icon :icon="['fal', 'triangle-exclamation']" />
            {{ $t("You made changes...") }}
            <button
              class="ml-4 rounded-full bg-green-500 px-4 py-1 text-base text-white"
              @click="$emit('save')"
            >
              {{ $t("save") }}
            </button>
          </div>
        </transition>
        <button class="rounded-full text-white lg:hidden" @click="$emit('close')">
          <font-awesome-icon :icon="['fad', 'circle-xmark']" />
        </button>
      </div>
    </template>
  </UICardHeader>
</template>

<script setup>
defineProps({
  selectedRole: {
    type: Object,
    required: true,
  },
  hasChanges: {
    type: Boolean,
    default: false,
  },
  canUpdate: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["save", "close"]);
</script>
