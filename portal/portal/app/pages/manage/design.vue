<template>
  <div>
    <div>
      <div class="flex flex-col w-full h-full p-4 md:flex-row justify-items-center">
        <DesignProvidersList class="w-full md:w-1/4" />
        <transition name="fade">
          <DesignTemplatesList
            v-if="Object.keys(selected_provider).length > 0"
            class="w-full md:w-3/4"
          ></DesignTemplatesList>
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";

export default {
  head() {
    return {
      title: `${this.$t("design templates")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      selected_provider: (state) => state.design.selected_provider,
    }),
  },
  async created() {
    await this.get_templates();
    await this.get_providers();
  },
  methods: {
    ...mapActions({
      get_providers: "design/get_providers",
      get_templates: "design/get_templates",
    }),
  },
};
</script>
