<template>
  <div
    :class="[
      { 'dark:border-gray-900': isExpanded },
      $attrs.class?.includes('flex') ? 'flex h-full flex-col' : '',
    ]"
  >
    <StudioTreeSectionHeader
      :title="title"
      :icon="icon"
      :no-toggle="notExpandable"
      :expanded="isExpanded"
      :variant="variant === 'sub-section' ? 'sub' : 'normal'"
      @toggle="toggleExpanded"
    />
    <transition name="accordion">
      <component
        :is="as"
        v-show="isExpanded"
        :class="[
          'py-2 pb-2 pl-4',
          variant === 'sub-section' ? 'pr-0' : 'pr-4',
          $attrs.class?.includes('flex') ? 'flex-1 overflow-hidden' : '',
        ]"
      >
        <slot />
      </component>
    </transition>
  </div>
</template>

<script setup>
const props = defineProps({
  variant: {
    type: String,
    default: "section",
    validator: (value) => ["section", "sub-section"].includes(value),
  },
  expanded: {
    type: Boolean,
    default: true,
  },
  title: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    required: true,
  },
  as: {
    type: String,
    default: "ul",
  },
  notExpandable: {
    type: Boolean,
    default: false,
  },
  managed: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["toggle"]);

const _expanded = ref(props.expanded);
const isExpanded = computed(() => (props.managed ? props.expanded : _expanded.value));
function toggleExpanded() {
  if (!props.managed) {
    _expanded.value = !_expanded.value;
  } else {
    emit("toggle", !isExpanded.value);
  }
}
</script>

<style scoped>
.accordion-enter-active,
.accordion-leave-active {
  transition:
    max-height 0.3s ease,
    opacity 0.3s ease;
  max-height: 200px;
  overflow: hidden;
}

.accordion-enter-from,
.accordion-leave-to {
  max-height: 0;
  opacity: 0;
}
</style>
