<template>
  <UILoader v-if="isLoading" />
  <UISwitchList v-else>
    <UISwitchListItem
      v-for="(context, index) in contexts"
      :key="'user_role_' + index"
      :name="$display_name(context.name)"
      :label="$display_name(context.name)"
      :model-value="selectedContexts.includes(context.id)"
      :disabled="disabled"
      @update:model-value="updateUserContexts(context)"
    />
  </UISwitchList>
</template>

<script setup>
const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});
const emit = defineEmits(["change"]);

const { data: contexts } = await useLazyAPI("/contexts", { transform: ({ data }) => data });
const isLoading = computed(() => contexts?.value?.length === 0);

const selectedContexts = computed(() => props.user.ctx.map((role) => role?.id ?? role));

const updateUserContexts = (ctx) => {
  if (selectedContexts.value.includes(ctx.id)) {
    const index = selectedContexts.value.indexOf(ctx.id);
    selectedContexts.value.splice(index, 1);
  } else {
    selectedContexts.value.push(ctx.id);
  }
  emit("change", selectedContexts.value);
};
</script>
