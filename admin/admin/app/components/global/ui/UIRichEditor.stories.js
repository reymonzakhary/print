import UIRichEdtor from './UIRichEditor.vue';

export default {
    title: 'global/ui/UIRichEditor',
    component: UIRichEdtor,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UIRichEdtor },
    template: `<UIRichEdtor v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    // props
};