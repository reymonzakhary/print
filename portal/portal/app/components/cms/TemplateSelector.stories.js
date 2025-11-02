import TemplateSelector from "./TemplateSelector.vue";

export default {
  title: "CMS/Atoms/TemplateSelector",
  component: TemplateSelector,
};

const Story = (args, { argTypes }) => ({
  props: Object.keys(argTypes),
  components: { TemplateSelector },
  template: `<TemplateSelector v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
  // props
};
