<template>
  <UISelector
    :is-loading="isLoading"
    :options="resourceTypes"
    :value="value"
    name="resourceType"
    @input="$emit('input', $event)"
  />
</template>

<script>
export default {
  name: "ResourceTypeSelector",
  props: {
    value: {
      type: [String, Number],
      default: "0",
    },
  },
  emits: ["input"],
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      resourceTypes: null,
      isLoading: true,
    };
  },
  async beforeCreate() {
    try {
      const resourceTypes = await this.api.get("modules/cms/resources/types");
      const normalizedResourceTypes =
        this.normalizeResourceTypes(resourceTypes);
      this.resourceTypes = normalizedResourceTypes;
      this.isLoading = false;
    } catch (e) {
      console.error(e);
    }
  },
  methods: {
    normalizeResourceTypes(resourceTypes) {
      return resourceTypes.map((resourceType) => {
        return {
          value: resourceType.id,
          label: resourceType.name,
          disabled: resourceType.id !== 1 && resourceType.id !== 9,
        };
      });
    },
  },
};
</script>
