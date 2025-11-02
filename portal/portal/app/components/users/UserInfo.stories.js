import Vue from 'vue';
import { mockPermissionsDecorator, mockedPermissionsMixin } from '../../.storybook/mockPermissions.js';

import UserInfo from './UserInfo.vue';

Vue.use(mockedPermissionsMixin)

export default {
    title: 'Users/Organisms/UserInfo',
    component: UserInfo,
    argTypes: {
        "user": {
            control: {
                type: "object",
            },
        },
    },
    decorators: [mockPermissionsDecorator],
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UserInfo },
    template: `<UserInfo v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    user: {
        roles: [
            { name: 'admin' },
            { name: 'user' },
        ],
        teams: [
            { name: 'team1' },
            { name: 'team2' },
        ],
        ctx: [
            { name: 'ctx1' },
            { name: 'ctx2' },
        ]
    }
}
Default.parameters = {
    permissions: ['acl-roles-list', 'teams-list', 'contexts-list']
}

export const NoPermissions = Story.bind({});
NoPermissions.args = {
    ...Default.args,
}
NoPermissions.parameters = {
    permissions: []
}

export const NoRoles = Story.bind({});
NoRoles.args = {
    ...Default.args,
    user: {
        ...Default.args.user,
        roles: [],
    }
}
NoRoles.parameters = {
    ...Default.parameters,
}

export const Loading = Story.bind({});
Loading.args = {
    ...Default.args,
    user: null,
    isLoading: true
}
Loading.parameters = {
    ...Default.parameters,
}