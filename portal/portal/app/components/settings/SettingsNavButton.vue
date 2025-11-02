<template>
  <div
    :class="{
      'bg-theme-100 dark:bg-theme-900 text-theme-500':
        $store.state.settings.namespace === namespace,
    }"
    class="flex items-center px-2 py-1 my-1 capitalize transition-colors duration-100 rounded cursor-pointer focus:outline-none hover:text-theme-500"
    @click="
      set_namespace(namespace),
        set_namespace_icon(icon),
        $emit('onNamespaceFilter',{ namespace: namespace })
    "
  >
    <font-awesome-icon
      :icon="['fal', icon ? icon : 'wrench']"
      class="mr-2"
      fixed-width
    />
    {{ namespace }}
  </div>
</template>

<script>
import { mapActions, mapMutations } from "vuex";

export default {
  props: {
    namespace: {},
    component: "",
  },
  computed: {
    icon() {
      switch (this.namespace) {
        case "core":
          return "sliders-v";
          break;
        case "dashboard":
          return "tachometer-fastest";
          break;
        case "productfinder":
          return "radar";
          break;
        case "orders":
          return "file-invoice-dollar";
          break;
        case "invoice":
          return "file-invoice-dollar";
          break;
        case "customers":
          return "users";
          break;
        case "users":
          return "user-group-crown";
          break;
        case "assortment":
          return "box-full";
          break;
        case "cms":
          return "windows";
          break;
        case "media manager":
          return "media";
          break;
        default:
          return "sliders-v";
          break;
      }
    },
  },
  methods: {
    ...mapMutations({
      set_namespace: "settings/set_namespace",
      set_namespace_icon: "settings/set_namespace_icon",
      set_component: "settings/set_component",
    }),
   //  ...mapActions({
   //    get_settings: "settings/get_settings",
   //  }),
  },
};
</script>

<style></style>
