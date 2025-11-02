<template>
  <ul class="flex justify-between w-full">
    <li key="transition_one">
      <a
        class="block px-3 py-2 transition-colors rounded-l-md"
        :class="{
          'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
            pagination.current_page === 1,
          ' text-blue-500 hover:bg-blue-400 hover:text-white': pagination.current_page > 1,
        }"
        href="#"
        @click.prevent="change(pagination.first_page)"
      >
        <font-awesome-icon :icon="['fal', 'chevron-double-left']" />
      </a>
    </li>
    <li key="transition_two">
      <a
        class="block px-3 py-2 transition-colors"
        :class="{
          'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
            pagination.current_page === 1,
          ' text-blue-500 hover:bg-blue-400 hover:text-white': pagination.current_page > 1,
        }"
        href="#"
        @click.prevent="change(pagination.current_page - 1)"
      >
        <font-awesome-icon :icon="['fal', 'chevron-left']" />
      </a>
    </li>
    <li v-for="page in pages" :key="page">
      <a
        :class="[
          page == pagination.current_page
            ? 'text-white bg-blue-500 border-blue-600'
            : 'hover:text-white hover:bg-blue-400 text-blue-500',
          'block px-3 py-2  transition-colors',
        ]"
        href="#"
        @click.stop="change(page)"
      >
        {{ page }}
      </a>
    </li>
    <li key="transition_prelast">
      <a
        class="block px-3 py-2 transition-colors hover:text-white"
        :class="{
          'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
            pagination.current_page === pagination.last_page,
          ' text-blue-500 hover:bg-blue-400 hover:text-white':
            pagination.current_page < pagination.last_page,
        }"
        href="#"
        @click.prevent="change(pagination.current_page + 1)"
      >
        <font-awesome-icon :icon="['fal', 'chevron-right']" />
      </a>
    </li>
    <li key="transition_last">
      <a
        class="block px-3 py-2 transition-colors rounded-r-md"
        :class="{
          'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
            pagination.current_page === pagination.last_page,
          ' text-blue-500 hover:bg-blue-400 hover:text-white':
            pagination.current_page < pagination.last_page,
        }"
        href="#"
        @click.prevent="change(pagination.last_page)"
      >
        <font-awesome-icon :icon="['fal', 'chevron-double-right']" />
      </a>
    </li>
  </ul>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  pagination: {
    type: Object,
    required: true,
  },
  offset: {
    type: Number,
    default: 1,
  },
});

const emit = defineEmits(["paginate"]);

const pages = computed(() => {
  if (!props.pagination.to) {
    return null;
  }
  let from = props.pagination.current_page - props.offset;
  if (from < 1) {
    from = 1;
  }
  let to = from + props.offset * 2;
  if (to >= props.pagination.last_page) {
    to = props.pagination.last_page;
  }
  const pages = [];
  for (let page = from; page <= to; page++) {
    pages.push(page);
  }
  return pages;
});

const change = (page) => {
  //   props.pagination.current_page = page;
  emit("paginate", page);
};
</script>
