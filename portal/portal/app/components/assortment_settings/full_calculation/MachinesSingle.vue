<template>
  <div>
    <div ref="machine" class="relative rounded bg-white dark:bg-gray-700">
      <div
        :class="{ 'rounded border-2 border-theme-400': internalMachine.default }"
        class="relative z-20 flex flex-col rounded-t bg-white shadow-md shadow-gray-300 dark:bg-gray-700 dark:shadow-gray-950"
      >
        <div class="grid w-full grid-cols-6 space-x-2 p-4 transition">
          <div class="flex">
            <font-awesome-icon
              :icon="[internalMachine.default ? 'fad' : 'fal', 'print']"
              class="fa-3x mr-4 text-theme-500"
              fixed-with
            />
            <div>
              <div class="font-bold">
                {{ internalMachine.name }}
              </div>

              <div class="text-sm">
                {{ internalMachine.description }}
              </div>
            </div>
          </div>

          <fieldset class="flex-1">
            <label class="text-xs font-semibold uppercase text-gray-400">
              <font-awesome-icon class="mr-1" :icon="['fal', 'print']" />
              {{ $t("type") }}
            </label>

            <div class="text-sm lowercase">
              {{ $t(internalMachine.type) }}
            </div>
          </fieldset>

          <fieldset class="flex-1">
            <template v-if="internalMachine.type === 'printing'">
              <label class="text-xs font-semibold uppercase text-gray-400">
                <font-awesome-icon :icon="['fal', 'toilet-paper-blank']" />
                /
                <font-awesome-icon class="mr-1" :icon="['fal', 'note-sticky']" />
                {{ $t("paper options") }}
              </label>

              <div class="text-sm">
                {{ internalMachine.fed }}
              </div>
            </template>
          </fieldset>

          <fieldset class="flex-1">
            <template v-if="internalMachine.type === 'printing'">
              <label class="text-xs font-semibold uppercase text-gray-400">{{
                $t("printing method")
              }}</label>
              <div>{{ internalMachine.pm }}</div>
            </template>
          </fieldset>

          <fieldset class="flex-1 p-1">
            <label class="text-xs font-semibold uppercase text-gray-400">{{ $t("EAN") }}</label>

            <div>
              {{ internalMachine.ean }}
            </div>
          </fieldset>

          <!-- <div class="flex-grow w-full">
						<label class="text-xs font-semibold text-gray-400 uppercase">
							{{ $t("Default machine") }}
						</label>
						<UISwitch
							:disabled="!editable"
							:key="`defaultMachine_${index}`"
							v-model="machine.default"
							variant="success"
							:name="`defaultMachine_${index}`"
						/>
					</div> -->
          <!-- </div> -->

          <div class="ml-auto mt-2 flex w-full items-start justify-end space-x-2">
            <!-- {{ machine }} -->

            <!-- <pre>{{ machine }}</pre> -->
            <!-- <div class="w-full"> -->
            <UIButton
              :icon="['fas', 'pencil']"
              variant="default"
              :disabled="false"
              name="editMachine"
              default="Button"
              @click="emit('onEdit', internalMachine)"
            />

            <UIButton
              :icon="['fas', 'trash']"
              variant="danger"
              :disabled="false"
              name="deleteMachine"
              default="Button"
              @click="emit('onDelete', { internalMachine, index })"
            />
            <!-- <div class="flex-1 py-1"> -->
            <UIButton
              v-if="!editable && internalMachine.options.length > 0"
              :icon="['fas', showDetails ? 'chevron-up' : 'chevron-down']"
              variant="default"
              :disabled="false"
              name="toggleDetailsMachine"
              class="shrink-0"
              @click="showDetails ? (showDetails = false) : (showDetails = true)"
            >
              {{ showDetails ? $t("Hide details") : $t("Show details") }}
            </UIButton>
            <!-- </div> -->
            <!-- </div> -->
          </div>
        </div>

        <transition name="slide">
          <div v-if="showDetails" class="flex w-full flex-wrap border-t p-4 dark:border-black">
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

                    <div>
                      {{ internalMachine.width }} <small>{{ internalMachine.unit }}</small>
                    </div>
                  </fieldset>

                  <fieldset class="flex-1 p-1">
                    <label class="text-xs font-semibold uppercase text-gray-400">
                      <font-awesome-icon class="mr-1" :icon="['fal', 'arrows-to-line']" />
                      {{ $t("sheet height") }}</label
                    >

                    <div>
                      {{ internalMachine.height }}
                      <small>{{ internalMachine.unit }}</small>
                    </div>
                  </fieldset>

                  <fieldset class="flex-1 p-1">
                    <label class="text-xs font-semibold uppercase text-gray-400">
                      <font-awesome-icon class="mr-1" :icon="['fal', 'scale-unbalanced']" />
                      {{ $t("min. paperweight") }}</label
                    >

                    <div>
                      {{ internalMachine.min_gsm }}
                      <small>{{ $t("grs") }} </small>
                    </div>
                  </fieldset>

                  <fieldset class="flex-1 p-1">
                    <label class="text-xs font-semibold uppercase text-gray-400"
                      ><font-awesome-icon class="mr-1" :icon="['fal', 'scale-unbalanced-flip']" />
                      {{ $t("max. paperweight") }}</label
                    >

                    <div>
                      {{ internalMachine.max_gsm }}
                      <small>{{ $t("grs") }} </small>
                    </div>
                  </fieldset>

                  <fieldset class="flex-1 p-1">-</fieldset>
                </div>

                <div class="grid grid-cols-3 gap-4 py-4">
                  <fieldset class="col-start-2 flex-1 p-1">
                    <label class="flex items-center text-xs font-semibold uppercase text-gray-400">
                      <font-awesome-icon class="mr-1" :icon="['fal', 'scalpel-line-dashed']" />
                      {{ $t("trim area between prints") }}
                      <div class="ml-2 h-3 w-3 rounded-full bg-cyan-500" />
                    </label>

                    <div>
                      {{ internalMachine.trim_area }}
                      <small>{{ $t("mm") }} </small>
                    </div>
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
                        disabled
                        variant="success"
                        name="trim_area_exclude_y"
                        :value="internalMachine.trim_area_exclude_y"
                      />
                    </div>
                    <div class="flex items-center space-x-2 text-sm">
                      {{ $t("exclude x") }}
                      <UISwitch
                        class="ml-2"
                        disabled
                        variant="success"
                        name="trim_area_exclude_x"
                        :value="machine.trim_area_exclude_x"
                      />
                    </div>
                  </fieldset>

                  <fieldset class="col-start-2 flex-1 space-y-2 p-1">
                    <label class="-tems-center flex text-xs font-semibold uppercase text-gray-400">
                      <font-awesome-icon class="mr-1" :icon="['fal', 'arrows-up-down']" />
                      {{ $t("margin y") }}
                      <div class="ml-2 h-3 w-3 rounded-full bg-pink-500" />
                    </label>

                    <div>
                      {{ internalMachine.margin_top }} <small class="text-grey-500">x</small>
                      {{ internalMachine.margin_bottom }}
                      <small>{{ $t("mm") }} </small>
                    </div>
                  </fieldset>

                  <fieldset class="flex-1 space-y-2">
                    <label class="text-xs font-semibold uppercase text-gray-400">
                      <font-awesome-icon class="mr-1" :icon="['fal', 'arrows-left-right']" />
                      {{ $t("margin x") }}
                    </label>

                    <div>
                      {{ internalMachine.margin_left }} <small class="text-grey-500">x</small>
                      {{ internalMachine.margin_right }}
                      <small>{{ $t("mm") }} </small>
                    </div>
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

                <div>
                  {{ internalMachine.setup_time }}
                  <small>{{ $t("min.") }}</small>
                </div>
              </fieldset>

              <fieldset
                v-if="internalMachine.fed === 'roll' && internalMachine.type === 'printing'"
                class="flex-1 p-1"
              >
                <label class="text-xs font-semibold uppercase text-gray-400">
                  <font-awesome-icon class="mr-1" :icon="['fal', 'toilet-paper-blank']" />
                  {{ $t("meter per minute") }}
                </label>

                <div>
                  {{ internalMachine.mpm }}
                  <small>{{ $t("m") }}</small>
                </div>
              </fieldset>

              <fieldset
                v-if="internalMachine.fed === 'sheet' && internalMachine.type === 'printing'"
                class="flex-1 p-1"
              >
                <label class="text-xs font-semibold uppercase text-gray-400">
                  <font-awesome-icon class="mr-1" :icon="['fal', 'note-sticky']" />
                  {{ $t("sheet per minute") }}
                </label>

                <div>
                  {{ internalMachine.spm }}
                  <small>{{ $t("sheet") }}</small>
                </div>
              </fieldset>

              <fieldset class="col-span-2 flex-1 p-1">
                <label class="text-xs font-semibold uppercase text-gray-400">
                  <font-awesome-icon class="mr-1" :icon="['fal', 'hourglass-clock']" />

                  {{ $t("coolingtime") }}
                </label>

                <div>
                  {{ internalMachine.cooling_time }}
                  <small>{{ $t("min.") }}</small> {{ $t("per") }}
                  {{ internalMachine.cooling_time_per }}
                  <small>{{ $t("min.") }}</small>
                </div>
              </fieldset>

              <fieldset class="flex-1 p-1">
                <label class="text-xs font-semibold uppercase text-gray-400">
                  <font-awesome-icon class="mr-1" :icon="['fal', 'trash']" />
                  {{ $t("waste") }}
                </label>

                <div>
                  {{ internalMachine.wf }}
                  <small>{{ $t("%") }}</small>
                </div>
              </fieldset>
            </div>

            <div class="mt-4 grid w-full grid-cols-6 bg-gray-50 dark:bg-gray-800">
              <fieldset class="flex-1 rounded-l py-2 pl-2">
                <label class="text-xs font-semibold uppercase text-gray-400">
                  {{ $t("startcost") }}
                </label>

                <div>
                  {{ internalMachine.display_price }}
                </div>
              </fieldset>

              <fieldset class="flex-1 p-2">
                <label class="text-xs font-semibold uppercase text-gray-400">
                  {{ $t("Spoilage") }}
                </label>

                <div>
                  {{ internalMachine.spoilage }}
                  <small>{{ $t("sheet") }}</small>
                </div>
              </fieldset>

              <fieldset class="col-span-2 flex-1 rounded-r py-2 pr-2">
                <label class="text-xs font-semibold uppercase text-gray-400">
                  {{ $t("divide startcosts between orders") }}
                </label>
                <div class="flex items-center">
                  <UISwitch
                    :key="`divide_by_up_on_position_${index}`"
                    disabled
                    variant="success"
                    :name="`divide_by_up_on_position${index}`"
                    :value="internalMachine.divide_start_cost"
                  />
                </div>
              </fieldset>
            </div>
          </div>
        </transition>
      </div>

      <transition name="slide">
        <div
          v-show="showDetails || editable"
          class="relative z-10 w-full space-y-4 rounded-b bg-gray-200 p-4 shadow-md dark:bg-gray-900"
        >
          <div class="flex w-full items-center">
            <div class="w-full">
              <div class="text-xs font-semibold uppercase text-gray-400">
                {{ $t("Available colors") }}
              </div>
            </div>
          </div>

          <MachineColors
            v-for="(option, i) in internalMachine.options"
            :key="`machine_option_${i}`"
            :item="constructOption(option, i)"
            :type="internalMachine.type"
            :editable="false"
            :index="i"
            :machine="internalMachine.id"
          />
        </div>
      </transition>
      <div
        v-if="!editable && !showDetails"
        class="relative z-10 w-full space-y-4 !rounded-b bg-gray-200 p-4 shadow-md dark:bg-gray-900"
      >
        <div class="text-xs font-semibold uppercase text-gray-400">
          {{ $t("Available options") }}
        </div>
        <section class="items center flex space-x-4">
          <template v-if="internalMachine.options && internalMachine.options.length > 0">
            <div
              v-for="color in internalMachine.options"
              :key="`machine_color_${color.name}`"
              class="rounded bg-gray-50 p-1 text-sm"
            >
              {{ $display_name(color.display_name) }}
            </div>
          </template>
          <div v-else class="text-sm italic text-amber-500">
            <font-awesome-icon :icon="['fad', 'triangle-exclamation']" class="mr-1" fixed-with />
            {{ $t("Please add some options") }}
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from "vue";

const props = defineProps({
  machine: {
    type: Object,
    required: true,
  },
  index: {
    type: Number,
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
  editedMachine: {
    type: Number,
    default: null,
  },
});

const emit = defineEmits(["onEdit", "onEditOff", "onSave", "onDelete"]);

const editable = ref(false);
const showDetails = ref(false);
const internalMachine = ref(props.machine);

// Dynamic refs
const scaleFactor = ref(0);
const fixedPixelWidth = ref(0);
const elementHeight = ref(0);
const showNoPreviewAvailable = ref(false);

const previewContainerRef = ref(null);

const calculateBoxStyles = computed(() => {
  const trimAreaInMm = internalMachine.value.trim_area;
  const borderWidth = trimAreaInMm * scaleFactor.value;

  return {
    borderTop: `${borderWidth}px solid #06b6d4`,
    borderRight: `${borderWidth}px solid #06b6d4`,
    borderBottom: `${borderWidth}px solid #06b6d4`,
    borderLeft: `${borderWidth}px solid #06b6d4`,
  };
});

const calculateContainerStyles = computed(() => {
  const topMarginInMm = internalMachine.value.margin_top;
  const bottomMarginInMm = internalMachine.value.margin_bottom;
  const leftMarginInMm = internalMachine.value.margin_left;
  const rightMarginInMm = internalMachine.value.margin_right;

  const topMargin = topMarginInMm * scaleFactor.value;
  const bottomMargin = bottomMarginInMm * scaleFactor.value;
  const leftMargin = leftMarginInMm * scaleFactor.value;
  const rightMargin = rightMarginInMm * scaleFactor.value;

  return {
    width: `${fixedPixelWidth.value}px`,
    height: `${elementHeight.value}px`,
    borderTop: `${topMargin}px solid #ec4899`,
    borderBottom: `${bottomMargin}px solid #ec4899`,
    borderLeft: `${leftMargin}px solid #ec4899`,
    borderRight: `${rightMargin}px solid #ec4899`,
    overflow: "hidden",
  };
});

const convertToMm = (value, unit) => {
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
};

const updateDimensions = () => {
  nextTick(() => {
    if (previewContainerRef.value) {
      fixedPixelWidth.value = previewContainerRef.value.clientWidth;
      const widthInMm = convertToMm(internalMachine.value.width, internalMachine.value.unit);
      scaleFactor.value = fixedPixelWidth.value / widthInMm;
      const heightInMm = convertToMm(internalMachine.value.height, internalMachine.value.unit);
      const calculatedHeight = heightInMm * scaleFactor.value;

      if (calculatedHeight > 700) {
        showNoPreviewAvailable.value = true;
      } else if (showNoPreviewAvailable.value) {
        updateDimensions();
        showNoPreviewAvailable.value = false;
      }
      elementHeight.value = calculatedHeight;
    } else {
      fixedPixelWidth.value = 0;
      elementHeight.value = 0;
    }
  });
};

const constructOption = (option, i) => {
  if (internalMachine.value.colors.length === 0) {
    return {
      ...option,
      spoilage: 0,
    };
  }
  return {
    ...option,
    ...internalMachine.value?.options[i]?.speed,
    spoilage: internalMachine.value?.options[i]?.spoilage,
  };
};

watch(
  () => props.machine,
  (newValue) => {
    internalMachine.value = { ...newValue };
  },
  { deep: true, immediate: true },
);

watch(
  showDetails,
  (v) => {
    if (v) {
      window.addEventListener("resize", updateDimensions);
      updateDimensions();
    } else {
      window.removeEventListener("resize", updateDimensions);
      fixedPixelWidth.value = 0;
      elementHeight.value = 0;
    }
  },
  { immediate: true },
);

watch(
  editable,
  (v) => {
    if (v) {
      window.addEventListener("resize", updateDimensions);
      // window.addEventListener("scroll", gandalf, true);
      updateDimensions();
    } else {
      // window.removeEventListener("scroll", gandalf, true);
      if (showDetails.value) return;
      window.removeEventListener("resize", updateDimensions);
      fixedPixelWidth.value = 0;
      elementHeight.value = 0;
    }
  },
  { immediate: true },
);

watch(
  internalMachine,
  () => {
    if (showDetails.value || editable.value) {
      updateDimensions();
    }
  },
  { deep: true },
);

onMounted(() => {
  if (internalMachine.value) {
    internalMachine.value.unit = "mm";
  }
  // window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  // window.removeEventListener("keydown", handleKeydown);
  // window.removeEventListener("scroll", gandalf, true);
  // window.removeEventListener("resize", updateDimensions);
});
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
