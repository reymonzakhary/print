import EditUserProfileModal from './EditUserProfileModal.vue';

export default {
    title: 'Modals/Users/EditUserProfileModal',
    component: EditUserProfileModal,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { EditUserProfileModal },
    template: '<div id="__layout"><div><edit-user-profile-modal v-bind="$props" v-on="$listeners" /></div></div>',
})

export const Default = Story.bind({});
Default.args = {
    profile: {
        first_name: "John",
        last_name: "Doe",
        dob: "2020-10-10",
        bio: "This is a demo bio for John Doe."
    }
}