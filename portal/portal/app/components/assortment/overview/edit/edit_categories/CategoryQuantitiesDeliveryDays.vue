<template>
  <section class="flex flex-wrap items-center w-full">
    <div class="flex items-center w-full mr-2 font-bold tracking-wide uppercase">
      <div class="flex items-center">
        <font-awesome-icon :icon="['fal', 'turtle']" />
        <font-awesome-icon class="mr-2" :icon="['fal', 'rabbit-fast']" />
        {{ $t("delivery days") }}

        <VDropdown>
          <font-awesome-icon class="mx-2 text-theme-500" :icon="['fas', 'circle-info']" />
          <template slot="popper">
            <span
              class="block w-64 p-2 text-sm font-normal text-white normal-case bg-gray-900 rounded shadow-md"
            >
              {{
                //prettier-ignore
                $t("Delivery days carry add on price from assrtoment settings. Delivery day settings on option overwrite those from category.")
              }}
            </span>
          </template>
        </VDropdown>
      </div>
      <button
        class="flex items-center ml-auto text-right text-theme-500"
        @click.prevent="show_add_dlv_days = true"
      >
        <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
        {{ $t("add") }}
      </button>
      <button
        class="flex items-center ml-4 text-right text-theme-500"
        @click="$parent.show_dlv_days = false"
      >
        <font-awesome-icon :icon="['fal', 'xmark']" class="mr-1" />
        {{ $t("close") }}
      </button>
    </div>

    <form
      v-for="(day, i) in printing_method.dlv_days"
      :key="`dlv_day_${day.days}`"
      class="flex items-center w-full my-1"
    >
      <div
        class="flex items-center w-1/6 p-1 mr-2 font-bold text-center bg-gray-300 rounded dark:bg-gray-700"
      >
        {{ day.days }}
        <span class="ml-2 font-normal">{{ $t("days") }}</span>
      </div>

      <div class="flex items-center w-1/2">
        <div class="relative flex w-1/2 mr-2" :key="`dlv_days_${day.days}_1`">
          <label class="flex items-center mr-2 font-bold">
            <font-awesome-icon :icon="['fal', 'bow-arrow']" class="mr-2" />
            {{ $t("range") }}
          </label>
          <input
            type="number"
            name="availability_from"
            v-model="day.availability.from"
            @change="update_selected_category()"
            class="w-full px-2 py-1 text-sm input"
          />
        </div>
        {{ $t("up to") }}
        <div class="relative flex w-1/2 ml-2" :key="`dlv_days_${day.days}_2`">
          <input
            type="number"
            name="availability_to"
            v-model="day.availability.to"
            @change="update_selected_category()"
            class="w-full px-2 py-1 text-sm input"
          />
        </div>
      </div>

      <button
        class="px-2 py-1 ml-4 text-red-500 transition bg-red-100 rounded-full hover:bg-red-200"
        @click="deleteDeliveryDay(i)"
      >
        <font-awesome-icon :icon="['fal', 'trash-can']" />
      </button>
    </form>

    <!-- add new delivery option -->
    <div v-if="show_add_dlv_days" class="flex w-full">
      <select v-model="dlv.days" name="dlv" id="dlv" class="w-1/6 px-2 py-1 mr-2 rounded input">
        <template v-for="day in filtered_delivery_days">
          <option v-if="day.iso === language" :value="day.days" :key="`day_${day.label}`">
            {{ day.days }}
            <span class="text-gray-500">{{ day.label }}</span>
          </option>
        </template>
      </select>

      <div class="flex items-center w-1/2">
        <div class="relative flex w-1/2 mr-2" :key="`add_dlv_days_${dlv.days}_1`">
          <label class="flex items-center mr-2 font-bold">
            <font-awesome-icon :icon="['fal', 'bow-arrow']" class="mr-2" />
            {{ $t("range") }}
          </label>
          <input
            type="number"
            name="dlv_availability_from"
            v-model="dlv.availability.from"
            class="w-full px-2 py-1 text-sm input"
          />
        </div>
        {{ $t("up to") }}
        <div class="relative flex w-1/2 ml-2" :key="`add_dlv_days_${dlv.days}_2`">
          <input
            type="number"
            name="dlv_availability_to"
            v-model="dlv.availability.to"
            class="w-full px-2 py-1 text-sm input"
          />
        </div>
      </div>

      <button
        v-if="dlv.days !== 0"
        @click="updateDeliveryDays()"
        class="px-2 py-1 ml-4 text-sm text-white rounded-full bg-theme-100 text-theme-600 hover:bg-theme-300"
      >
        {{ $t("add") }}
      </button>
      <button class="ml-auto text-right text-theme-500" @click.prevent="show_add_dlv_days = false">
        <font-awesome-icon v-if="show_add_dlv_days" :icon="['fal', 'plus']" class="mr-1" />
        {{ $t("close") }}
      </button>
    </div>
  </section>
</template>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from "vuex";
export default {
  name: "CategoryQuantitiesDeliveryDays",
  props: {
    printing_method: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      show_add_dlv_days: false,
      dlv: {
        availability: {
          from: 0,
          to: 10000,
        },
        days: 0,
      },
    };
  },
  computed: {
    ...mapState({
      printing_methods: (state) => state.printing_methods.printing_methods,
      delivery_days: (state) => state.delivery_days.delivery_days,
      selected_category: (state) => state.product_wizard.selected_category,
    }),
    ...mapGetters({
      langauge: "settings/language",
    }),
    filtered_delivery_days() {
      return this.delivery_days.filter(
        (el) => !this.printing_method.dlv_days.find((dlv) => dlv.days === el.days),
      );
    },
  },
  watch: {
    printing_method: {
      handler(v) {
        return v;
      },
      deep: true,
    },
  },
  methods: {
    // map store mutations & actions to component methods
    ...mapMutations({
      update_delivery_days: "product_wizard/update_delivery_days",
    }),
    ...mapActions({
      update_selected_category: "product_wizard/update_selected_category",
    }),
    updateDeliveryDays() {
      this.printing_method.dlv_days.push(this.dlv);
      this.update_selected_category();
      (this.dlv = {
        availability: {
          from: 0,
          to: 10000,
        },
        days: 0,
      }),
        (this.show_add_dlv_days = false);
    },
    deleteDeliveryDay(i) {
      this.printing_method.dlv_days.splice(i, 1);
      this.update_selected_category();
    },
  },
};
</script>

<style></style>
