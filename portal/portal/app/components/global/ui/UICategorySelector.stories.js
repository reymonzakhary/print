import UISelector from './UISelector.vue';

export default {
    name: 'Global/UI/UISelector',
    component: UISelector,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UISelector },
    template: `<UISelector v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    value: "0",
    options: [
        { value: "1", label: 'One' },
        { value: "2", label: 'Two' },
        { value: "3", label: 'Three' },
    ],
};

export const Disabled = Story.bind({});
Disabled.args = {
    ...Default.args,
    disabled: true,
};

export const IsLoading = Story.bind({});
IsLoading.args = {
    isLoading: true
};

export const Invalid = Story.bind({});
Invalid.args = {
    value: "0",
    invalid: true,
};