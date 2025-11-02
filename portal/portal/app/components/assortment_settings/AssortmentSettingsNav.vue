<template>
  <div class="sticky top-0 max-h-96 w-full overflow-y-auto sm:w-2/6 lg:w-1/6">
    <div class="flex items-center justify-between p-4 pb-0">
      <h2 class="p-2 text-sm font-bold uppercase tracking-wide">
        {{ $t("nav") }}
      </h2>
    </div>
    <section class="px-4">
      <h3 class="p-2 text-xs font-bold uppercase tracking-wide">
        {{ $t("Print products") }}
      </h3>
      <button
        v-if="
          permissions.includes('print-assortments-machines-access') ||
          permissions.includes('print-assortments-catalogues-access') ||
          permissions.includes('print-assortments-system-catalogues-access')
        "
        class="group my-1 flex w-full cursor-pointer items-center justify-between rounded px-2 py-1 transition-colors duration-100 hover:text-theme-500 focus:outline-none"
        :class="{
          'bg-theme-100 text-theme-500 dark:bg-theme-900': active_nav === 'full',
        }"
        @click="
          ((active_nav = 'full'), $router.push('/manage/assortment-settings/full-calculation'))
        "
      >
        <p>
          <font-awesome-icon :icon="[active_nav === 'full' ? 'fad' : 'fal', 'print']" fixed-width />

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
        </p>
      </button>

      <button
        v-if="
          permissions.includes('print-assortments-boxes-access') ||
          permissions.includes('print-assortments-options-access')
        "
        class="group my-1 flex w-full cursor-pointer items-center justify-between rounded px-2 py-1 transition-colors duration-100 hover:text-theme-500 focus:outline-none"
        :class="{
          'bg-theme-100 text-theme-500 dark:bg-theme-900': active_nav === 'semi',
        }"
        @click="
          ((active_nav = 'semi'), $router.push('/manage/assortment-settings/semi-calculation'))
        "
      >
        <p>
          <font-awesome-icon
            :icon="[active_nav === 'semi' ? 'fad' : 'fal', 'shapes']"
            fixed-width
          />
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
        </p>
      </button>

      <button
        v-if="permissions.includes('print-assortments-printing-methods-access')"
        class="group my-1 flex w-full cursor-pointer items-center justify-between rounded px-2 py-1 transition-colors duration-100 hover:text-theme-500 focus:outline-none"
        :class="{
          'bg-theme-100 text-theme-500 dark:bg-theme-900': active_nav === 'pm',
        }"
        @click="((active_nav = 'pm'), $router.push('/manage/assortment-settings/printing-methods'))"
      >
        <p>
          <font-awesome-icon
            :icon="[active_nav === 'pm' ? 'fad' : 'fal', 'fill-drip']"
            fixed-width
          />
          {{ capitalizeFirstLetter($t("Printing methods")) }}
        </p>
      </button>

      <button
        v-if="permissions.includes('print-assortments-margins-access')"
        class="group my-1 flex w-full cursor-pointer items-center justify-between rounded px-2 py-1 transition-colors duration-100 hover:text-theme-500 focus:outline-none"
        :class="{
          'bg-theme-100 text-theme-500 dark:bg-theme-900': active_nav === 'margins',
        }"
        @click="((active_nav = 'margins'), $router.push('/manage/assortment-settings/margins'))"
      >
        <p>
          <font-awesome-icon
            :icon="[active_nav === 'margins' ? 'fad' : 'fal', 'hand-holding-dollar']"
            fixed-width
          />
          {{ capitalizeFirstLetter($t("margins")) }}
        </p>
      </button>

      <button
        v-if="permissions.includes('print-assortments-discount-access')"
        class="group my-1 flex w-full cursor-pointer items-center justify-between rounded px-2 py-1 transition-colors duration-100 hover:text-theme-500 focus:outline-none"
        :class="{
          'bg-theme-100 text-theme-500 dark:bg-theme-900': active_nav === 'discounts',
        }"
        @click="((active_nav = 'discounts'), $router.push('/manage/assortment-settings/discounts'))"
      >
        <p>
          <font-awesome-icon
            :icon="[active_nav === 'discounts' ? 'fad' : 'fal', 'badge-percent']"
            fixed-width
          />
          {{ capitalizeFirstLetter($t("discounts")) }}
        </p>
      </button>

      <h3 class="p-2 text-xs font-bold uppercase tracking-wide">
        {{ $t("Custom products") }}
      </h3>

      <button
        class="group my-1 flex w-full cursor-pointer items-center justify-between rounded px-2 py-1 transition-colors duration-100 hover:text-theme-500 focus:outline-none"
        :class="{
          'bg-theme-100 text-theme-500 dark:bg-theme-900': active_nav === 'variations',
        }"
        @click="
          ((active_nav = 'variations'), $router.push('/manage/assortment-settings/variations'))
        "
      >
        <p>
          <font-awesome-icon
            :icon="[active_nav === 'variations' ? 'fad' : 'fal', 'shapes']"
            fixed-width
          />
          {{ $t("variations") }}
        </p>
      </button>
    </section>
  </div>
</template>

<script>
export default {
  setup() {
    const { capitalizeFirstLetter } = useUtilities();
    const { permissions } = storeToRefs(useAuthStore());
    return { permissions, capitalizeFirstLetter };
  },
  data() {
    return {
      active_nav: "full",
    };
  },
  watch: {
    active_nav(v) {
      return v;
    },
  },
  mounted() {
    switch (this.$route.path.substring(this.$route.path.lastIndexOf("/") + 1)) {
      case "full-calculation":
        this.active_nav = "full";
        break;

      case "semi-calculation":
        this.active_nav = "semi";
        break;

      case "printing-methods":
        this.active_nav = "pm";
        break;

      case "margins":
        this.active_nav = "margins";
        break;

      case "variations":
        this.active_nav = "variations";
        break;

      default:
        break;
    }
  },
};
</script>

<style></style>
