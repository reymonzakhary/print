import RemoveAddressModal from './RemoveAddressModal.vue';

export default {
    title: 'Modals/Users/RemoveAddressModal',
    component: RemoveAddressModal,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { RemoveAddressModal },
    template: '<div id="__layout"><div><remove-address-modal v-bind="$props" v-on="$listeners" /></div></div>',
})

export const Default = Story.bind({});