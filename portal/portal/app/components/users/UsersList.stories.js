import Vue from 'vue'
import { mockedPermissionsMixin, mockPermissionsDecorator } from '../../.storybook/mockPermissions.js'

import UsersList from './UsersList.vue';

Vue.use(mockedPermissionsMixin)

export default {
    title: 'Users/Organisms/UsersList',
    component: UsersList,
    decorators: [mockPermissionsDecorator],
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UsersList },
    template: `<UsersList v-bind="$props" v-on="$listeners" />`
});

export const Default = Story.bind({});
Default.args = {
    users: [
        {
            id: 1,
            name: "John Doe",
            email: "John@Doe.com",
            email_verified_at: 1697527063,
            created: 1697527063,
        },
        {
            id: 2,
            name: "Jane Doe",
            email: "Jane@Doe.nl",
            email_verified_at: null,
            created: 1697427063,
        },
        {
            id: 3,
            name: "John Smith",
            email: "John@Smith.nl",
            email_verified_at: 1697527063,
            created: 1687527163,
        },
    ]
}
Default.parameters = {
    permissions: ['users-create', 'users-update', 'users-delete']
}

export const NoPermissions = Story.bind({});
NoPermissions.args = {
    ...Default.args,
}
NoPermissions.parameters = {
    permissions: []
}

export const NoUsers = Story.bind({});
NoUsers.args = {
    ...Default.args,
    users: []
}
NoUsers.parameters = {
    permissions: ['users-create', 'users-update', 'users-delete']
}

export const NoDataWithFilter = Story.bind({});
NoDataWithFilter.args = {
    ...Default.args,
    theFilter: ";;;"
}


export const Loading = Story.bind({});
Loading.args = {
    ...Default.args,
    isLoading: true,
    users: null,
}
Loading.parameters = {
    permissions: ['users-create', 'users-update', 'users-delete']
}

export const LongList = Story.bind({});
LongList.args = {
    ...Default.args,
    users: [
        ...Default.args.users,
        ...Default.args.users,
        ...Default.args.users,
        ...Default.args.users,
        ...Default.args.users,
    ]
}
LongList.parameters = {
    permissions: ['users-create', 'users-update', 'users-delete']
}