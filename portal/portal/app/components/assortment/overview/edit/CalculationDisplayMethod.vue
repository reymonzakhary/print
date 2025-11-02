<template>
  <div class="flex flex-col w-full">
    <div v-for="method in price_calculation" :key="method.slug">
      <button
        class="flex items-center justify-between w-full p-2 my-2 transition duration-150 rounded shadow-md hover:shadow-lg"
        :class="{
          'font-bold bg-theme-400 text-themecontrast-400 dark:bg-theme-500': method.active === true,
          'dark:bg-gray-700 bg-white': method.active === false,
        }"
        @click="update(method)"
      >
        <span class="flex-1">
          <figure class="w-20 h-16 p-1 bg-white rounded">
            <img :src="src(method.slug)" class="object-contain" />
          </figure>
        </span>

        <span class="flex-1">
          {{ method.name }}
        </span>

        <font-awesome-icon
          v-tooltip="$t(`{method}_info`, { method: method.name })"
          :icon="['fas', 'circle-info']"
          class="flex-1"
        />
      </button>

      <!-- <transition name="slide">
				<section
					class="p-4 bg-white rounded-b dark:bg-gray-700 text-theme-900 dark:text-white"
					v-if="active == key"
				>
					<div class="flex items-stretch w-full mt-4">
						<button
							@click="method.startingcosts_mode = 'fixed'"
							class="w-1/2 border rounded-l border-theme-500 text-theme-500 hover:bg-theme-100"
							:class="{
								'bg-theme-400 text-themecontrast-400 hover:bg-theme-400 cursor-default':
									method.startingcosts_mode == 'fixed'
							}"
						>
							{{ $t("fixed startingcosts") }}
						</button>
						<button
							@click="method.startingcosts_mode = 'per_run'"
							class="w-1/2 border border-l-0 rounded-r border-theme-500 text-theme-500 hover:bg-theme-100"
							:class="{
								'bg-theme-400 text-themecontrast-400 hover:bg-theme-400 cursor-default':
									method.startingcosts_mode == 'per_run'
							}"
						>
							{{ $t("startingcosts per run") }}
						</button>
					</div>

					<div class="flex items-center justify-between pt-2">
						<label class="label">{{ $t("startingcosts") }}</label>
						<div class="relative flex items-center w-1/2">
							<input
								class="p-1 pl-6 input"
								type="number"
								placeholder="100"
							/>
							<font-awesome-icon
								class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-400"
								:icon="['fal', 'euro-sign']"
							/>
						</div>
					</div>

					
				</section>
			</transition> -->
    </div>
  </div>
</template>

<script>
const images = import.meta.glob("assets/images/*.png", {
  eager: true,
});
export default {
  props: {
    item: {
      required: true,
      type: Array,
    },
  },
  emits: ["update_calculation_method"],
  data() {
    return {
      active: "sliding-scale",
      price_calculation: [],
      priceCalculation: [
        {
          name: "Fixed price",
          slug: "fixed-price",
          active: false,
        },
        {
          name: "Sliding scale",
          slug: "sliding-scale",
          active: false,
        },
        {
          name: "M2 by unit",
          slug: "m2-by-unit",
          active: false,
        },
        {
          name: "M2 by material",
          slug: "m2-by-material",
          active: false,
        },
        {
          name: "M2 by material and unit",
          slug: "m2-by-material-and-unit",
          active: false,
        },
      ],
    };
  },
  created() {
    if (this.item && this.item.length > 0) {
      this.price_calculation = this.item;
    } else {
      this.price_calculation = this.priceCalculation;
    }
  },
  // computed: {
  // 	...mapState({
  // 		item: state => state.assortmentsettings.item
  // 	})
  // },
  methods: {
    update(method) {
      this.price_calculation.forEach((calc_method) => {
        calc_method.active = false;
        if (calc_method.slug === method.slug) {
          calc_method.active = true;
        }
      });

      this.$emit("update_calculation_method", this.price_calculation);
    },
    src(method) {
      let imgUrl = "";

      if (images[`/assets/images/${method}.png`]) {
        imgUrl = images[`/assets/images/${method}.png`].default;
      }

      return imgUrl;
    },
  },
};
</script>

<style></style>
