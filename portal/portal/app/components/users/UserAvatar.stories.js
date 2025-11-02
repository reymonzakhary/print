import UserAvatar from './UserAvatar.vue';

export default {
    title: 'Users/Molecules/UserAvatar',
    component: UserAvatar,
    argTypes: {
        "size": {
            control: {
                type: "select",
                options: ["80", "responsive"]
            }
        }
    }
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UserAvatar },
    template: `<UserAvatar v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    src: "https://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50?s=200",
    size: "80",
    isLoading: false
}

export const Loading = Story.bind({});
Loading.args = {
    ...Default.args,
    isLoading: true
}

export const NoImage = Story.bind({});
NoImage.args = {
    ...Default.args,
    src: null
}