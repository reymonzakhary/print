<template>
  <UISelector
    :is-loading="isLoading"
    :options="templates"
    :value="value"
    name="template"
    @input="$emit('input', $event)"
  />
</template>

<script>
export default {
  name: "TemplateSelector",
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
      templates: null,
      isLoading: true,
    };
  },
  async beforeCreate() {
    try {
      const templates = await this.api.get("modules/cms/templates");
      const normalizedTemplates = this.normalizeTemplates(templates.data);
      this.templates = normalizedTemplates;
      this.isLoading = false;
    } catch (e) {
      console.error(e);
    }
  },
  methods: {
    normalizeTemplates(templates) {
      return templates.map((template) => {
        return {
          value: template.id,
          label: template.name,
        };
      });
    },
  },
};
</script>
