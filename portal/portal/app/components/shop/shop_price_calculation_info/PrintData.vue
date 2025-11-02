<template>
  <div>
    <div class="mb-2 font-bold tracking-wide text-gray-400 uppercase">
      {{ $t("print") }}
    </div>
    <div class="py-4 text-lg font-bold">
      <font-awesome-icon class="mr-2 text-2xl" :icon="['fal', 'droplet']" />
      {{ calculation.details.width_with_trim_area_and_bleed }}mm x
      {{ calculation.details.height_with_trim_area_and_bleed }}mm

      <span class="px-2 py-1 ml-2 font-mono text-xs rounded bg-theme-50 text-theme-500">
        {{ calculation.details.ps }}
      </span>
    </div>
    <section class="grid grid-cols-2 gap-2 mt-4">
      <div class="row-span-2">
        <div class="text-gray-400">{{ $t("print size") }}</div>
        <div class="">
          w {{ calculation.details.format_width }}mm <br />
          <span class="text-gray-400">x</span> <br />
          h {{ calculation.details.format_height }}mm
        </div>
      </div>

      <div>
        <div class="text-gray-400">{{ $t("print orientation") }}</div>
        <div class="">
          {{ calculation.details.ps }}
        </div>
      </div>

      <div>
        <div class="text-gray-400">{{ $t("prints per sheet") }}</div>
        <div class="">
          {{ calculation.details.maximum_prints_per_sheet }}
        </div>
      </div>
      <div class="row-span-2">
        <div class="text-gray-400">
          {{ $t("print size + bleed") }}
        </div>
        <div class="">
          w {{ calculation.details.width_with_bleed.toFixed(0) }}mm <br />
          <span class="text-gray-400">x</span> <br />
          h {{ calculation.details.height_with_bleed.toFixed(0) }}mm
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
defineProps({
  calculation: {
    type: Object,
    required: true,
  },
});
</script>
