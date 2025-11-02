<template>
  <SidePanel v-if="$store.state.blueprint.show_settings">
    <template #side-panel-header>
      <h3 class="p-4 font-bold tracking-wide uppercase">
        {{ $store.state.blueprint.settings.self.name }}
      </h3>
    </template>

    <template #side-panel-content>
      <section class="px-4">
        <div
          class="flex items-center justify-around w-full py-2 bg-gray-100 rounded"
        >
          <div class="w-1/3">
            <div
              :class="`px-4 py-1 ml-2 font-bold border-2 rounded-full text-center w-full bg-${$store.state.blueprint.settings.from.data.color}-100 border-white`"
            >
              {{ $store.state.blueprint.settings.from.name }}
            </div>
          </div>
          <font-awesome-icon
            :icon="['fal', 'right']"
            class="mr-1 text-2xl text-theme-500"
          />
          <div class="w-1/3">
            <!-- {{ $t("to") }} -->
            <div
              :class="`px-4 py-1 ml-2 font-bold border-2 w-full text-center rounded-full bg-${$store.state.blueprint.settings.to.data.color}-100 border-white`"
            >
              {{ $store.state.blueprint.settings.to.name }}
            </div>
          </div>
        </div>
      </section>
    </template>
  </SidePanel>
</template>

<script>
import { mapMutations } from "vuex";
export default {
  methods: {
    ...mapMutations({
      toggle_actionpanel: "blueprint/toggle_actionpanel",
      populate_settings: "blueprint/populate_settings",
    }),
    close() {
      this.populate_settings({});
      this.toggle_actionpanel(false);
    },
  },
};
</script>
