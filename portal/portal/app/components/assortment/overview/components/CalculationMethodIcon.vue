<template>
  <div class="relative">
    <VDropdown
      :shown="
        calcMethod.type === 'full_calculation' &&
        activeCategory[0] === category.id &&
        incompleteCalcRefs
      "
      :disabled="calcMethod.type !== 'full_calculation'"
      placement="bottom-start"
      class="mr-2 text-gray-500 dark:text-gray-300"
    >
      <font-awesome-icon
        v-tooltip="calcMethod.tooltip"
        class="mr-2 cursor-pointer"
        :class="{
          '!text-amber-500':
            calcMethod.type === 'full_calculation' &&
            activeCategory[0] === category.id &&
            incompleteCalcRefs,
        }"
        fixed-width
        :icon="['fal', calcMethod.icon]"
      />
      <template #popper>
        <div class="w-64 bg-amber-100 p-2 text-sm">
          <h4 class="mb-2 font-bold">{{ $t("Calculation references") }}</h4>
          <p class="mb-2 text-xs">
            {{ $t("The following calculation references are required for price calculation:") }}
          </p>
          <ul class="space-y-1 text-xs">
            <li
              :class="{
                'text-green-500': necessaryCalcRefs.format,
                'text-orange-500': !necessaryCalcRefs.format,
              }"
            >
              <font-awesome-icon
                v-if="necessaryCalcRefs.format"
                class="ml-1"
                :icon="['fas', 'check']"
              />
              <font-awesome-icon v-else class="ml-1" :icon="['fas', 'exclamation-triangle']" />
              {{ $t("Format") }}
            </li>
            <li
              :class="{
                'text-green-500': necessaryCalcRefs.material,
                'text-orange-500': !necessaryCalcRefs.material,
              }"
            >
              <font-awesome-icon
                v-if="necessaryCalcRefs.material"
                class="ml-1"
                :icon="['fas', 'check']"
              />
              <font-awesome-icon v-else class="ml-1" :icon="['fas', 'exclamation-triangle']" />
              {{ $t("Material") }}
            </li>
            <li
              :class="{
                'text-green-500': necessaryCalcRefs.weight,
                'text-orange-500': !necessaryCalcRefs.weight,
              }"
            >
              <font-awesome-icon
                v-if="necessaryCalcRefs.weight"
                class="ml-1"
                :icon="['fas', 'check']"
              />
              <font-awesome-icon v-else class="ml-1" :icon="['fas', 'exclamation-triangle']" />
              {{ $t("Weight") }}
            </li>
            <li
              :class="{
                'text-green-500': necessaryCalcRefs.printingcolors,
                'text-orange-500': !necessaryCalcRefs.printingcolors,
              }"
            >
              <font-awesome-icon
                v-if="necessaryCalcRefs.printingcolors"
                class="ml-1"
                :icon="['fas', 'check']"
              />
              <font-awesome-icon v-else class="ml-1" :icon="['fas', 'exclamation-triangle']" />
              {{ $t("Printing Colors") }}
            </li>
          </ul>
        </div>
      </template>
    </VDropdown>
  </div>
</template>

<script>
export default {
  name: "CalculationMethodIcon",
  props: {
    category: {
      type: Object,
      required: true,
    },
    calcMethod: {
      type: Object,
      required: true,
    },
    incompleteCalcRefs: {
      type: Boolean,
      required: true,
    },
    activeCategory: {
      type: Array,
      required: true,
    },
    necessaryCalcRefs: {
      type: Object,
      required: true,
    },
  },
};
</script>
