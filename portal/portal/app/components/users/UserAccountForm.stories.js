import UserAccountForm from './UserAccountForm.vue';

export default {
    title: 'Users/Organisms/UserAccountForm',
    component: UserAccountForm,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UserAccountForm },
    template: '<user-account-form v-bind="$props" v-on="$listeners" />',
});

export const Default = Story.bind({});

export const isEditing = Story.bind({});
isEditing.args = {
    user: {
        id: 1,
        email: "john@doe.nl",
        profile: {
            first_name: "John",
            last_name: "Doe",
            gender: "male",
        }
    }
}