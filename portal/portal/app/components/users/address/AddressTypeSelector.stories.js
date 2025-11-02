import AddressTypeSelector from './AddressTypeSelector.vue';

export default {
    title: 'Users/Molecules/AddressTypeSelector',
    component: AddressTypeSelector,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { AddressTypeSelector },
    template: '<address-type-selector v-bind="$props" />',
});

export const Default = Story.bind({});

export const Checked = Story.bind({});
Checked.args = {
    addressType: "Home"
}