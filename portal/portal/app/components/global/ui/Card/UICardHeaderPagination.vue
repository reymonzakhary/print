<template>
  <div>
    <font-awesome-icon
      v-if="props.loading"
      :icon="['fad', 'spinner-third']"
      spin
      class="text-white"
    />
    <div v-else class="flex items-center gap-[3px]">
      <label for="page" class="text-xs mt-[1px]">{{ $t("page") }}</label>
      <select
        name="page"
        class="text-sm border border-white rounded text-themecontrast-400 bg-theme-400"
        @change="handlePageChange"
      >
        <option
          v-for="count in props.lastPage"
          :key="count"
          :value="count"
          :selected="count === props.page"
        >
          {{ count }}
        </option>
      </select>
      <label for="page" class="text-xs mt-[1px]">{{ $t("of") }}</label>
      <label for="page" class="text-sm mt-[1px]">{{ props.lastPage }}</label>

      <span class="flex items-center pl-2 mx-1 border-l">
        <select
          class="text-sm border border-white rounded bg-theme-400"
          @change="handlePerPageChange"
        >
          <option value="10" :selected="props.perPage === 10">10</option>
          <option value="25" :selected="props.perPage === 25">25</option>
          <option value="50" :selected="props.perPage === 50">50</option>
          <option value="100" :selected="props.perPage === 100">100</option>
        </select>
        <p class="ml-2 text-xs mt-[1px]">{{ $t("per page") }}</p>
      </span>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  lastPage: {
    type: Number,
    required: true,
  },
  page: {
    type: Number,
    default: 1,
  },
  perPage: {
    type: Number,
    required: true,
    validator: (value) => [10, 25, 50, 100].includes(value),
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emits = defineEmits(["update:page", "update:perPage"]);

function handlePageChange(event) {
  if (event.target.value !== props.page) {
    emits("update:page", event.target.value);
  }
}

function handlePerPageChange(event) {
  if (event.target.value !== props.perPage) {
    emits("update:perPage", event.target.value);
  }
}
</script>
