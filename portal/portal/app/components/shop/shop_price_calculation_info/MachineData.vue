<template>
  <div>
    <div class="mb-2 font-bold tracking-wide text-gray-400 uppercase">
      {{ $t("machine") }}
    </div>
    <div class="py-4 text-lg font-bold">
      <font-awesome-icon class="mr-2 text-2xl" :icon="['fal', 'print']" />
      {{ calculation.machine.name }}
      <span class="px-2 py-1 ml-2 font-mono text-xs rounded bg-theme-50 text-theme-500">
        {{ calculation.machine.type }}
      </span>
    </div>
    <section class="grid grid-cols-2 gap-2 mt-4">
      <div class="row-span-2">
        <div class="text-gray-400">{{ $t("max paper size") }}</div>
        <div class="">
          w {{ calculation.machine.width }}{{ calculation.machine.unit }}
          <br />
          <span class="text-gray-400">x</span> <br />
          h {{ calculation.machine.height }}{{ calculation.machine.unit }}
        </div>
      </div>
      <div class="">
        <div class="text-gray-400">{{ $t("trim area") }}</div>
        <div class="">{{ calculation.machine.trim_area }}{{ calculation.machine.unit }}</div>
      </div>
      <div class="">
        <div class="text-gray-400">{{ $t("trim area exclude y") }}</div>
        <div class="">
          {{ calculation.machine.trim_area_exclude_y }}
        </div>
      </div>
      <div></div>
      <div class="">
        <div class="text-gray-400">{{ $t("trim area exclude x") }}</div>
        <div class="">
          {{ calculation.machine.trim_area_exclude_x }}
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
