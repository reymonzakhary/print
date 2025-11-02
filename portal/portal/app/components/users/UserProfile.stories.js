import Vue from 'vue';
import { mockPermissionsDecorator, mockedPermissionsMixin } from '../../.storybook/mockPermissions.js';

import UserProfile from './UserProfile.vue';

Vue.use(mockedPermissionsMixin)

export default {
    title: 'Users/Organisms/UserProfile',
    component: UserProfile,
    decorators: [mockPermissionsDecorator],
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UserProfile },
    template: `<UserProfile v-bind="$props" v-on="$listeners" />`
});

export const Default = Story.bind({});
Default.args = {
    hasViewPermission: true,
    userId: 0,
    profile: {
        first_name: "John",
        last_name: "Doe",
        dob: "1980-01-01",
        bio: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, sapien vel bibendum bibendum, velit sapien bibendum sapien, vel bibendum sapien sapien vel sapien. Sed euismod, sapien vel bibendum bibendum, velit sapien bibendum sapien, vel bibendum sapien sapien vel sapien.",
        gender: "male",
        avatar: "https://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50?s=200",
    },
    isLoading: false
}
Default.parameters = {
    permissions: ['users-profiles-update', 'users-profiles-read']
}

export const Loading = Story.bind({});
Loading.args = {
    ...Default.args,
    isLoading: true
}
Loading.parameters = {
    permissions: ['users-profiles-update', 'users-profiles-read']
}

export const NoPermissions = Story.bind({});
NoPermissions.args = {
    ...Default.args,
    hasViewPermission: false,
}