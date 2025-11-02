<template>
  <!-- /** 
* accordion take data attr
* slot takes box attribute
* example: 
*           <accordion :data="data">
*               template v-slot="{box}">   <- !child-labor! this returns the data after the v-for to the parent, so you can work with it's children
*                    --your content html--
*                </template>
*            </accordion>
*/ -->
  <div>
    <div v-for="(box, index) in data" :key="index">
      <section
        class="relative flex w-full flex-col"
        :class="{
          'my-6 rounded border border-gray-200 px-4 py-6 dark:border-gray-500':
            divided && box.divider?.length > 0 && box.divider !== null,
          '!border-gray-200':
            box.divider === 'null' || box.divider === 'undefined' || box.divider === '',
          '!mt-0 ml-0 rounded-t-none !border-t-0 !pt-0': data[index - 1]?.divider === box.divider,
          '!mb-0 mr-0 rounded-b-none !border-b-0 !pb-0': data[index + 1]?.divider === box.divider,
        }"
      >
        <div
          v-if="
            (divided && data[index - 1]?.divider !== box.divider) ||
            (index === 0 && box.divider?.length > 0 && box.divider !== null)
          "
          class="absolute mx-auto -mt-9 bg-white px-2 text-sm font-bold uppercase tracking-wider text-gray-500 dark:bg-gray-700"
          :class="{
            '!text-gray-300':
              box.divider === 'null' || box.divider === 'undefined' || box.divider === '',
          }"
        >
          {{
            box.divider === "null" || box.divider === "undefined" || box.divider === ""
              ? $t("not divided")
              : box.divider
          }}
        </div>
        <header
          class="group mb-1 flex w-full cursor-pointer justify-between rounded p-2 shadow-md transition hover:shadow-lg"
          :class="{
            'sticky top-0 mb-0': active_box,
            'mb-2 border-2 shadow-none dark:border-gray-800': wizardMode,
            'border-theme-500 bg-theme-50 dark:border-theme-500 dark:bg-theme-700':
              wizardMode && active_box === index,
            'bg-white dark:bg-gray-700': !wizardMode,
          }"
          @click="index === active_box ? toggle_active_box(null) : toggle_active_box(index)"
        >
          <div class="flex w-1/2 items-center justify-between">
            <div class="flex-1">{{ $display_name(box.display_name) }}</div>
            <small
              v-tooltip="$t('original name')"
              class="ml-2 flex-1 text-gray-500 dark:text-gray-400"
            >
              {{ box.name }}
            </small>

            <div class="flex-1">
              <font-awesome-icon
                v-if="box.calc_ref"
                class=""
                fixed-width
                :icon="['fal', box?.calc_ref?.length > 0 ? calcRef(box.calc_ref) : 'calculator']"
                :class="[box?.calc_ref?.length > 0 ? 'text-green-400' : 'text-amber-400']"
              />
              <font-awesome-icon
                v-if="!box.calc_ref"
                class="text-xs"
                fixed-with
                :icon="['fas', 'exclamation']"
                :class="['text-amber-500']"
              />
              <button
                v-if="editable"
                class="invisible ml-2 rounded-full px-2 text-red-500 hover:bg-red-100 group-hover:visible dark:text-red-300 dark:hover:bg-red-800"
                @click.stop="$emit('removeBox', box, 'box', index)"
              >
                <font-awesome-icon aria-hidden="true" :icon="['fal', 'trash-can']" />
              </button>
            </div>
          </div>

          <div class="flex items-center">
            <!-- <small v-tooltip="$t('divider')" class="mr-2 text-gray-500">
              {{ box.divider }}
            </small> -->
            <button
              v-if="editable"
              class="invisible mr-4 rounded-full px-2 text-theme-500 hover:bg-theme-100 group-hover:visible dark:text-theme-300 dark:hover:bg-theme-800"
              @click.stop="$emit('editBox', box, 'editBox', index)"
            >
              <font-awesome-icon aria-hidden="true" :icon="['fal', 'pencil']" />
            </button>
            <button aria-label="more options">
              <font-awesome-icon aria-hidden="true" :icon="['fal', 'angle-down']" />
            </button>
          </div>
        </header>

        <div class="accordion" :class="{ open: active_box === index }">
          <div>
            <slot :box="box" :index="index"> {{ $t("content") }}</slot>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
export default {
  props: {
    data: {
      type: Array,
      required: true,
    },
    editable: {
      type: Boolean,
      default: true,
      required: false,
    },
    divided: {
      type: Boolean,
      default: false,
      required: false,
    },
    divider: {
      type: String,
      default: "",
      required: false,
    },
    wizardMode: {
      type: Boolean,
      default: false,
      required: false,
    },
  },
  emits: ["editBox", "removeBox"],

  computed: {
    ...mapState({
      active_box: (state) => state.accordion.active_box,
    }),
  },

  methods: {
    ...mapMutations({
      toggle_active_box: "accordion/toggle_active_box",
    }),
    calcRef(calc_ref) {
      switch (calc_ref) {
        case "format":
          return "ruler-combined";
        case "material":
          return "file";
        case "weight":
          return "weight-hanging";
        case "printing_colors":
          return "circles-overlap-3";
        default:
          return "check";
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.accordion {
  // margin-top: -100px;
  overflow: hidden;
}
.accordion.open {
  transition: all ease 200ms;
  max-height: 100%;
  opacity: 1;
  padding: 1.5rem !important;
}
.accordion {
  transition: all ease 200ms;
  max-height: 0;
  opacity: 0;
  padding: 0 !important;
}

// sorting styles
.ghost {
  @apply rounded outline-dashed outline-1 outline-theme-500;
}
</style>
