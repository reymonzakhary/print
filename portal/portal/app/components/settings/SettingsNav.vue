<template>
  <div class="sticky top-4">
    <h2 class="p-2 text-sm font-bold uppercase tracking-wide">
      {{ $t("settings") }}
      {{ $t("navigation") }}
    </h2>
    <nav class="w-full flex-col">
      <template v-for="namespace in namespaces" :key="namespace">
        <SettingsNavButton
          :namespace="namespace"
          @on-namespace-filter="$emit('handleNamespaceFilter', $event)"
        />
      </template>
    </nav>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions, mapGetters } from "vuex";
export default {
  computed: {
    ...mapState({
      component: (state) => state.settings.component,
      settings: (state) => state.settings.settings,
      namespace: (state) => state.settings.namespace,
    }),
    namespaces() {
      const namespaces = [...new Set(this.settings.namespace)];
      // const namespaces = [...new Set(this.$store.state.settings.meta.modules.namespaces.map(item => item.namespace))];
      // let namespaces = this.$store.state.settings.meta.modules.namespaces.filter((value, index, array) => array.indexOf(value) === index);
      // namespaces = namespaces.filter((value, index, array) => array.indexOf(value) === index);

      return namespaces.sort();
    },
  },
  mounted() {
    if (this.namespace === "") {
      this.set_namespace("core");
    }
  },
  methods: {
    ...mapMutations({
      set_component: "settings/set_component",
      set_namespace: "settings/set_namespace",
    }),
    ...mapActions({
      set_namespace_settings: "settings/set_namespace_settings",
      set_namespace_areas: "settings/set_namespace_areas",
    }),
  },
};
</script>
