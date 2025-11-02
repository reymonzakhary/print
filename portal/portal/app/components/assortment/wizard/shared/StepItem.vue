<template>
  <button
    @click="$emit('click')"
    :disabled="!accessible"
    :class="[
      'group relative flex w-full items-center rounded-lg p-3 text-left transition-all duration-200',
      // RACE-SAFE STATE CLASSES: Uses computed stepState to prevent conflicts
      stepState.isCurrent
        ? 'bg-theme-500 text-white shadow-md'
        : stepState.isCompleted
          ? 'bg-green-100 text-gray-700 ring-1 ring-green-500 dark:bg-green-800 dark:text-gray-300'
          : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
      !accessible ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
      optional ? 'border-l-4 border-gray-300 dark:border-gray-600' : '',
      critical ? 'border-l-4 border-red-400' : '',
    ]"
  >
    <!-- Step Number/Icon -->
    <div class="mr-3 flex-shrink-0">
      <div
        :class="[
          'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold',
          // RACE-SAFE ICON CLASSES: Uses computed stepState to prevent conflicts
          stepState.isCurrent
            ? 'bg-white text-theme-500'
            : stepState.isCompleted
              ? 'bg-green-500 text-white'
              : 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300',
        ]"
      >
        <!-- RACE-SAFE ICON DISPLAY: Clear priority prevents conflicting icons -->
        <font-awesome-icon v-if="stepState.isCompleted" :icon="['fas', 'check']" class="text-xs" />
        <font-awesome-icon v-else-if="icon" :icon="['fal', icon]" class="text-xs" />
        <span v-else>{{ stepNumber }}</span>
      </div>
    </div>

    <!-- Step Content -->
    <div class="min-w-0 flex-1">
      <div class="flex items-center justify-between">
        <h4 class="truncate text-sm font-medium">
          {{ title }}
        </h4>
        <div class="ml-2 flex items-center space-x-1">
          <span v-if="optional" class="text-xs text-gray-400">{{ $t("Optional") }}</span>
          <span v-if="critical" class="text-xs text-red-400">{{ $t("Critical") }}</span>
        </div>
      </div>
      <p
        class="mt-1 truncate text-xs"
        :class="stepState.isCurrent ? 'text-theme-100' : 'text-gray-600 dark:text-gray-300'"
      >
        {{ description }}
      </p>
    </div>

    <!-- Arrow Indicator -->
    <div v-if="stepState.isCurrent" class="ml-2 flex-shrink-0">
      <font-awesome-icon :icon="['fas', 'chevron-right']" class="text-xs text-white" />
    </div>
  </button>
</template>

<script setup>
import { computed } from "vue";

const { t: $t } = useI18n();

const props = defineProps({
  stepNumber: {
    type: Number,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: "",
  },
  icon: {
    type: String,
    default: null,
  },
  current: {
    type: Boolean,
    default: false,
  },
  completed: {
    type: Boolean,
    default: false,
  },
  accessible: {
    type: Boolean,
    default: true,
  },
  optional: {
    type: Boolean,
    default: false,
  },
  critical: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["click"]);

// ========================================================================
// COMPUTED PROPERTIES - Race condition prevention
// ========================================================================

/**
 * NORMALIZED STEP STATE - Prevents race conditions
 *
 * Ensures mutually exclusive states with clear priority:
 * Priority: current > completed > default
 *
 * ⚠️  RACE CONDITION PREVENTION:
 * A step should never be both current AND completed simultaneously.
 * Current takes priority to show active state clearly.
 */
const stepState = computed(() => {
  // Current step takes absolute priority
  if (props.current) {
    return {
      isCurrent: true,
      isCompleted: false, // Current overrides completed
      isDefault: false,
    };
  }

  // Completed state (only if not current)
  if (props.completed) {
    return {
      isCurrent: false,
      isCompleted: true,
      isDefault: false,
    };
  }

  // Default state
  return {
    isCurrent: false,
    isCompleted: false,
    isDefault: true,
  };
});

/**
 * DEVELOPMENT WARNING - State validation
 *
 * Logs warning if parent tries to set conflicting states.
 * Helps developers identify race condition sources.
 */
if (process.env.NODE_ENV === "development") {
  if (props.current && props.completed) {
    console.warn(
      "StepItem: Conflicting states detected! Step cannot be both current and completed. Current takes priority.",
      {
        stepNumber: props.stepNumber,
        title: props.title,
        current: props.current,
        completed: props.completed,
      },
    );
  }
}
</script>
