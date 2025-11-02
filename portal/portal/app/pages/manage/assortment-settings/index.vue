<template>
  <div>
    <header
      v-if="permissions.includes('print-assortments-access')"
      class="my-4 flex w-full items-center justify-center text-center text-lg font-bold uppercase tracking-wide"
    >
      <font-awesome-icon :icon="['fal', 'box-full']" />
      <h1>{{ $t("assortment settings") }}</h1>
    </header>

    <header class="mb-4 mt-8 w-full text-center text-sm font-bold uppercase tracking-wide">
      <font-awesome-icon :icon="['fal', 'print']" />
      {{ $t("print product settings") }}
    </header>
    <div class="container grid grid-cols-2 gap-4 p-4 lg:grid-cols-4">
      <nuxt-link
        v-if="
          permissions.includes('print-assortments-machines-access') ||
          permissions.includes('print-assortments-catalogues-access') ||
          permissions.includes('print-assortments-system-catalogues-access')
        "
        to="/manage/assortment-settings/full-calculation"
        class="h-full w-full rounded border-gray-200 bg-white py-8 text-center shadow-md transition-shadow duration-150 hover:shadow-lg dark:border-gray-900 dark:bg-gray-700"
      >
        <font-awesome-icon class="fa-4x text-theme-400" :icon="['fad', 'print']" />
        +
        <font-awesome-icon class="fa-4x text-theme-400" :icon="['fad', 'layer-group']" />
        <h2 class="mt-4 font-semibold">
          <span v-if="permissions.includes('print-assortments-machines-access')">
            {{ capitalizeFirstLetter($t("machine")) }}
          </span>
          <span
            v-if="
              permissions.includes('print-assortments-machines-access') &&
              permissions.includes('print-assortments-catalogues-access')
            "
          >
            &
          </span>
          <span v-if="permissions.includes('print-assortments-machines-access')">
            {{ capitalizeFirstLetter($t("catalogue")) }}
          </span>
        </h2>
        <p class="mt-2 text-sm text-gray-400">
          {{ $t("settings") }}
        </p>
      </nuxt-link>
      <nuxt-link
        v-if="
          permissions.includes('print-assortments-boxes-access') ||
          permissions.includes('print-assortments-options-access')
        "
        to="assortment-settings/semi-calculation"
        class="h-full w-full rounded border-gray-200 bg-white px-4 py-8 text-center shadow-md transition-shadow duration-150 hover:shadow-lg dark:border-gray-900 dark:bg-gray-700"
      >
        <font-awesome-icon class="fa-4x text-theme-400" :icon="['fad', 'box-open']" />
        +
        <font-awesome-icon class="fa-4x text-theme-400" :icon="['fad', 'shapes']" />
        <h2 class="mt-4 font-semibold">
          <span v-if="permissions.includes('print-assortments-boxes-access')">
            {{ capitalizeFirstLetter($t("boxes")) }}
          </span>
          <span
            v-if="
              permissions.includes('print-assortments-boxes-access') &&
              permissions.includes('print-assortments-options-access')
            "
          >
            &
          </span>
          <span v-if="permissions.includes('print-assortments-options-access')">
            {{ capitalizeFirstLetter($t("Options")) }}
          </span>
        </h2>
        <p class="mt-2 text-sm text-gray-400">
          {{ $t("settings") }}
        </p>
      </nuxt-link>
      <nuxt-link
        v-if="permissions.includes('print-assortments-printing-methods-access')"
        to="assortment-settings/printing-methods"
        class="h-full w-full rounded border-gray-200 bg-white px-4 py-8 text-center shadow-md transition-shadow duration-150 hover:shadow-lg dark:border-gray-900 dark:bg-gray-700"
      >
        <font-awesome-icon class="fa-4x text-theme-400" :icon="['fad', 'fill-drip']" />
        <h2 class="mt-4 font-semibold">{{ capitalizeFirstLetter($t("Printing methods")) }}</h2>
        <p class="mt-2 text-sm text-gray-400">
          {{ capitalizeFirstLetter($t("manage printing methods")) }}
        </p>
      </nuxt-link>
      <nuxt-link
        v-if="permissions.includes('print-assortments-margins-access')"
        to="assortment-settings/margins"
        class="h-full w-full rounded border-gray-200 bg-white px-4 py-8 text-center shadow-md transition-shadow duration-150 hover:shadow-lg dark:border-gray-900 dark:bg-gray-700"
      >
        <font-awesome-icon class="fa-4x text-theme-400" :icon="['fad', 'hand-holding-dollar']" />
        <h2 class="mt-4 font-semibold">{{ capitalizeFirstLetter($t("margins")) }}</h2>
        <p class="mt-2 text-sm text-gray-400">
          {{ capitalizeFirstLetter($t("manage margins")) }}
        </p>
      </nuxt-link>

      <!-- <div class="w-full sm:w-4/6 lg:w-5/6">
            <transition name="fade">
               <Calculation v-if="active_nav === 'full'"></Calculation>
               <Calculation v-if="active_nav === 'semi'"></Calculation>
               <PrintingMethods v-if="active_nav === 'pm'"></PrintingMethods>
               <ProductionDays v-if="active_nav === 'dlv_days'" />
               <Margins v-if="active_nav === 'margins'" />
               <Variations v-if="active_nav === 'variations'" />
            </transition>
         </div> -->
    </div>
    <header class="mb-4 mt-8 w-full text-center text-sm font-bold uppercase tracking-wide">
      <font-awesome-icon :icon="['fal', 'box']" />
      {{ $t("Custom product settings") }}
    </header>
    <div class="container grid grid-cols-2 gap-4 p-4 lg:grid-cols-4">
      <nuxt-link
        v-if="
          permissions.includes('custom-assortments-boxes-access') ||
          permissions.includes('custom-assortments-options-access') ||
          permissions.includes('custom-assortments-products-variations-access')
        "
        to="assortment-settings/variations"
        class="col-o h-full w-full rounded border-gray-200 bg-white px-4 py-8 text-center shadow-md transition-shadow duration-150 hover:shadow-lg dark:border-gray-900 dark:bg-gray-700"
      >
        <font-awesome-icon class="fa-4x text-theme-400" :icon="['fad', 'shapes']" />
        <h2 class="mt-4 font-semibold">{{ $t("Variations") }}</h2>
        <p class="mt-2 text-sm text-gray-400">
          <span v-if="permissions.includes('custom-assortments-boxes-access')">{{
            $t("Boxes")
          }}</span>
          <span
            v-if="
              permissions.includes('custom-assortments-boxes-access') &&
              permissions.includes('custom-assortments-options-access')
            "
          >
            &
          </span>
          <span v-if="permissions.includes('custom-assortments-options-access')">
            {{ $t("Options") }}
          </span>
          {{ $t("settings") }}
        </p>
      </nuxt-link>
    </div>
  </div>
</template>

<script setup>
const { capitalizeFirstLetter } = useUtilities();
const { permissions } = storeToRefs(useAuthStore());
const { t: $t } = useI18n();

useHead({
  title: computed(() => `${$t("assortment settings")} | Prindustry Manager`),
});
</script>
