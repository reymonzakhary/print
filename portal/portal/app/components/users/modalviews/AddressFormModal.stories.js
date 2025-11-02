import AddressFormModal from './AddressFormModal.vue';

export default {
    title: 'Modals/Users/AddressFormModal',
    component: AddressFormModal,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { AddressFormModal },
    template: '<div id="__layout"><div><address-form-modal v-bind="$props" v-on="$listeners" /></div></div>',
})

export const Default = Story.bind({});