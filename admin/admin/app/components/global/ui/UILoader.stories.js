import UILoader from './UILoader.vue';

export default {
    title: 'Global/UI/Loader',
    component: UILoader
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UILoader },
    template: `<UILoader v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});