<template>
  <div class="h-full px-2">
    <article v-if="category[0] !== null" class="">
      <header class="item-center flex flex-wrap justify-between p-4">
        <button class="text-theme-500" @click="deactivateDetails()">
          <font-awesome-icon :icon="['fal', 'chevron-left']" />
          <span class="has-text-weight-normal capitalize">
            {{ $t("back") }}
          </span>
        </button>

        <p class="text-lg">
          <font-awesome-icon v-if="selected_category.ref_id" :icon="['fal', 'parachute-box']" />
          <font-awesome-icon v-else :icon="['fal', 'box-full']" />
          {{ "Category details" }} -
          <b>
            {{
              selected_category.display_name
                ? $display_name(selected_category.display_name)
                : category[2]
            }}
          </b>
          <NuxtLink
            v-if="
              permissions.includes('print-assortments-margins-access') ||
              permissions.includes('print-assortments-boxes-access') ||
              permissions.includes('print-assortments-options-access') ||
              permissions.includes('print-assortments-machines-access') ||
              permissions.includes('print-assortments-printing-methods-access') ||
              permissions.includes('print-assortments-catalogues-access') ||
              permissions.includes('print-assortments-system-catalogues-access')
            "
            v-tooltip="$t(`To assortment settings`)"
            :to="`/manage/assortment-settings`"
            class="ml-2 rounded-full px-2 py-1 text-theme-500 hover:bg-theme-100"
          >
            <font-awesome-icon :icon="['fal', 'gear']" />
          </NuxtLink>
        </p>

        <button
          class="card-header-icon ml-auto hidden sm:ml-0 md:block"
          @click="deactivateDetails()"
        >
          <font-awesome-icon :icon="['fad', 'circle-xmark']" />
        </button>
      </header>

      <CategoryEditNav
        class="top-4 z-20 mx-2 md:sticky md:mx-4"
        :changes-made="changesMade"
        @continue-navigation="changesMade = false"
      />

      <div class="mt-4 rounded-b-md p-2 sm:p-4">
        <transition name="slide">
          <section v-if="active_detail === 'info'">
            <CategoryEditForm :item="selected_category" :producer="me?.supplier" type="category" />
          </section>
        </transition>

        <transition name="slide">
          <section v-if="active_detail === 'config'">
            <CategoryConfig :item="selected_category" @on-update-config="updateConfig($event)" />
          </section>
        </transition>

        <transition name="slide">
          <section v-if="active_detail === 'boops'">
            <div class="relative mx-auto mb-4 flex flex-wrap items-center p-2 md:w-1/2">
              <div class="w-full">
                <h2 class="w-full text-lg font-bold tracking-wide">
                  <span class="icon">
                    <font-awesome-icon :icon="['fal', 'box-full']" fixed-width />
                  </span>
                  {{ $t("Boxes & Options") }}
                </h2>
                <p class="mb-4 text-sm text-gray-500">
                  <span class="italic">{{
                    // prettier-ignore
                    $t("The skeleton of your category, the boxes and options which define the endless variations of your product")
                  }}</span>
                </p>
              </div>

              <transition-group name="slide">
                <template v-if="!manageDivider && !hideSaveButton">
                  <label for="divided" class="text-sm font-bold uppercase tracking-widest">
                    <font-awesome-icon :icon="['fal', 'split']" class="fa-fw mr-2 text-gray-500" />
                    {{ $t("divided") }}
                  </label>
                  <UISwitch :value="divided || false" name="divided" @input="divided = $event" />

                  <VMenu theme="tooltip" class="ml-2">
                    <!-- This will be the popover reference (for the events and position) -->
                    <font-awesome-icon
                      :icon="['fal', 'circle-info']"
                      class="fa-fw rounded-full text-theme-500"
                    />

                    <!-- This will be the content of the popover -->
                    <template #popper>
                      <div class="flex max-w-80 flex-col p-4">
                        <p>
                          {{
                            capitalizeFirstLetter(
                              //prettier-ignore
                              $t(`when enabling this toggle, you will be able to add boxes to a divider group to have them seperately calculated. `),
                            )
                          }}

                          <br />
                          <br />
                          {{
                            capitalizeFirstLetter(
                              //prettier-ignore
                              $t(`for example: a book with a cover and the pages within`),
                            )
                          }}
                        </p>
                      </div>
                    </template>
                  </VMenu>
                </template>

                <div
                  v-if="divided && !hideSaveButton"
                  class="relative mx-auto flex items-center md:w-1/2"
                >
                  <UIButton
                    :class="{ '!bg-green-500 !text-white': manageDivider }"
                    variant="theme"
                    class="shadow"
                    @click="manageDivider = !manageDivider"
                  >
                    {{
                      manageDivider
                        ? capitalizeFirstLetter($t("done managing divider and reorder"))
                        : capitalizeFirstLetter($t("manage divider and reorder"))
                    }}
                  </UIButton>
                </div>
              </transition-group>
            </div>

            <div v-if="manageDivider" class="mx-auto w-full md:w-1/2">
              <ManageBoopsDivider :selected-boops="selected_boops" />
            </div>

            <section class="mx-auto w-full lg:w-2/3 2xl:w-1/2">
              <EditBoops
                v-if="!manageDivider"
                :divided="divided"
                :editable="!selected_category.ref_id"
                @handle-checked-divider="handleCheckedDivider($event)"
                @ordering="hideSaveButton = true"
                @done-ordering="hideSaveButton = false"
                @changes-made="changesMade = true"
              />
            </section>
            <div
              v-if="!hideSaveButton && !manageDivider"
              class="mx-auto mt-4 flex w-full items-center justify-end pr-4 lg:w-2/3 lg:pr-0 2xl:w-1/2"
            >
              <UIButton variant="success" class="px-4 py-1 !text-base" @click="updateBoops">
                <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
                {{ $t("save boxes and options") }}
              </UIButton>
            </div>
          </section>
        </transition>

        <!-- Excludes -->
        <transition name="slide">
          <section v-if="active_detail === 'excludes'">
            <div class="mx-auto w-full">
              <h2 class="w-full text-lg font-bold tracking-wide">
                <span class="icon">
                  <font-awesome-icon :icon="['fal', 'clipboard-list-check']" fixed-width />
                </span>
                {{ $t("Excludes") }}
              </h2>
              <p class="mb-4 text-sm text-gray-500">
                <span class="italic">{{
                  // prettier-ignore
                  $t("Combinations of boxes and options that are not allowed to be ordered together")
                }}</span>
              </p>
            </div>
            <section class="mx-auto w-full">
              <section v-if="!editBoops" class="">
                <!-- Supplier info -->
                <!-- <div
                  class="hidden justify-center items-center pb-4 mr-4 w-full h-auto border-b sm:flex lg:w-auto lg:border-b-0 dark:border-gray-900"
                >
                  <div class="flex flex-col">
                    <figure
                      v-if="supplier"
                      class="flex overflow-hidden flex-shrink-0 justify-center items-center mx-auto w-20 h-20 rounded-full border dark:bg-white"
                    >
                      <img
                        v-if="supplier.name"
                        :src="`/img/suppliers/images/logos/${supplier.name
                          .replace(/\s+/g, '-')
                          .toLowerCase()}.jpg`"
                      />
                      <div v-else>
                        <font-awesome-icon
                          :icon="['fad', 'parachute-box']"
                          class="fa-2xl text-theme-400"
                        />
                      </div>
                    </figure>
                    <p class="font-bold capitalize">{{ category[2] }}</p>
                  </div>

                  <font-awesome-icon
                    :icon="['fal', 'chevron-right']"
                    class="ml-10 text-6xl text-gray-300"
                  />

                  <div class="text-sm text-gray-800 dark:text-gray-500 dark:bg-gray-700">
                    <span class="italic">{{ supplier.name }}</span>
                  
                    <div
                      v-for="country in supplier.operating_countries"
                      :key="country.iso_code"
                      class=""
                    >
                      <span class="">
                        <font-awesome-icon :icon="['fal', 'flag']" />
                      </span>
                  </div>
                </div> -->

                <section class="my-4 flex items-center">
                  <div class="text-sm">
                    <span
                      class="mr-1 rounded border border-b-4 bg-gray-50 px-2 dark:border-gray-950 dark:bg-gray-900"
                    >
                      <font-awesome-icon :icon="['fal', 'up']" fixed-width />
                      Shift
                    </span>
                    +
                    <span
                      class="mr-1 rounded border border-b-4 bg-gray-50 px-2 dark:border-gray-950 dark:bg-gray-900"
                    >
                      <font-awesome-icon
                        :icon="['fal', 'computer-mouse-scrollwheel']"
                        fixed-width
                      />
                      scroll
                      <font-awesome-icon :icon="['fal', 'sort']" fixed-width />
                    </span>
                    <span class="text-gray-500">{{ $t("for horizontal scrolling") }}</span>
                  </div>

                  <div v-if="!hideSaveButton" class="ml-auto flex justify-self-end pr-4">
                    <UIButton variant="success" class="px-4 py-1 !text-base" @click="updateBoops">
                      <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
                      {{ $t("save excludes") }}
                    </UIButton>
                  </div>
                </section>

                <!-- show ALL boxes -->
                <div class="w-full rounded border">
                  <ManageExcludes
                    :divided="divided"
                    @on-managing-excludes="hideSaveButton = true"
                    @on-done-managing-excludes="hideSaveButton = false"
                  />
                </div>
              </section>

              <div
                v-if="!hideSaveButton"
                class="mx-auto mt-4 flex w-full items-center justify-end pr-4"
              >
                <UIButton variant="success" class="px-4 py-1 !text-base" @click="updateBoops">
                  <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
                  {{ $t("save excludes") }}
                </UIButton>
              </div>
            </section>
          </section>
        </transition>

        <!-- price calculation -->
        <transition name="slide">
          <section v-if="active_detail === 'calculation' && !selected_category.ref_id">
            <div
              class="mx-auto"
              :class="{
                'w-full sm:w-full lg:w-2/3 xl:w-full':
                  selected_category.price_build && selected_category.price_build.semi_calculation,
                'xl:w-2/4':
                  selected_category.price_build && selected_category.price_build.full_calculation,
              }"
            >
              <h2 class="w-full text-lg font-bold tracking-wide">
                <span class="icon">
                  <font-awesome-icon :icon="['fal', 'calculator']" fixed-width />
                </span>
                {{ $t("calculation") }}
              </h2>
              <p class="mb-4 text-sm text-gray-500">
                <span class="italic">{{
                  // prettier-ignore
                  $t("lets calculate that price")
                }}</span>
              </p>
            </div>

            <div
              class="mx-auto overflow-x-auto rounded bg-white p-4 shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
              :class="{
                'w-full sm:w-full lg:w-2/3 xl:w-full':
                  selected_category.price_build && selected_category.price_build.semi_calculation,
                'xl:w-2/4':
                  selected_category.price_build && selected_category.price_build.full_calculation,
              }"
            >
              <article
                class="mx-auto grid w-full flex-wrap justify-between gap-4"
                :class="{
                  'grid-cols-1 xl:grid-cols-4':
                    selected_category.price_build && selected_category.price_build.semi_calculation,
                  'grid-cols-1 lg:grid-cols-2':
                    selected_category.price_build && selected_category.price_build.full_calculation,
                }"
              >
                <section
                  class="relative flex w-full flex-col justify-between space-y-8 md:flex-row md:space-x-2 md:space-y-0 lg:mx-auto lg:w-2/3 lg:flex-col lg:space-x-0 lg:space-y-8 xl:w-full"
                >
                  <!-- Calculation type -->
                  <div class="mb-4 w-full">
                    <h3 class="mb-2 flex w-full text-sm font-bold uppercase tracking-wide">
                      <span class="icon">
                        <font-awesome-icon :icon="['fal', 'hourglass']" />
                      </span>
                      {{ $t("startingcosts") }}
                      <VMenu theme="tooltip" class="ml-2">
                        <!-- This will be the popover reference (for the events and position) -->
                        <font-awesome-icon
                          :icon="['fal', 'circle-info']"
                          class="fa-fw rounded-full text-theme-500"
                        />

                        <!-- This will be the content of the popover -->
                        <template #popper>
                          <div class="flex max-w-80 flex-col p-4">
                            <p>
                              {{
                                capitalizeFirstLetter(
                                  //prettier-ignore
                                  $t(`if this categories price calculation needs additional startcosts, you can add them here. `),
                                )
                              }}
                            </p>
                          </div>
                        </template>
                      </VMenu>
                    </h3>

                    <span class="relative">
                      <UICurrencyInput
                        v-model="selected_category.start_cost"
                        input-class="w-full border-green-500 ring-green-200 focus:border-green-500 p-2"
                        :options="{
                          precision: 5,
                        }"
                      />
                    </span>
                  </div>

                  <!-- Calculation type -->
                  <div class="my-4 w-full">
                    <h3 class="w-full text-sm font-bold uppercase tracking-wide">
                      <span class="icon">
                        <font-awesome-icon :icon="['fal', 'calculator-simple']" />
                      </span>
                      {{ $t("calculation type") }}
                    </h3>

                    <CalculationType :price-build="selected_category.price_build" />
                  </div>

                  <transition name="fade">
                    <div
                      v-if="
                        selected_category.price_build &&
                        !selected_category.price_build.full_calculation
                      "
                      class="my-4 w-full"
                    >
                      <!-- Calculation method -->
                      <h4 class="w-full text-xs font-bold uppercase tracking-wide">
                        <font-awesome-icon :icon="['fal', 'chart-area']" />
                        {{ $t("calculation display method") }}
                      </h4>

                      <CalculationDisplayMethod
                        :item="selected_category.calculation_method"
                        @update_calculation_method="updateCalculationMethod"
                      />
                    </div>
                  </transition>

                  <transition name="fade">
                    <div
                      v-if="
                        selected_category.price_build &&
                        selected_category.price_build.full_calculation
                      "
                      class="my-4 w-full"
                    >
                      <!-- Calculation method -->
                      <h3 class="w-full text-sm font-bold uppercase tracking-wide">
                        <font-awesome-icon :icon="['fal', 'print']" />
                        {{ $t("machine") }}
                      </h3>
                      <p class="w-full text-sm text-gray-400">
                        {{ $t("select machine(s) to calculate prices with for this category") }}
                      </p>

                      <div
                        v-for="(catMachine, index) in selected_category.additional"
                        :key="catMachine.machine"
                        class="flex items-center"
                      >
                        <v-select
                          v-model="selected_category.additional[index].machine"
                          :reduce="(machine) => machine.id"
                          :options="machines"
                          label="name"
                          class="input mr-4 w-full rounded !bg-white !py-0 px-1 text-sm !text-theme-900 dark:!shadow-gray-300"
                          :class="`z-[${index}]`"
                        />

                        <button
                          class="ml-2 rounded-full px-2 py-1 text-red-500 hover:bg-red-100"
                          @click="removeMachine(catMachine.machine)"
                        >
                          <font-awesome-icon :icon="['fal', 'trash']" />
                        </button>
                      </div>

                      <button
                        class="ml-auto mt-2 rounded-full px-2 py-1 text-theme-500 hover:bg-theme-100"
                        @click="addMachine($event)"
                      >
                        <font-awesome-icon :icon="['fal', 'plus']" />
                        <font-awesome-icon :icon="['fal', 'print']" class="mr-1" />
                        {{ $t("add machine") }}
                      </button>
                    </div>
                  </transition>
                </section>

                <div
                  class="mt-8 w-full px-2 md:mt-0"
                  :class="{
                    'lg:mx-auto lg:w-2/3 xl:w-full':
                      selected_category.price_build &&
                      selected_category.price_build.semi_calculation,
                  }"
                >
                  <p class="w-full font-bold uppercase tracking-wide">
                    <font-awesome-icon :icon="['fal', 'calendar-circle-exclamation']" />
                    {{ $t("production schedule") }}
                  </p>

                  <section
                    class="my-2 rounded-md border pt-4 dark:border-gray-900 dark:bg-gray-700 dark:shadow-gray-900"
                  >
                    <CategoryProductionSchedule
                      :production-days="selected_category.production_days"
                      @on-update-production-days="updateProductionDays"
                    />
                    <!-- <div class="mt-4 flex items-center justify-between px-4">
                      <button
                        class="flex items-center p-2 text-theme-500"
                        @click="navigateTo('/manage/assortment-settings/semi-calculation')"
                      >
                        <font-awesome-icon
                          :icon="['fal', 'calculator-simple']"
                          class="mr-2 text-theme-500"
                        />
                        {{ $t("set prices on options") }}
                        <font-awesome-icon
                          :icon="['fal', 'arrow-up-right-from-square']"
                          class="ml-1 text-theme-500"
                        />
                      </button>
                    </div> -->
                  </section>
                </div>

                <div
                  v-if="
                    selected_category.price_build && selected_category.price_build.semi_calculation
                  "
                  :class="{
                    'xl:col-span-2':
                      selected_category.price_build &&
                      selected_category.price_build.semi_calculation,
                  }"
                >
                  <p class="w-full font-bold uppercase tracking-wide">
                    <font-awesome-icon :icon="['fal', 'calendar-circle-exclamation']" />
                    {{ $t("extra cost for production days") }}
                  </p>

                  <section
                    class="my-2 rounded-md border pt-4 dark:border-gray-900 dark:bg-gray-700 dark:shadow-gray-900"
                  >
                    <CategoryProductionScheduleCosts
                      :production-dlv="selected_category.production_dlv"
                      @on-update-production-dlv="updateProductionDlv"
                    />
                  </section>
                </div>
              </article>
              <div class="mt-4 flex w-full items-center justify-end">
                <UIButton variant="success" class="px-4 py-1 !text-base" @click="updateCategory()">
                  <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
                  {{ $t("save calculation") }}
                </UIButton>
              </div>
            </div>
          </section>
        </transition>

        <transition name="slide">
          <div
            v-if="
              selected_category.price_build &&
              !selected_category.price_build.collection &&
              active_detail === 'buffering'
            "
            class="mx-auto w-full xl:w-2/3"
          >
            <CategoryRanges
              :category-ranges="selected_category.ranges ?? []"
              :category-range-list="selected_category.range_list ?? []"
              :category-free-entry="selected_category.free_entry ?? []"
              :category-limits="selected_category.limits ?? []"
              :category-range-around="selected_category.range_around"
              @on-update-ranges="updateRanges($event)"
            />
          </div>
        </transition>

        <transition name="slide">
          <!-- margins -->
          <div v-if="active_detail === 'margins'" class="mx-auto w-full xl:w-2/3">
            <div class="mx-auto w-full">
              <h2 class="w-full text-lg font-bold tracking-wide">
                <span class="icon">
                  <font-awesome-icon :icon="['fal', 'hand-holding-dollar']" fixed-width />
                </span>
                {{ $t("margins") }}
              </h2>
              <p class="mb-4 text-sm text-gray-500">
                <span class="italic">{{
                  // prettier-ignore
                  $t("let the money roll in")
                }}</span>
              </p>
            </div>

            <MarginsPage v-if="category[1]" :category="category[1]" />
          </div>
        </transition>
      </div>
    </article>
  </div>
</template>

<script>
import { mapState, mapActions, mapMutations, useStore } from "vuex";
import _ from "lodash";

export default {
  transition: "slideleftlarge",
  setup() {
    const { capitalizeFirstLetter } = useUtilities();
    const api = useAPI();
    const { permissions } = storeToRefs(useAuthStore());
    const store = useStore();
    const eventStore = useEventStore();
    const route = useRoute();
    const { handleError, handleSuccess } = useMessageHandler();
    return {
      api,
      permissions,
      store,
      eventStore,
      route,
      handleError,
      handleSuccess,
      capitalizeFirstLetter,
    };
  },
  data() {
    return {
      hideSaveButton: false,
      loading: false,
      prices: [],
      supplier: {},
      discounts: {},
      editBoops: false,
      manageExcludes: false,
      machines: [],
      qty: 100,
      divided: false,
      manageDivider: false,
      ProductDetailsTourOptions: {
        useKeyboardNavigation: true,
        labels: {
          buttonSkip: "Skip tour",
          buttonPrevious: "Previous",
          buttonNext: "Next",
          buttonStop: "Finish!",
        },
        highlight: true,
      },
      steps: [
        {
          target: '[data-v-step="0"]',
          content:
            "<b>Welcome to your dashboard!</b><br>This is where you will find your acount info and settings",
          params: {
            placement: "right",
          },
          before: () =>
            new Promise((resolve) => {
              // Time-consuming UI/async operation here
              resolve(this.set_active_detail("info"));
            }),
        },
        {
          target: '[data-v-step="1"]',
          content:
            "<b>Boxes and options</b><br>We will display the most important order data right right when you enter the app",
          params: {
            placement: "top",
          },
          before: () =>
            new Promise((resolve) => {
              // Time-consuming UI/async operation here
              resolve(this.set_active_detail("boops"));
            }),
        },
      ],
      selectedPrice: {},
      changesMade: false,
    };
  },
  head() {
    return {
      title: `${this.$t("assortment")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      me: (state) => state.settings.me,

      // vuex product
      category: (state) => state.product.active_category,
      boops: (state) => state.product.boops,
      active_items: (state) => state.product.active_items,
      collection: (state) => state.product.collection,

      // vuex productwizard
      active_detail: (state) => state.product_wizard.active_detail,
      selected_category: (state) => state.product_wizard.selected_category,
      selected_boops: (state) => state.product_wizard.selected_boops,
      price_collection: (state) => state.product_wizard.price_collection,
    }),
  },
  watch: {
    selected_category: {
      handler(newVal) {
        return newVal;
      },
      deep: true,
      immediate: true,
    },
    boops(newVal) {
      return newVal;
    },
    selected_boops(newVal) {
      return newVal;
    },
    qty: _.debounce(function (v) {
      if (this.active_detail === "prices") {
        this.getPrices(v, this.price_collection);
      }
      return v;
    }, 500),
    prices: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    active_detail(v) {
      return v;
    },
    // collection: {
    //   handler(v) {
    //     if (this.active_detail === "prices" && v.length === this.boops.boops.length) {
    //       this.getPrices(this.qty, v);
    //     }
    //   },
    //   deep: true,
    // },
    price_collection: {
      handler(v) {
        if (this.active_detail === "prices" && v.length === this.boops.boops.length) {
          this.getPrices(this.qty, v);
        }
      },
      deep: true,
      immediate: true,
    },
    changesMade(v) {
      return v;
    },
  },
  mounted() {
    const slug = this.route.query.cat;
    this.getCategory(slug);
    this.fetchMachines();
    this.set_active_detail("info");
    this.divided = this.selected_category?.boops?.divided;
  },
  beforeUnmount() {
    this.eventStore.off("add_category_printing_method");
    this.set_active_detail("");
    this.set_selected_category({});
    this.set_active_category([]);
    this.set_boops([]);
  },
  methods: {
    ...mapMutations({
      set_active_category: "product/set_active_category",
      set_boops: "product/set_boops",
      set_selected_category: "product_wizard/set_selected_category",
      set_selected_boops: "product_wizard/set_selected_boops",
      update_calculation_method: "product_wizard/update_calculation_method",
      update_price_build: "product_wizard/update_price_build",
      update_ranges: "product_wizard/update_ranges",
      update_limits: "product_wizard/update_limits",
      update_free_entry: "product_wizard/update_free_entry",
      update_range_around: "product_wizard/update_range_around",
      update_range_list: "product_wizard/update_range_list",
      update_bleed: "product_wizard/update_bleed",
      update_production_days: "product_wizard/update_production_days",
      update_production_dlv: "product_wizard/update_production_dlv",
      set_active_detail: "product_wizard/set_active_detail",
    }),
    ...mapActions({
      update_selected_category: "product_wizard/update_selected_category",
    }),

    updateRanges(e) {
      const ranges = e.ranges;
      const limits = e.limits;
      const free_entry = e.freeEntry;
      this.update_ranges(ranges);
      this.update_limits(limits);
      this.update_free_entry(free_entry);
      this.update_range_around(e.rangeAround);
      this.update_range_list(e.rangeList);
      this.updateCategory();
    },
    updateConfig(e) {
      this.update_bleed(e);
      this.updateCategory();
    },
    updateProductionDays(e) {
      this.update_production_days(e);
      // this.updateCategory();
    },
    updateProductionDlv(e) {
      this.update_production_dlv(e);
      // this.updateCategory();
    },
    addMachine() {
      if (this.selected_category.additional.length === 0) {
        Object.assign({ additional: [] }, this.selected_category);
      }
      this.selected_category.additional.push({
        machine: "",
      });
    },
    removeMachine(e) {
      const index = this.selected_category.additional.findIndex((machine) => machine === e);
      this.selected_category.additional.splice(index, 1);
    },
    updateCalculationMethod(e) {
      this.update_calculation_method(e);

      setTimeout(() => {
        this.update_selected_category();
      }, 300);
    },

    async updateCategory() {
      this.api
        .put(`categories/${this.selected_category.slug}`, this.selected_category)
        .then((response) => {
          this.handleSuccess(response);
        })
        .catch((error) => {
          this.handleError(error);
        });
    },

    async getCategory(slug) {
      this.api
        .get(`categories/${slug}`)
        .then((response) => {
          const category = response.data;
          this.set_selected_category(category);
          this.set_selected_boops(category.boops[0]?.boops ?? []);
          this.set_active_category([category.supplier_category, category.slug, category.name]);
          if (category.ref_id) {
            this.getSupplier(category.ref_id);
          } else {
            this.supplier = null;
          }
          this.$forceUpdate();
          this.getMargins();
          this.divided = category.boops[0]?.divided ?? false;
        })
        .catch((error) => this.handleError(error));
    },

    // Get Supplier by ID
    async getSupplier(id) {
      await this.api
        .get(`/suppliers/${id}`)
        .then((response) => (this.supplier = response))
        .catch((error) => this.handleError(error));
    },

    // Obtain and show calculated prices of selected category & product for authenticated Client (reseller)
    async getPrices(qty, collection) {
      if (Object.keys(collection).length > 0) {
        await this.api
          .post(`/categories/${this.category[1]}/products/calculate/prices`, {
            product: [...new Set(collection)],
            quantity: qty,
          })
          .then((response) => {
            this.prices = response.data;
          })
          .catch((error) => {
            if (error.status === 404) {
              this.prices = [];
            }
            this.handleError(error);
          });
      }
    },

    // Get margin - Category Object of Authenticated Reseller (Seller)
    async getMargins() {
      // TODO: move this function to vuex store to have it globally accessible
      let supplier_url = "";
      if (this.store.state.product.category_ref_id != null) {
        supplier_url = `?supplier_id=${this.store.state.product.category_ref_id}`;
      }
      await this.api
        .get(`/categories/${this.category[1]}/margins${supplier_url}`)
        .then(
          (
            margins, // add result to store
          ) => this.store.commit("margins/store", margins.data),
        )
        .catch((error) => this.handleError(error));
    },

    // save updated margins
    async saveMargins(object) {
      object.forEach((element) => {
        element.slots.forEach((innerItem) => {
          if (innerItem.to == "") {
            innerItem.to = "-1";
          }
        });
        return object;
      });
      let supplier_id;
      if (this.store.state.product.category_ref_id != null) {
        supplier_id = this.store.state.product.category_ref_id;
      }
      await this.api
        .put(`/categories/${this.category[1]}/margins`, {
          margin: object,
          supplier_id: supplier_id,
        })
        .then((response) => this.handleSuccess(response))
        .catch((error) => this.handleError(error));
    },

    handleCheckedDivider(e) {},

    // save updated boops
    async updateBoops() {
      let haveSameBoxes = false;
      if (!this.divided) {
        this.selected_boops.forEach((boop) => {
          const matches = this.selected_boops.filter((item) => item.id === boop.id);
          if (matches.length > 1) {
            haveSameBoxes = true;
            return;
          }
        });
        if (haveSameBoxes) {
          this.handleError(this.$t("You can't have the same box twice"));
          return;
        }
      }
      this.loading = true;
      this.$store.commit("settings/setPreventNavigation", false); // Reset the flag if user confirms so navigation can occur (navigationGuard Middleware)
      this.changesMade = false;

      await this.api
        .put(`categories/${this.category[1]}/boops`, {
          id: this.category[0],
          name: this.category[2],
          slug: this.category[1],
          boops: this.selected_boops,
          divided: this.divided,
        })
        .then((response) => {
          this.handleSuccess(response);

          if (this.selected_category.price_build.collection === false) {
            // TODO: this was on for some reason regarding the imports but it makes export function export empty excel
            this.api
              .post(`categories/${this.selected_category.slug}/products/combinations/regenerate`, {
                id: this.selected_category.id,
                name: this.selected_category.name,
                slug: this.selected_category.slug,
                boops: this.selected_boops,
              })
              .then((response) => {
                this.handleSuccess(response);
                this.loading = false;
              })
              .catch((error) => {
                this.handleError(error);
                this.loading = false;
              });
          }
        })
        .catch((error) => this.handleError(error))
        .finally(() => (this.loading = false));
    },

    async fetchMachines() {
      await this.api
        .get("/machines")
        .then((response) => (this.machines = response.data))
        .catch((error) => this.handleError(error));
    },

    // close dertails and navigate to products overview
    deactivateDetails() {
      this.set_active_detail("");
      this.$router.push("/assortment");
    },
  },
};
</script>

<style lang="scss" scoped>
.ghost {
  @apply rounded outline-dashed outline-1 outline-theme-500;
}
</style>
