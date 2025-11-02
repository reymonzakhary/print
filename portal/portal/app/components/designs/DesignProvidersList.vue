<template>
  <div class="sticky top-4">
    <h2 class="p-2 text-sm font-bold tracking-wide uppercase">
      {{ $t("design providers") }}
    </h2>

    <nav class="flex-col w-full">
      <template v-for="provider in providers" :key="provider">
        <div
          v-if="provider.name !== 'Conneo preflight'"
          :class="{
            'bg-theme-100 dark:bg-theme-900 text-theme-500':
              selected_provider && selected_provider.id === provider.id,
          }"
          class="flex items-center px-2 py-1 my-1 capitalize transition-colors duration-100 rounded cursor-pointer focus:outline-none hover:text-theme-500"
          @click="set_selected_provider(provider)"
        >
          {{ provider.name }}
        </div>
      </template>
    </nav>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
export default {
  computed: {
    ...mapState({
      providers: (state) => state.design.providers,
      selected_provider: (state) => state.design.selected_provider,
    }),
  },
  methods: {
    ...mapMutations({
      set_selected_provider: "design/set_selected_provider",
    }),
  },
};
</script>
