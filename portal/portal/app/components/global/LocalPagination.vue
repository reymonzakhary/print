<template>
  <div v-if="pagination" class="flex items-center justify-center">
    <!-- {{ pagination }} -->
    <ul class="bottom-1 flex justify-between rounded-md bg-white shadow dark:bg-gray-700">
      <li key="transition_one">
        <a
          class="block rounded-l-md px-3 py-2 transition-colors"
          :class="{
            'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
              pagination.current_page === 1,
            'text-theme-500 hover:bg-theme-400 hover:text-white': pagination.current_page > 1,
          }"
          @click.prevent="
            ((loader = true),
            $emit('pagination', {
              page: 1,
              type: 'set',
            }))
          "
        >
          <font-awesome-icon :icon="['fal', 'chevrons-left']" />
        </a>
      </li>
      <li key="transition_two">
        <a
          class="block px-3 py-2 transition-colors"
          :class="{
            'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
              pagination.current_page === 1,
            'text-theme-500 hover:bg-theme-400 hover:text-white': pagination.current_page > 1,
          }"
          @click.prevent="
            ((loader = true),
            $emit('pagination', {
              page: pagination.current_page - 1,
              type: 'set',
            }))
          "
        >
          <font-awesome-icon :icon="['fal', 'chevron-left']" />
        </a>
      </li>
      <li v-for="page in pagination.last_page" :key="'first_set_' + page">
        <a
          v-if="
            page > pagination.current_page - 2 &&
            page < pagination.current_page + 2 &&
            page > 0 &&
            page < pagination.last_page - 2
          "
          :class="[
            page === pagination.current_page
              ? 'border-theme-600 bg-theme-400 text-white'
              : 'text-theme-500 hover:bg-theme-400 hover:text-white',
            'block px-3 py-2 transition-colors',
          ]"
          href="#"
          @click.stop="
            ((loader = true),
            $emit('pagination', {
              page: page,
              type: 'set',
            }))
          "
        >
          {{ page }}
        </a>
      </li>
      <li
        v-if="pagination.current_page < pagination.last_page - 2"
        class="flex h-full items-center"
      >
        ...
      </li>
      <li v-for="page in pagination.last_page" :key="'last_set_' + page">
        <a
          v-if="page > pagination.last_page - 3"
          :class="[
            page === pagination.current_page
              ? 'border-theme-600 bg-theme-400 text-white'
              : 'text-theme-500 hover:bg-theme-400 hover:text-white',
            'block px-3 py-2 transition-colors',
          ]"
          href="#"
          @click.stop="
            ((loader = true),
            $emit('pagination', {
              page: page,
              type: 'set',
            }))
          "
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
            'text-theme-500 hover:bg-theme-400 hover:text-white':
              pagination.current_page < pagination.last_page,
          }"
          href="#"
          @click.prevent="
            ((loader = true),
            $emit('pagination', {
              page: pagination.current_page + 1,
              type: 'set',
            }))
          "
        >
          <font-awesome-icon :icon="['fal', 'chevron-right']" />
        </a>
      </li>
      <li key="transition_last">
        <a
          class="block rounded-r-md px-3 py-2 transition-colors"
          :class="{
            'pointer-events-none text-gray-500 hover:bg-white hover:text-gray-500':
              pagination.current_page === pagination.last_page,
            'text-theme-500 hover:bg-theme-400 hover:text-white':
              pagination.current_page < pagination.last_page,
          }"
          href="#"
          @click.prevent="
            ((loader = true),
            emit('pagination', {
              page: pagination.last_page,
              type: 'set',
            }))
          "
        >
          <font-awesome-icon :icon="['fal', 'chevrons-right']" />
        </a>
      </li>
    </ul>
  </div>
</template>
<script setup>
const props = defineProps({
  pagination: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["pagination"]);

watch(
  () => props.pagination,
  (newVal) => {
    return newVal;
  },
  { deep: true },
);

// Methods can be defined as regular functions if needed
// function change(page) {
//   props.pagination.current_page = page;
//   emit('paginate');
// }
</script>
