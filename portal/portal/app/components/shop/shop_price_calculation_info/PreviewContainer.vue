<template>
  <fieldset
    v-if="calculation && containers.length > 0"
    :ref="`previewContainerRef_${i}`"
    class="relative w-full shadow-md bg-gradient-to-bl from-gray-200 via-gray-100 to-gray-300 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700 shadow-pink-200 dark:shadow-pink-800"
  >
    <div class="absolute text-xs -translate-x-1/2 -top-5 left-1/2">
      <span class="text-gray-400">{{ $t("margin") }}</span>
      {{ calculation.machine.margin_top }} mm
    </div>
    <div class="absolute text-xs -translate-y-1/2 -right-16 top-1/2">
      <span class="text-gray-400">{{ $t("margin") }}</span> <br />
      {{ calculation.machine.margin_right }} mm
    </div>
    <div class="absolute text-xs -translate-x-1/2 -bottom-5 left-1/2">
      <span class="text-gray-400">{{ $t("margin") }}</span>
      {{ calculation.machine.margin_bottom }} mm
    </div>
    <div class="absolute text-xs -translate-y-1/2 -left-16 top-1/2">
      <span class="text-gray-400">{{ $t("margin") }}</span> <br />
      {{ calculation.machine.margin_left }} mm
    </div>
    <div class="absolute text-xs text-black dark:text-theme-100 -top-9">
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
        class="absolute text-xs text-gray-500 bg-white shadow-md paper shadow-gray-400 dark:bg-gray-200 dark:shadow-gray-950 place-items-center place-content-center divide-cyan-500"
        :style="[calculatePaperStyles(calculation, i)]"
      >
        <div class="absolute top-0 z-0 font-mono text-xs text-black left-1">
          <span class="text-gray-400">{{ $t("paper") }}</span>
          {{ calculation.details.catalogue_width }}mm x {{ calculation.details.catalogue_height }}mm
        </div>
        <div class="absolute z-0 text-xs text-black dark:text-theme-100 -top-4 left-1">
          <span class="text-gray-400">{{ $t("bleed") }}</span>
          {{
            (calculation.details.catalogue_width - calculation.details.printable_area_width) / 2
          }}mm
        </div>

        <!-- PRINT -->
        <section class="z-10 grid justify-center max-w-fit" :class="computeGridClass(calculation)">
          <div
            v-for="n in calculation.details.maximum_prints_per_sheet"
            :key="n"
            class="grid grid-cols-1 grid-rows-1 text-xs text-center bg-gradient-to-br from-orange-200 to-pink-200 place-items-center"
            :style="
              calculation.details.rotate_format || calculation.details.ps === 'Landscape'
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
</template>

<script>
export default {
  props: {
    i: {
      type: Number,
      required: true,
    },
    calculation: {
      type: Object,
      required: true,
    },
    // also add full calculations object to calculate the different container dimensions
    calculations: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      // Dynamic
      scaleFactor: 0,
      fixedPixelWidth: 0,
      elementHeight: 0,
      containers: [], // Array to store container-specific measurements
    };
  },
  watch: {
    calculations: {
      deep: true,
      handler(v) {
        for (let i = 0; i < v.length; i++) {
          const calculation = v[i];
          this.updateDimensions(calculation, i);
        }
      },
    },
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
  },
};
</script>
