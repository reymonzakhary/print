<template>
  <div>
    <SidePanel width="w-full lg:w-3/4" :full-height="true" @on-close="$emit('onEditOff')">
      <template #side-panel-content>
        <div ref="machine" class="relative rounded bg-white pb-[20%] dark:bg-gray-700">
          <div
            :class="{ 'rounded border-2 border-theme-400': internalMachine.default }"
            class="relative z-20 flex flex-col rounded-t bg-white shadow-md shadow-gray-300 dark:bg-gray-700 dark:shadow-gray-950"
          >
            <div
              class="sticky top-0 z-10 grid w-full grid-cols-6 space-x-2 bg-white p-4 transition"
            >
              <div class="flex">
                <font-awesome-icon
                  :icon="[internalMachine.default ? 'fad' : 'fal', 'print']"
                  class="fa-3x mr-4 text-theme-500"
                  fixed-with
                />
                <div>
                  <UIInputText
                    v-model="internalMachine.name"
                    name="machineName"
                    class="w-full"
                    :placeholder="$t('machine name')"
                  />

                  <UITextArea v-model="internalMachine.description" />
                </div>
              </div>

              <fieldset class="flex-1">
                <label class="text-xs font-semibold uppercase text-gray-400">
                  <font-awesome-icon class="mr-1" :icon="['fal', 'print']" />
                  {{ $t("type") }}
                </label>
                <UISelector
                  v-model="internalMachine.type"
                  :options="[
                    { value: 'printing', label: $t('printing') },
                    { value: 'lamination', label: $t('lamination') },
                    // { value: 'bundling', label: $t('bundling') },
                    // { value: 'punch_holes', label: $t('punch holes') },
                    // { value: 'cutting ', label: $t('cutting') },
                    // { value: 'covering', label: $t('covering') },
                  ]"
                />
              </fieldset>

              <fieldset class="flex-1">
                <template v-if="internalMachine.type === 'printing'">
                  <label class="text-xs font-semibold uppercase text-gray-400">
                    <font-awesome-icon :icon="['fal', 'toilet-paper-blank']" />
                    /
                    <font-awesome-icon class="mr-1" :icon="['fal', 'note-sticky']" />
                    {{ $t("paper options") }}
                  </label>
                  <!-- :value="(machine.fed === ['sheet'])?{ value: ['sheet'], label: $t('sheet') }:(machine.fed === ['roll'])?{ value: ['roll'], label: $t('roll') }: { value: ['sheet', 'roll'], label: $t('sheet & roll') }" -->
                  <UISelector
                    v-model="internalMachine.fed"
                    display-property="label"
                    :options="[
                      { value: 'sheet', label: $t('sheet') },
                      { value: 'roll', label: $t('roll') },
                    ]"
                    @input="setFED($event)"
                  />
                </template>
              </fieldset>

              <fieldset class="flex-1">
                <template v-if="internalMachine.type === 'printing'">
                  <label class="text-xs font-semibold uppercase text-gray-400">
                    {{ $t("printing method") }}
                  </label>
                  <v-select
                    v-if="internalMachine.type === 'printing'"
                    v-model="internalMachine.pm"
                    :reduce="(pm) => pm.name"
                    :options="printingMethods"
                    label="name"
                    class="input z-50 mr-4 w-auto rounded bg-white p-1 py-0 text-sm text-theme-900"
                  />
                </template>
              </fieldset>

              <fieldset class="flex-1 p-1">
                <label class="text-xs font-semibold uppercase text-gray-400">{{ $t("EAN") }}</label>
                <UIInputText
                  v-model="internalMachine.ean"
                  name="machineEan"
                  :placeholder="$t('1234567890')"
                />
              </fieldset>

              <div class="ml-auto mt-2 flex w-full items-start justify-end space-x-2">
                <UIButton
                  :icon="['fas', 'check']"
                  variant="success"
                  :disabled="false"
                  default="Button"
                  name="saveMachine"
                  @click="($emit('onSave', internalMachine), $emit('onEditOff'))"
                />
                <UIButton
                  :icon="['fas', 'xmark']"
                  variant="neutral"
                  :disabled="false"
                  name="doneEditMachine"
                  default="Button"
                  @click="$emit('onEditOff')"
                />
                <UIButton
                  :icon="['fas', 'trash']"
                  variant="danger"
                  :disabled="false"
                  name="deleteMachine"
                  default="Button"
                  @click="$emit('onDelete', { internalMachine })"
                />
              </div>
            </div>

            <div class="z-0 flex w-full flex-wrap border-t p-4 dark:border-black">
              <h3 class="mt-4 w-full text-sm font-bold uppercase tracking-wide text-gray-400">
                {{ $t("sizes and weights") }}
              </h3>
              <section class="flex w-full items-stretch justify-between">
                <article class="relative mr-8 pr-4 md:w-2/3">
                  <!-- line -->
                  <div
                    class="absolute right-0 h-full w-6 border-2 border-l-0 border-theme-400 text-theme-400"
                  />
                  <!-- arrow -->
                  <div class="absolute -right-4 top-1/2 h-4 w-4 text-theme-400">
                    <font-awesome-icon class="text-xl" :icon="['fas', 'right']" />
                  </div>

                  <div class="grid grid-cols-3 gap-4 border-b pb-4 dark:border-black">
                    <fieldset class="flex-1 p-1">
                      <label class="text-xs font-semibold uppercase text-gray-400">
                        <font-awesome-icon
                          class="mr-1"
                          :icon="['fal', 'arrows-left-right-to-line']"
                        />
                        {{ $t("sheet width") }}</label
                      >
                      <UIInputText
                        v-model="internalMachine.width"
                        type="number"
                        :prefix="internalMachine.unit"
                        name="machineWidth"
                        :placeholder="$t('width')"
                      />
                    </fieldset>

                    <fieldset class="flex-1 p-1">
                      <label class="text-xs font-semibold uppercase text-gray-400">
                        <font-awesome-icon class="mr-1" :icon="['fal', 'arrows-to-line']" />
                        {{ $t("sheet height") }}</label
                      >
                      <UIInputText
                        v-model="internalMachine.height"
                        type="number"
                        :prefix="internalMachine.unit"
                        name="machineHeight"
                        :placeholder="$t('height')"
                      />
                    </fieldset>

                    <fieldset class="flex-1 p-1">
                      <label class="text-xs font-semibold uppercase text-gray-400">{{
                        $t("units")
                      }}</label>
                      <UISelector
                        v-model="internalMachine.unit"
                        disabled
                        :options="[
                          { value: 'mm', label: 'milimeter' },
                          // disabled this because calculation can only handle mm for now 20-11-2024
                          // { value: 'cm', label: 'centimeter' },
                          // { value: 'm', label: 'meter' },
                        ]"
                      />
                    </fieldset>

                    <fieldset class="flex-1 p-1">
                      <label class="text-xs font-semibold uppercase text-gray-400">
                        <font-awesome-icon class="mr-1" :icon="['fal', 'scale-unbalanced']" />
                        {{ $t("min. paperweight") }}</label
                      >
                      <UIInputText
                        v-model="internalMachine.min_gsm"
                        type="number"
                        :affix="$t('grs')"
                        name="machineMinGrs"
                        placeholder="60"
                      />
                    </fieldset>

                    <fieldset class="flex-1 p-1">
                      <label class="text-xs font-semibold uppercase text-gray-400"
                        ><font-awesome-icon class="mr-1" :icon="['fal', 'scale-unbalanced-flip']" />
                        {{ $t("max. paperweight") }}</label
                      >
                      <UIInputText
                        v-model="internalMachine.max_gsm"
                        type="number"
                        :affix="$t('grs')"
                        name="machineNaxGrs"
                        placeholder="300"
                      />
                    </fieldset>
                  </div>

                  <div class="grid grid-cols-3 gap-4 py-4">
                    <fieldset class="col-start-2 flex-1 p-1">
                      <label
                        class="flex items-center text-xs font-semibold uppercase text-gray-400"
                      >
                        <font-awesome-icon class="mr-1" :icon="['fal', 'scalpel-line-dashed']" />
                        {{ $t("trim area between prints") }}
                        <div class="ml-2 h-3 w-3 rounded-full bg-cyan-500" />
                      </label>
                      <UIInputText
                        v-model="internalMachine.trim_area"
                        type="number"
                        :affix="$t('mm')"
                        name="machineTrim_area"
                        placeholder="6"
                      />
                    </fieldset>
                    <fieldset class="flex-1 p-1">
                      <label class="text-xs font-semibold uppercase text-gray-400">
                        <font-awesome-icon class="mr-1" :icon="['fal', 'scalpel-line-dashed']" />
                        {{ $t("trim area excludes") }}
                      </label>
                      <div class="flex items-center space-x-2 text-sm">
                        {{ $t("exclude y") }}
                        <UISwitch
                          class="ml-2"
                          variant="success"
                          name="trim_area_exclude_y"
                          :value="internalMachine.trim_area_exclude_y"
                          @input="setTrimAreaExcludeY($event)"
                        />
                      </div>
                      <div class="flex items-center space-x-2 text-sm">
                        {{ $t("exclude x") }}
                        <UISwitch
                          class="ml-2"
                          variant="success"
                          name="trim_area_exclude_x"
                          :value="internalMachine.trim_area_exclude_x"
                          @input="setTrimAreaExcludeX($event)"
                        />
                      </div>
                    </fieldset>

                    <fieldset class="col-start-2 flex-1 space-y-2 p-1">
                      <label
                        class="-tems-center flex text-xs font-semibold uppercase text-gray-400"
                      >
                        <font-awesome-icon class="mr-1" :icon="['fal', 'arrows-up-down']" />
                        {{ $t("margin y") }}
                        <div class="ml-2 h-3 w-3 rounded-full bg-pink-500" />
                      </label>

                      <UIInputText
                        v-model="internalMachine.margin_top"
                        type="number"
                        :prefix="$t('top')"
                        :affix="$t('mm')"
                        name="machine_margin_top"
                        placeholder="2"
                      />

                      <UIInputText
                        v-model="internalMachine.margin_bottom"
                        type="number"
                        :prefix="$t('bottom')"
                        :affix="$t('mm')"
                        name="machine_margin_bottom"
                        placeholder="2"
                      />
                    </fieldset>

                    <fieldset class="flex-1 space-y-2">
                      <label class="text-xs font-semibold uppercase text-gray-400">
                        <font-awesome-icon class="mr-1" :icon="['fal', 'arrows-left-right']" />
                        {{ $t("margin x") }}
                      </label>

                      <UIInputText
                        v-model="internalMachine.margin_left"
                        type="number"
                        :prefix="$t('left')"
                        :affix="$t('mm')"
                        name="machine_margin_left"
                        placeholder="2"
                      />

                      <UIInputText
                        v-model="internalMachine.margin_right"
                        type="number"
                        :prefix="$t('right')"
                        :affix="$t('mm')"
                        name="machine_margin_right"
                        placeholder="2"
                      />
                    </fieldset>
                  </div>
                </article>

                <article class="md:w-1/3">
                  <h3 class="-mt-4 mb-4 text-sm font-bold uppercase tracking-wide text-gray-400">
                    {{ $t("preview") }}
                  </h3>
                  <div
                    v-show="showNoPreviewAvailable"
                    class="grid h-full w-full items-center rounded-lg border-4 border-dashed border-gray-200 bg-gray-100 p-12 text-center text-gray-400"
                  >
                    <div>
                      <font-awesome-icon
                        :icon="['fad', 'image-slash']"
                        class="text-8xl text-gray-300"
                      />
                      <span class="mt-3 block text-xl">{{ $t("No preview available") }}</span>
                      <span class="text-md block">
                        {{ $t("This preview only works for certain sheet ratio's.") }}
                      </span>
                    </div>
                  </div>
                  <fieldset
                    v-show="!showNoPreviewAvailable"
                    ref="previewContainerRef"
                    class="min-w-0 overflow-hidden"
                    :class="{
                      'no-trim-x': internalMachine.trim_area_exclude_x,
                      'no-trim-y': internalMachine.trim_area_exclude_y,
                    }"
                  >
                    <div
                      class="grid h-full grid-cols-2 border border-gray-900 shadow"
                      :style="calculateContainerStyles"
                    >
                      <div
                        v-for="i in 6"
                        :key="i"
                        class="preview-container__box-item grid place-items-center border border-gray-900 bg-gray-100 text-center text-xs text-gray-400"
                        :style="calculateBoxStyles"
                      >
                        w {{ (internalMachine.width / 3).toFixed(2) }}mm x h
                        {{ (internalMachine.height / 3).toFixed(2) }}mm
                      </div>
                    </div>
                  </fieldset>
                </article>
              </section>
              <h3 class="mt-4 w-full text-sm font-bold uppercase tracking-wide text-gray-400">
                {{ $t("runtime speed and spoilage") }}
              </h3>
              <div class="grid w-full grid-cols-6 space-x-4">
                <fieldset class="flex-1 p-1">
                  <label class="text-xs font-semibold uppercase text-gray-400">
                    <font-awesome-icon class="mr-1" :icon="['fal', 'hourglass-start']" />
                    {{ $t("setup time") }}
                  </label>
                  <UIInputText
                    v-model="internalMachine.setup_time"
                    type="number"
                    affix="min."
                    name="machineSetup"
                    :placeholder="$t('0 minutes')"
                    step=".01"
                  />
                </fieldset>

                <fieldset
                  v-if="internalMachine.fed === 'roll' && internalMachine.type === 'printing'"
                  class="flex-1 p-1"
                >
                  <label class="text-xs font-semibold uppercase text-gray-400">
                    <font-awesome-icon class="mr-1" :icon="['fal', 'toilet-paper-blank']" />
                    {{ $t("meter per minute") }}
                  </label>
                  <UIInputText
                    v-model="internalMachine.mpm"
                    type="number"
                    :affix="$t('meter')"
                    name="machineMPM"
                    :placeholder="$t('0 meter')"
                    step=".01"
                  />
                </fieldset>

                <fieldset
                  v-if="internalMachine.fed === 'sheet' && internalMachine.type === 'printing'"
                  class="flex-1 p-1"
                >
                  <label class="text-xs font-semibold uppercase text-gray-400">
                    <font-awesome-icon class="mr-1" :icon="['fal', 'note-sticky']" />
                    {{ $t("sheet per minute") }}
                  </label>
                  <UIInputText
                    v-model="internalMachine.spm"
                    type="number"
                    :affix="$t('sheet')"
                    name="machineSPM"
                    :placeholder="$t('0 sheets')"
                    step=".01"
                  />
                </fieldset>

                <fieldset class="col-span-2 flex-1 p-1">
                  <label class="text-xs font-semibold uppercase text-gray-400">
                    <font-awesome-icon class="mr-1" :icon="['fal', 'hourglass-clock']" />

                    {{ $t("coolingtime") }}
                  </label>
                  <div class="flex">
                    <UIInputText
                      v-model="internalMachine.cooling_time"
                      type="number"
                      affix="min."
                      name="machineCoolingTime"
                      :placeholder="$t('0 minutes')"
                      step=".01"
                    />
                    <span class="mx-2">{{ $t("per") }}</span>
                    <UIInputText
                      v-model="internalMachine.cooling_time_per"
                      type="number"
                      affix="min."
                      name="machineCoolingTimePer"
                      :placeholder="$t('0 minutes')"
                      step=".01"
                    />
                  </div>
                </fieldset>

                <fieldset class="flex-1 p-1">
                  <label class="text-xs font-semibold uppercase text-gray-400">
                    <font-awesome-icon class="mr-1" :icon="['fal', 'trash']" />
                    {{ $t("waste") }}
                  </label>
                  <div class="flex">
                    <UIInputText
                      v-model="internalMachine.wf"
                      type="number"
                      name="machineWF"
                      affix="%"
                      placeholder="0"
                    />
                  </div>
                </fieldset>
              </div>

              <div class="mt-4 grid w-full grid-cols-6 bg-gray-50 dark:bg-gray-800">
                <fieldset class="flex-1 rounded-l py-2 pl-2">
                  <label class="text-xs font-semibold uppercase text-gray-400">
                    {{ $t("startcost") }}
                  </label>
                  <div class="pr-2">
                    <UICurrencyInput
                      v-model="internalMachine.price"
                      input-class="mr-2  w-full border-green-500 ring-green-200 focus:border-green-500 dark:border-green-500 text-sm"
                      :options="{
                        precision: 5,
                      }"
                    />
                  </div>
                </fieldset>

                <fieldset class="flex-1 p-2">
                  <label class="text-xs font-semibold uppercase text-gray-400">
                    {{ $t("Spoilage") }}
                  </label>
                  <UIInputText
                    v-model="internalMachine.spoilage"
                    type="number"
                    name="machineSpoilage"
                  />
                </fieldset>

                <fieldset class="col-span-2 flex-1 rounded-r py-2 pr-2">
                  <label class="text-xs font-semibold uppercase text-gray-400">
                    {{ $t("divide startcosts between orders") }}
                  </label>
                  <div class="flex items-center">
                    <div class="mr-1">
                      {{ $t("Divide by amount up on position") }}
                    </div>
                    <UISwitch
                      :key="`divide_by_up_on_position`"
                      variant="success"
                      :name="`divide_by_up_on_position`"
                      :value="internalMachine.divide_start_cost"
                      @input="setDivideStartcost($event)"
                    />
                  </div>
                </fieldset>
              </div>
            </div>
          </div>

          <div
            class="relative z-10 w-full space-y-4 rounded-b bg-gray-200 p-4 shadow-md dark:bg-gray-900"
          >
            <div class="flex w-full items-center">
              <div class="w-full">
                <div class="text-xs font-semibold uppercase text-gray-400">
                  {{ $t("Available colors") }}
                </div>
              </div>
              <div class="grid w-1/2 grid-cols-2">
                <v-select
                  v-model="newOption"
                  :options="options"
                  label="name"
                  placeholder="Choose Option"
                  class="input z-50 mr-4 w-full !rounded-none rounded-l bg-white p-1 py-0 text-sm text-theme-900"
                />
                <UIButton
                  :icon="['fas', 'fill-drip']"
                  variant="default"
                  :disabled="false"
                  name="addOptionMachine"
                  class="rounded-rigth shrink-0 !rounded-none"
                  @click="addOption()"
                >
                  {{ $t("Add option") }}
                </UIButton>
              </div>
            </div>

            <MachineColors
              v-for="(option, i) in internalMachine.options"
              :key="`machine_option_${i}`"
              :item="constructOption(option, i)"
              :type="internalMachine.type"
              :editable="true"
              :index="i"
              :machine="internalMachine.id"
              @on-remove-color="removeOption($event)"
              @on-update="handleColorsUpdate(i, $event)"
            />
          </div>
        </div>
        <div
          class="sticky bottom-0 top-8 z-20 flex border-t bg-white/60 p-4 shadow-md backdrop-blur-md dark:border-gray-900 dark:bg-gray-700/80 dark:text-theme-200"
        >
          <UIButton
            :icon="['fas', 'check']"
            variant="success"
            :disabled="false"
            default="Button"
            name="saveMachineBottom"
            class="mx-auto px-4"
            @click="$emit('onSave', internalMachine)"
          >
            <div class="text-base">{{ $t("save") }}</div>
          </UIButton>
        </div>
      </template>
    </SidePanel>
  </div>
</template>

<script>
export default {
  props: {
    machine: {
      type: Object,
      required: true,
    },

    printingMethods: {
      type: Array,
      required: true,
    },
    options: {
      type: Array,
      required: true,
    },
  },
  emits: ["onEdit", "onEditOff", "onSave", "onDelete"],
  setup() {
    const { addToast } = useToastStore();
    return { addToast };
  },
  data() {
    return {
      showDetails: false,
      newOption: this.options[0] ?? null,
      internalMachine: { ...this.machine },
      // Dynamic
      scaleFactor: 0,
      fixedPixelWidth: 0,
      elementHeight: 0,
      showNoPreviewAvailable: false,
    };
  },
  computed: {
    calculateBoxStyles() {
      const trimAreaInMm = this.internalMachine.trim_area;
      const borderWidth = trimAreaInMm * this.scaleFactor;

      return {
        borderTop: `${borderWidth}px solid #06b6d4`,
        borderRight: `${borderWidth}px solid #06b6d4`,
        borderBottom: `${borderWidth}px solid #06b6d4`,
        borderLeft: `${borderWidth}px solid #06b6d4`,
      };
    },
    calculateContainerStyles() {
      const topMarginInMm = this.internalMachine.margin_top;
      const bottomMarginInMm = this.internalMachine.margin_bottom;
      const leftMarginInMm = this.internalMachine.margin_left;
      const rightMarginInMm = this.internalMachine.margin_right;

      const topMargin = topMarginInMm * this.scaleFactor;
      const bottomMargin = bottomMarginInMm * this.scaleFactor;
      const leftMargin = leftMarginInMm * this.scaleFactor;
      const rightMargin = rightMarginInMm * this.scaleFactor;

      return {
        width: `${this.fixedPixelWidth}px`,
        height: `${this.elementHeight}px`,
        borderTop: `${topMargin}px solid #ec4899`,
        borderBottom: `${bottomMargin}px solid #ec4899`,
        borderLeft: `${leftMargin}px solid #ec4899`,
        borderRight: `${rightMargin}px solid #ec4899`,
        overflow: "hidden",
      };
    },
  },
  watch: {
    machine: {
      deep: true,
      immediate: true,
      handler(v) {
        return v;
      },
    },
    showDetails: {
      immediate: true,
      handler(v) {
        if (v) {
          window.addEventListener("resize", this.updateDimensions);
          this.updateDimensions();
        } else {
          window.removeEventListener("resize", this.updateDimensions);
          this.fixedPixelWidth = 0;
          this.elementHeight = 0;
        }
      },
    },

    internalMachine: {
      deep: true,
      handler() {
        this.updateDimensions();
      },
    },
  },
  mounted() {
    if (this.internalMachine) {
      this.internalMachine.unit = "mm";
    }
    window.addEventListener("keydown", this.handleKeydown);
  },
  beforeUnmount() {
    window.removeEventListener("keydown", this.handleKeydown);
    window.removeEventListener("scroll", this.gandalf, true);
    window.removeEventListener("resize", this.updateDimensions);
  },
  methods: {
    gandalf(event) {
      const machineRect = this.$refs.machine.getBoundingClientRect();
      const backdropRect = this.$refs.backdrop.getBoundingClientRect();
      if (machineRect.bottom < backdropRect.bottom) {
        const scrollPosition = event.target.scrollTop + machineRect.bottom - backdropRect.bottom;
        event.target.scrollTop = scrollPosition;
      }
    },
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
    updateDimensions() {
      this.$nextTick(() => {
        if (this.$refs.previewContainerRef) {
          this.fixedPixelWidth = this.$refs.previewContainerRef.clientWidth;
          const widthInMm = this.convertToMm(this.internalMachine.width, this.internalMachine.unit);
          this.scaleFactor = this.fixedPixelWidth / widthInMm;
          const heightInMm = this.convertToMm(
            this.internalMachine.height,
            this.internalMachine.unit,
          );
          const calculatedHeight = heightInMm * this.scaleFactor;
          if (calculatedHeight > 700) {
            this.showNoPreviewAvailable = true;
          } else if (this.showNoPreviewAvailable) {
            this.updateDimensions();
            this.showNoPreviewAvailable = false;
          }
          this.elementHeight = calculatedHeight;
        } else {
          this.fixedPixelWidth = 0;
          this.elementHeight = 0;
        }
      });
    },
    constructOption(option, i) {
      if (this.internalMachine.colors.length === 0) {
        return {
          ...option,
          spoilage: 0,
        };
      }
      return {
        ...option,
        // onderstaande gaf error
        ...this.internalMachine?.options[i]?.speed,
        spoilage: this.internalMachine?.options[i]?.spoilage,
      };
    },
    handleColorsUpdate(index, event) {
      this.internalMachine.options[index] = event;
      const speed = {
        mpm: event.mpm,
        spm: event.spm,
      };
      this.internalMachine.colors[index] = {
        ...this.internalMachine.colors[index],
        speed,
        spoilage: event.spoilage,
      };
    },
    handleKeydown(event) {
      if (event.key === "Escape") {
        this.$emit("onEditOff");
      }
    },
    setPM(value) {
      this.internalMachine.pm = value.name;
    },
    setFED(value) {
      this.internalMachine.fed = value.value;
    },
    setTrimAreaExcludeY(value) {
      this.internalMachine.trim_area_exclude_y = value;
    },
    setTrimAreaExcludeX(value) {
      this.internalMachine.trim_area_exclude_x = value;
    },
    setDivideStartcost(e) {
      this.internalMachine.divide_start_cost = e;
    },
    addOption() {
      // check if newoption exists
      if (Object.keys(this.newOption).length > 0) {
        // if the options key is missing...
        if (!this.internalMachine.options) {
          Object.assign(this.internalMachine, { options: [] }); // assign key to object
        }

        // when machine is a printer
        if (this.internalMachine.type === "printing") {
          // if the colors key is missing...
          if (!this.internalMachine.colors) {
            Object.assign(this.internalMachine, { colors: [] }); // assign key to object
          }

          // add color object
          this.internalMachine.colors.push({
            mode_id: this.newOption.id,
            mode_name: this.newOption.slug,
            display_name: this.newOption,
            speed: {
              mpm: 0, // roll - meter per minute
              spm: 0, // sheet - sheet per minute
            },
          });
        }

        // add option to machine with runs (in reality, the machine is added to the option in the mongo db...)
        this.internalMachine.options.push({
          ...this.newOption,
          id: this.newOption.id,
          sheet_runs: [
            {
              machine: this.internalMachine.id,
              dlv_production: [
                // only used for colors on printing machine
                {
                  days: 0,
                  value: 0,
                  mode: "percentage",
                },
                {
                  days: 1,
                  value: 0,
                  mode: "percentage",
                },
                {
                  days: 2,
                  value: 0,
                  mode: "percentage",
                },
              ],
              runs: [
                // sets the machine tickprices
                {
                  from: 1,
                  to: 100,
                  price: 10,
                },
              ],
            },
          ],
        });
      } else {
        this.addToast({
          message: this.$t("select an option first"),
          type: "warning",
        });
      }
    },
    removeOption(e) {
      // remove option from machine
      this.internalMachine.options.splice(e.i, 1);

      // remove colors from printer
      if (this.internalMachine.type === "printing") {
        const cIndex = this.machine.colors.findIndex((c) => c.id === e.color.id);
        this.internalMachine.colors.splice(cIndex, 1);
      }
    },
  },
};
</script>

<style lang="scss">
.no-trim-x .preview-container__box-item:nth-child(even) {
  border-right: none !important;
}

.no-trim-x .preview-container__box-item:nth-child(odd) {
  border-left: none !important;
}

.no-trim-y .preview-container__box-item:nth-child(-n + 2) {
  border-top: none !important;
}

.no-trim-y .preview-container__box-item:nth-last-child(-n + 2) {
  border-bottom: none !important;
}
</style>
