import UITextArea from './UITextArea.vue';

export default {
    title: 'Global/ui/UITextArea',
    component: UITextArea,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UITextArea },
    template: `<UITextArea v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    // props
};

export const WithMaxLength = Story.bind({});
WithMaxLength.args = {
    maxLength: 100,
};