<template>
  <sidePanel
    transition="slide"
    :full-height="true"
    width="w-11/12 lg:w-2/3 xl:w-1/2"
    class=""
    @on-close="close()"
  >
    <template #side-panel-header>
      <h2
        class="ixed fixed top-0 z-20 mr-2 flex w-full items-center justify-between rounded-tl bg-white/80 px-4 py-2 uppercase tracking-wide text-theme-900 shadow backdrop-blur-md dark:bg-gray-700/80 dark:text-theme-200"
      >
        <div class="flex w-full items-center">
          <font-awesome-icon class="mr-2" :icon="['fal', 'calculator']" />
          {{ $t("How is this price calculated?") }}
        </div>
      </h2>
    </template>

    <template #side-panel-content>
      <div v-if="containers.length > 0" class="z-10 bg-gray-100 p-4 pt-14 text-sm dark:bg-gray-900">
        <template v-for="(calculation, i) in calculations" :key="'sorted_price_' + i">
          <div class="flex">
            <div class="w-full rounded bg-white p-4 shadow-md dark:bg-gray-700">
              <h1
                class="w-full pb-4 text-center text-lg font-bold uppercase text-gray-600 dark:text-gray-400"
              >
                {{ calculation.name }}
              </h1>
              <div class="flex flex-wrap items-stretch divide-x pb-4">
                <!-- MACHINE -->
                <div class="w-1/3 p-2 pr-4">
                  <MachineData :calculation="calculation"></MachineData>
                </div>

                <!-- PAPER -->
                <div class="w-1/3 bg-gray-50 p-2 px-4 dark:bg-gray-800">
                  <PaperData :calculation="calculation"></PaperData>
                </div>

                <!-- PRINT -->
                <div class="w-1/3 p-2 pl-4">
                  <PrintData :calculation="calculation"></PrintData>
                </div>
              </div>

              <section class="w-full bg-gray-50 p-4 font-mono shadow-inner dark:bg-gray-800">
                <div class="mx-auto my-8 h-full w-full md:w-1/2">
                  <!-- CONTAINER -->
                  <fieldset
                    :ref="`previewContainerRef_${i}`"
                    class="relative w-full bg-gradient-to-bl from-gray-200 via-gray-100 to-gray-300 shadow-md shadow-pink-200 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 dark:shadow-pink-800"
                  >
                    <div class="absolute -top-5 left-1/2 -translate-x-1/2 text-xs">
                      <span class="text-gray-400">{{ $t("margin") }}</span>
                      {{ calculation.machine.margin_top }} mm
                    </div>
                    <div class="absolute -right-16 top-1/2 -translate-y-1/2 text-xs">
                      <span class="text-gray-400">{{ $t("margin") }}</span> <br />
                      {{ calculation.machine.margin_right }} mm
                    </div>
                    <div class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-xs">
                      <span class="text-gray-400">{{ $t("margin") }}</span>
                      {{ calculation.machine.margin_bottom }} mm
                    </div>
                    <div class="absolute -left-16 top-1/2 -translate-y-1/2 text-xs">
                      <span class="text-gray-400">{{ $t("margin") }}</span> <br />
                      {{ calculation.machine.margin_left }} mm
                    </div>
                    <div class="absolute -top-9 text-xs text-black dark:text-theme-100">
                      <span class="text-gray-400"> {{ calculation.machine.name }}</span> <br />
                      {{ calculation.machine.width.toFixed(0) }}{{ calculation.machine.unit }} x
                      {{ calculation.machine.height.toFixed(0) }}{{ calculation.machine.unit }}
                    </div>

                    <!-- MACHINE -->
                    <div
                      :style="calculateMachineStyles(calculation, i)"
                      class="relative flex items-center justify-center"
                    >
                      <!-- PAPER -->
                      <div
                        class="paper absolute place-content-center place-items-center divide-cyan-500 bg-white text-xs text-gray-500 shadow-md shadow-gray-400 dark:bg-gray-200 dark:shadow-gray-950"
                        :style="[calculatePaperStyles(calculation, i)]"
                      >
                        <div class="absolute left-1 top-0 z-0 font-mono text-xs text-black">
                          <span class="text-gray-400">{{ $t("paper") }}</span>
                          {{ calculation.details.catalogue_width }}mm x
                          {{ calculation.details.catalogue_height }}mm
                        </div>
                        <div
                          class="absolute -top-4 left-1 z-0 text-xs text-black dark:text-theme-100"
                        >
                          <span class="text-gray-400">{{ $t("bleed") }}</span>
                          {{
                            (calculation.details.catalogue_width -
                              calculation.details.printable_area_width) /
                            2
                          }}mm
                        </div>

                        <!-- PRINT -->
                        <section
                          class="z-10 grid max-w-fit justify-center"
                          :class="computeGridClass(calculation)"
                        >
                          <div
                            v-for="n in calculation.details.maximum_prints_per_sheet"
                            :key="n"
                            class="grid grid-cols-1 grid-rows-1 place-items-center bg-gradient-to-br from-orange-200 to-pink-200 text-center text-xs"
                            :style="
                              calculation.details.rotate_format ||
                              calculation.details.ps === 'Landscape'
                                ? [
                                    `height: ${calculation.details.width_with_bleed * containers[i].scaleFactor}px`,
                                    `width: ${calculation.details.height_with_bleed * containers[i].scaleFactor}px`,
                                    calculatePrintStyle(calculation, i, n),
                                  ]
                                : [
                                    `width: ${calculation.details.width_with_bleed * containers[i].scaleFactor}px`,
                                    `height: ${calculation.details.height_with_bleed * containers[i].scaleFactor}px`,
                                    calculatePrintStyle(calculation, i, n),
                                  ]
                            "
                          >
                            <span class="text-gray-400">{{ $t("print") }}</span>
                            <br />
                            w
                            {{ calculation.details.format_width.toFixed(2) }}mm <br />
                            x <br />
                            h
                            {{ calculation.details.format_height.toFixed(2) }}mm
                          </div>
                        </section>
                      </div>
                    </div>
                  </fieldset>
                </div>
              </section>
            </div>
          </div>

          <div class="my-4 flex w-full">
            <UIButton
              class="!ml-auto px-4 !text-base shadow-md"
              @click="priceDetail = !priceDetail"
            >
              {{ $t("more detailed information") }}
              <font-awesome-icon
                class="ml-2"
                :icon="['fal', !priceDetail ? 'arrow-right' : 'xmark']"
              />
            </UIButton>
          </div>

          <!-- Extensive Price info -->
          <transition name="slide">
            <article v-if="priceDetail" class="w-full bg-gray-100 p-4 text-xs dark:bg-gray-900">
              <ExtensiveCalculationInfo :calculation="calculation" />
            </article>
          </transition>
        </template>
      </div>
    </template>
  </sidePanel>
</template>

<script>
export default {
  name: "PriceCalc",
  props: {
    calculations: {
      type: Array,
      requiered: true,
      default: () => [],
    },
    priceInfo: {
      type: Boolean,
      requiered: true,
      default: false,
    },
  },
  emits: ["on-close"],
  data() {
    return {
      priceDetail: false,
      // Dynamic
      scaleFactor: 0,
      fixedPixelWidth: 0,
      elementHeight: 0,
      containers: [], // Array to store container-specific measurements
    };
  },
  watch: {
    // calculations: {
    //   deep: true,
    //   handler(v) {
    //     for (let i = 0; i < v.length; i++) {
    //       const calculation = v[i];
    //       this.updateDimensions(calculation, i);
    //     }
    //   },
    // },
  },
  mounted() {
    this.containers = this.calculations.map(() => ({
      fixedPixelWidth: 0,
      elementHeight: 0,
      scaleFactor: 0,
    }));

    this.$nextTick(() => {
      this.calculations.forEach((calculation, i) => {
        this.updateDimensions(calculation, i);
      });
    });
  },
  methods: {
    /**
     * convert all units to mm for easier calculations
     * @param value
     * @param unit
     */
    convertToMm(value, unit) {
      switch (unit) {
        case "mm":
          return value;
        case "cm":
          return value * 10;
        case "m":
          return value * 1000;
        default:
          console.error("Unknown unit:", unit);
          return value;
      }
    },

    /**
     * Update dimensions to recalculate all width, height, bleed, margin and trimarea parameters
     * @param calculation this is data
     * @param i this is the index
     */
    updateDimensions(calculation, i) {
      // make sure the component is mounted
      this.$nextTick(() => {
        // retrieve the container dimensions
        const reffer = `previewContainerRef_${i}`;
        //check for existance
        if (this.$refs[reffer] && this.$refs[reffer][0]) {
          // set correct values
          const containerWidth = this.$refs[reffer][0].clientWidth;
          const widthInMm = this.convertToMm(calculation.machine.width, calculation.machine.unit);
          const scaleFactor = containerWidth / widthInMm;
          const heightInMm = this.convertToMm(calculation.machine.height, calculation.machine.unit);
          const calculatedHeight = heightInMm * scaleFactor;

          // store values to reference later
          this.containers[i] = {
            fixedPixelWidth: containerWidth,
            elementHeight: calculatedHeight,
            scaleFactor: scaleFactor,
          };
        }
      });
    },

    /**
     * Calculate styles to present the trim areas
     * @param calculation this is the data
     * @param i this is the index
     * @param boxNumber this is the current print to have its properties calculated
     */
    calculatePrintStyle(calculation, i, boxNumber) {
      // set variables
      let trimAreaInMm;
      let borderWidth;

      // convert values in mm to scaled px
      if (this.containers[i]) {
        trimAreaInMm = calculation.machine.trim_area;
        borderWidth = trimAreaInMm * this.containers[i].scaleFactor;
      }

      // Base style for all boxes
      const style = {
        border: `${borderWidth}px dashed #22d3ee`,
      };

      // calculate the columns and rows needed
      const totalPrints = calculation.details.maximum_prints_per_sheet;
      const cols = this.getColumnCount(calculation, "cols");
      // const rows = Math.ceil(totalPrints / cols);
      const rows = this.getColumnCount(calculation, "rows");

      // Calculate box position
      const rowIndex = Math.ceil(boxNumber / cols); // Current row (1-based)
      const colIndex = ((boxNumber - 1) % cols) + 1; // Current column (1-based)

      // set the excludes for the trim areas when needed
      if (calculation.machine.trim_area_exclude_y) {
        // Top row
        if (rowIndex === 1) {
          style.borderTop = "0px";
        }
        // Bottom row
        if (rowIndex === rows) {
          style.borderBottom = "0px";
        }
      }

      if (calculation.machine.trim_area_exclude_x) {
        // Leftmost column
        if (colIndex === 1) {
          style.borderLeft = "0px";
        }
        // Rightmost column
        if (colIndex === cols) {
          style.borderRight = "0px";
        }
      }

      // return the magic .・゜゜・
      return style;
    },

    /**
     * Get grid classes based on amount of prints fitting on the paper
     * @param calculation this is the data
     */
    computeGridClass(calculation) {
      const cols = this.getColumnCount(calculation, "cols");
      const totalPrints = calculation.details.maximum_prints_per_sheet;
      // const rows = Math.ceil(totalPrints / cols);
      const rows = this.getColumnCount(calculation, "rows");

      return `grid-rows-${rows} grid-cols-${cols}`;
    },

    /**
     * calculate the amount of prints fitting on the paper
     * @param calculation this is the data
     */
    getColumnCount(calculation, dimension) {
      // set the variables
      // these can be reassigned
      let { format_width: printWidth, format_height: printHeight } = calculation.details;

      // these are constant
      const {
        ps: orientation,
        rotate_format: rotated,
        maximum_prints_per_sheet: maxPrints,
        catalogue_width: paperWidth,
        catalogue_height: paperHeight,
      } = calculation.details;

      // rotate when needed
      if (rotated) {
        printWidth = calculation.details.format_height;
        printHeight = calculation.details.format_width;
      }
      // rotate when needed
      if (orientation === "Landscape") {
        printWidth = calculation.details.format_height;
        printHeight = calculation.details.format_width;
      }

      // Calculate how many prints can fit horizontally
      const maxHorizontalPrints = Math.floor(paperWidth / printWidth);

      // Calculate how many prints can fit vertically
      const maxVerticalPrints = Math.floor(paperHeight / printHeight);

      // Return the number of columns that allows for maximum prints
      // while respecting paper dimensions .・゜゜・
      if (dimension === "rows") {
        return Math.min(maxVerticalPrints, maxPrints);
      } else {
        return Math.min(maxHorizontalPrints, maxPrints);
      }
    },

    /**
     * Calculate the styles to present width, heght and margins of the machine
     * @param calculation this is the data
     * @param i this is the calculation index (multiple calculations might apply)
     */
    calculateMachineStyles(calculation, i) {
      // calculate the scale factor
      const container = this.containers[i] || {
        fixedPixelWidth: 0,
        elementHeight: 0,
        scaleFactor: 0,
      };

      // set the margins in mm
      const topMarginInMm = calculation.machine.margin_top;
      const bottomMarginInMm = calculation.machine.margin_bottom;
      const leftMarginInMm = calculation.machine.margin_left;
      const rightMarginInMm = calculation.machine.margin_right;

      // convert bleeds to px with the scalefactor to maintain proportions
      const topMargin = topMarginInMm * container.scaleFactor;
      const bottomMargin = bottomMarginInMm * container.scaleFactor;
      const leftMargin = leftMarginInMm * container.scaleFactor;
      const rightMargin = rightMarginInMm * container.scaleFactor;

      // return the magic .・゜゜・
      return {
        borderTop: `${topMargin}px solid #f472b6`,
        borderBottom: `${bottomMargin}px solid #f472b6`,
        borderLeft: `${leftMargin}px solid #f472b6`,
        borderRight: `${rightMargin}px solid #f472b6`,
        overflow: "hidden",
        width: `${container.fixedPixelWidth}px`,
        height: `${container.elementHeight}px`,
      };
    },

    /**
     * Calculate and convert the width, height and bleed of the paper
     * @param calculation
     * @param i
     */
    calculatePaperStyles(calculation, i) {
      // calculate the scale factor
      const container = this.containers[i] || {
        fixedPixelWidth: 0,
        elementHeight: 0,
        scaleFactor: 0,
      };

      // declare width and height variables to return later
      let paperHeight;

      // Get the paper width in mm and scale them
      const paperWidthInMm = this.convertToMm(calculation.details.catalogue_width, "mm");

      // Use the container's scaleFactor to maintain proportions and make it always fit the container
      const paperWidth = paperWidthInMm * container.scaleFactor;

      // when it is a sheet, calculate width AND height
      if (calculation.details.fed === "sheet") {
        // Get the paper height in mm and scale them
        const paperHeightInMm = this.convertToMm(calculation.details.catalogue_height, "mm");

        // Use the container's scaleFactor to maintain proportions and make it always fit the container
        paperHeight = paperHeightInMm * container.scaleFactor;
      }

      // calculate bleed based on bruto and netto catalogue widths
      const bleed = {
        width: (calculation.details.catalogue_width - calculation.details.printable_area_width) / 2, // Divide by 2 to get per side
        height:
          (calculation.details.catalogue_height - calculation.details.printable_area_height) / 2, // Divide by 2 to get per side
      };

      // set the bleeds in mm
      const topBleedInMm = bleed.height;
      const bottomBleedInMm = bleed.height;
      const leftBleedInMm = bleed.width;
      const rightBleedInMm = bleed.width;

      // convert bleeds to px with the scalefactor to maintain proportions and make it always fit the container
      const topBleed = topBleedInMm * container.scaleFactor;
      const bottomBleed = bottomBleedInMm * container.scaleFactor;
      const leftBleed = leftBleedInMm * container.scaleFactor;
      const rightBleed = rightBleedInMm * container.scaleFactor;

      // return the magic .・゜゜・
      return {
        borderTop: `${topBleed}px solid #4ade80`,
        borderBottom: `${bottomBleed}px solid #4ade80`,
        borderLeft: `${leftBleed}px solid #4ade80`,
        borderRight: `${rightBleed}px solid #4ade80`,
        // overflow: "hidden",
        width: `${paperWidth}px`,
        height: paperHeight ? `${paperHeight}px` : "100%",
      };
    },
    close() {
      this.$emit("on-close");
    },
  },
};
</script>
