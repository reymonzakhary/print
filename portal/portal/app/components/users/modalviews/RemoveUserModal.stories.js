import RemoveUserModal from './RemoveUserModal.vue';

export default {
    title: 'Modals/Users/RemoveUserModal',
    component: RemoveUserModal,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { RemoveUserModal },
    template: '<div id="__layout"><div><remove-user-modal v-bind="$props" v-on="$listeners" /></div></div>',
})

export const Default = Story.bind({});