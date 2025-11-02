<template>
  <div
    class="group relative size-24 cursor-pointer overflow-hidden rounded-md border border-gray-200 bg-gray-50 shadow-sm shadow-gray-200 transition-all duration-300 ease-in-out dark:border-gray-900 dark:bg-gray-700 dark:shadow-gray-900"
    :class="[selected ? 'border-theme-400' : 'border-gray-200 hover:border-theme-200']"
    @click="toggleProducer(producer.id)"
  >
    <!-- Logo Background -->
    <div class="absolute inset-0 p-2">
      <NuxtImg
        v-if="producer.logo"
        :src="producer.logo"
        :alt="producer.name"
        class="h-full w-full object-contain"
      />
      <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
        <font-awesome-icon :icon="['fas', 'building']" class="text-2xl" />
      </div>
      <!-- Variant Count Badge -->
      <div
        class="absolute bottom-1.5 right-1.5 rounded-full bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-700 shadow-sm transition-colors duration-300 group-hover:bg-theme-50 group-hover:text-theme-700 dark:bg-gray-700 dark:text-gray-300"
        :class="{ 'bg-theme-50 text-theme-700': selected }"
      >
        {{ producer.variants }}
      </div>
    </div>
    <!-- <div
      class="relative size-24 overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 transition-all duration-300"
      :class="[selected ? 'border-theme-400' : 'border-gray-200 hover:border-theme-200']"
    >
      <div class="relative flex h-full w-full flex-col items-center justify-center p-4">
        <div
          class="mb-1 max-w-full truncate px-1 text-center text-sm font-medium text-gray-700 transition-colors duration-300 group-hover:text-gray-900"
          :class="{ 'text-theme-700': selected }"
          :title="producer.name"
        >
          {{ producer.name }}
        </div>

        <div
          class="rounded-full px-2.5 py-0.5 text-xs font-medium tracking-wide transition-colors duration-300"
          :class="[
            selected
              ? 'bg-theme-100 text-primary-700'
              : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200',
          ]"
        >
          {{ producer.variants }} {{ $t("variants") }}
        </div>
      </div>

      <div
        v-if="selected"
        class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-theme-100 text-theme-500"
      >
        <font-awesome-icon :icon="['fas', 'check']" size="0.7em" />
      </div>
    </div> -->
  </div>
</template>

<script setup>
const props = defineProps({
  producer: {
    type: Object,
    required: true,
  },
  selected: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["toggle-producer"]);
const toggleProducer = () => emit("toggle-producer", props.producer.id);
</script>
