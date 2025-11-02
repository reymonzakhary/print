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
        class="relative flex flex-col w-full"
        :class="{
          'border-2 border-gray-300 dark:border-gray-500 rounded px-4 py-6 my-6':
            divided && box.divider?.length > 0 && box.divider !== null,
          '!border-gray-200':
            box.divider === 'null' || box.divider === 'undefined' || box.divider === '',
          '!border-t-0 rounded-t-none ml-0 !pt-0 !mt-0': data[index - 1]?.divider === box.divider,
          '!border-b-0 rounded-b-none mr-0 !pb-0 !mb-0': data[index + 1]?.divider === box.divider,
        }"
      >
        <!--  -->
        <div
          v-if="
            (divided && data[index - 1]?.divider !== box.divider) ||
            (index === 0 && box.divider?.length > 0 && box.divider !== null)
          "
          class="absolute px-2 mx-auto text-sm font-bold tracking-wider text-gray-500 uppercase bg-gray-100 -mt-9 dark:bg-gray-800 backdrop-opacity-50 backdrop-blur-md"
          :class="{
            '!text-gray-300 ':
              box.divider === 'null' || box.divider === 'undefined' || box.divider === '',
          }"
        >
          {{
            box.divider === "null" || box.divider === "undefined" || box.divider === ""
              ? "not divided"
              : box.divider
          }}
        </div>
        <section
          class="flex justify-between w-full p-2 mb-1 transition bg-white rounded shadow-md cursor-pointer dark:bg-gray-700 group hover:shadow-lg"
          :class="{ 'sticky top-0 ': activeBox }"
          @click="index === activeBox ? toggleactiveBox(null) : toggleactiveBox(index)"
        >
          <div>
            {{ box.name }}
            <small v-tooltip="'system key'" class="ml-2 text-gray-500">
              {{ box.system_key }}
            </small>
          </div>

          <div class="flex items-center">
            <small v-tooltip="'divider'" class="mr-2 text-gray-500">
              {{ box.divider }}
            </small>
            <!-- <button
              v-if="editable"
              class="invisible px-2 mr-4 rounded-full text-theme-500 group-hover:visible hover:bg-theme-100 dark:hover:bg-theme-800"
              @click.stop="emit('editBox', box, 'editBox', index)"
            >
              <font-awesome-icon aria-hidden="true" :icon="['fal', 'pencil']" />
            </button> -->
            <button
              class="invisible px-2 ml-2 text-red-500 rounded-full group-hover:visible hover:bg-red-100 dark:hover:bg-red-800"
              @click.stop="emit('removeBox', { box, index })"
            >
              <font-awesome-icon aria-hidden="true" :icon="['fal', 'trash-can']" />
            </button>
            <button aria-label="more options">
              <font-awesome-icon aria-hidden="true" :icon="['fal', 'angle-down']" />
            </button>
          </div>
        </section>

        <div class="accordion" :class="{ open: activeBox === index }">
          <div class="">
            <slot :box="box" :index="index"> content</slot>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
<script setup>
const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  editable: {
    type: Boolean,
    default: true,
    required: false,
  },
  activeBox: {
    type: Number,
    default: null,
    required: false,
  },
  divided: {
    type: Boolean,
    default: false,
    required: false,
  },
});

const emit = defineEmits(["editBox", "toggleActiveBox", "removeBox"]);

const toggleactiveBox = (index) => {
  emit("toggleActiveBox", index);
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
  @apply outline-1 outline-dashed outline-theme-500 rounded;
}
</style>
