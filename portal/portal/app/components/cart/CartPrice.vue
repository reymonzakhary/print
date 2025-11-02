<template>
  <div
    class="sticky top-0 flex h-full w-full flex-col p-4 pb-40 xl:rounded-r"
    style="max-height: calc(100vh - 100px)"
  >
    <CartReceipt />

    <footer
      class="fixed bottom-0 right-0 mb-10 flex w-full justify-between bg-white p-2 shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900 md:w-2/3 lg:absolute lg:w-48 xl:absolute xl:mb-0 xl:mr-0 xl:w-full xl:rounded"
    >
      <span class="w-full">
        <button
          class="my-2 w-full rounded-full border border-green-500 px-2 py-1 text-sm text-green-500 transition-colors duration-150 hover:bg-green-100"
          @click="set_checkout(true)"
        >
          <font-awesome-icon :icon="['fas', 'check']" />
          {{ $t("checkout") }}
        </button>
      </span>
    </footer>
  </div>
</template>

<script>
import { mapMutations } from "vuex";

export default {
  methods: {
    ...mapMutations({
      set_checkout: "cart/set_checkout",
    }),
  },
};
</script>
