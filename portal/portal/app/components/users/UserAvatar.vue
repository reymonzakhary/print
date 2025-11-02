<template>
  <div
    class="flex rounded-full border-4 border-white bg-gray-100 dark:border-gray-800"
    :class="{
      'h-20 w-20': size === '80',
      'h-full w-full': size === 'responsive',
      'loading bg-gray-200': isLoading,
    }"
  >
    <template v-if="isLoading" />
    <img v-else-if="src" class="h-full w-full rounded-full object-cover" :src="src" />
    <font-awesome-icon
      v-else
      :icon="['fad', randomUserIcon]"
      class="m-auto flex text-4xl text-gray-500"
    />
  </div>
</template>

<script>
export default {
  name: "UserAvatar",
  props: {
    src: {
      type: [String, Boolean],
      default: null,
    },
    size: {
      type: String,
      default: "80",
      validator: function (value) {
        return ["80", "responsive"].includes(value);
      },
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      randomUserIcon: "user-xmark",
    };
  },
};
</script>
