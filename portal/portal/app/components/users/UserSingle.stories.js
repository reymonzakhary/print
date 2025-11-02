import Vue from 'vue'
import { mockedPermissionsMixin, mockPermissionsDecorator } from '../../.storybook/mockPermissions.js'

import UserSingle from './UserSingle.vue';

Vue.use(mockedPermissionsMixin)

export default {
    title: 'Users/Molecules/UserSingle',
    component: UserSingle,
    decorators: [mockPermissionsDecorator],
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UserSingle },
    template: `<UserSingle v-bind="$props" v-on="$listeners" />`
});

export const Default = Story.bind({});
Default.args = {
    user: {
        id: 1,
        name: "John Doe",
        email: "john@doe.com",
        email_verified_at: 1697527063,
    },
}
Default.parameters = {
    permissions: ['users-update', 'users-delete']
}

export const VerifiedNoPermissions = Story.bind({});
VerifiedNoPermissions.args = {
    ...Default.args,
}
VerifiedNoPermissions.parameters = {
    permissions: []
}

export const Unverified = Story.bind({});
Unverified.args = {
    ...Default.args,
    user: {
        ...Default.args.user,
        email_verified_at: null
    }
}
Unverified.parameters = {
    permissions: ['users-update', 'users-delete']
}

export const UnverifiedNoPermissions = Story.bind({});
UnverifiedNoPermissions.args = {
    ...Default.args,
    user: {
        ...Default.args.user,
        email_verified_at: null,
    }
}
UnverifiedNoPermissions.parameters = {
    permissions: []
}

export const Selected = Story.bind({});
Selected.args = {
    ...Default.args,
    selected: true
}
Selected.parameters = {
    permissions: ['users-update', 'users-delete']
}