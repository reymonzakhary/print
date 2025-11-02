<template>
  <div>
    <div class="flex justify-between mb-2 font-bold tracking-wide text-gray-400 uppercase">
      {{ $t("paper") }}
      <span class="ml-2 normal-case text-theme-400 dark:text-theme-400">
        {{ calculation.details.catalogue_supplier }}
      </span>
    </div>
    <div class="py-4 text-lg font-bold">
      <font-awesome-icon class="mr-2 text-2xl" :icon="['fal', 'sticky-note']" />
      {{ calculation.details.material_used }}
      <span class="px-2 py-1 ml-2 font-mono text-xs rounded bg-theme-50 text-theme-500">
        {{ calculation.details.wight_used }} {{ $t("grs") }}
      </span>
    </div>

    <section class="grid grid-cols-2 gap-2 mt-4">
      <div class="row-span-2">
        <div class="text-gray-400">{{ $t("paper size") }}</div>
        <div class="">
          w {{ calculation.details.catalogue_width.toFixed(0) }}mm <br />
          <span class="text-gray-400">x</span> <br />
          h {{ calculation.details.catalogue_height.toFixed(0) }}mm
        </div>
      </div>

      <div>
        <div class="text-gray-400">{{ $t("bleed") }}</div>
        <div class="">
          {{
            (calculation.details.catalogue_width - calculation.details.printable_area_width) / 2
          }}mm
        </div>
      </div>

      <div>
        <div class="text-gray-400">{{ $t("paper type") }}</div>
        <div class="">
          {{ calculation.details.fed }}
        </div>
      </div>

      <div>
        <div class="pt-2 text-gray-400 border-t">{{ $t("sheets needed") }}</div>
        <div class="">x {{ calculation.details.amount_of_sheets_needed }}</div>
      </div>
      <div>
        <div class="pt-2 text-gray-400 border-t">{{ $t("sheets printed") }}</div>
        <div class="">x {{ calculation.details.amount_of_sheets_printed }}</div>
      </div>
      <div>
        <div class="text-gray-400">{{ $t("price per sheet") }}</div>
        <div class="">{{ calculation.details.price_per_sheet }}</div>
      </div>
      <div>
        <div class="text-gray-400">{{ $t("total sheet price") }}</div>
        <div class="">{{ calculation.details.total_sheet_price }}</div>
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
