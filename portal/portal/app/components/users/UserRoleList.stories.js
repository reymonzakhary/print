import MockAxiosAdapter from 'axios-mock-adapter';
import Vue from 'vue';
import Vuex from 'vuex';

import UserRoleList from './UserRoleList.vue';

Vue.use(Vuex);

const store = {
    state: {
        auth: {
            user: {
                id: 1
            }
        }
    }
}

export default {
    title: 'Users/Organisms/UserRoleList',
    component: UserRoleList,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UserRoleList },
    created() {
        const mockAxios = new MockAxiosAdapter(this.$axios);
        mockAxios.onGet('acl/roles').reply(200, {
            data: [
                {
                    id: 1,
                    display_name: "Mgr",
                },
                {
                    id: 2,
                    display_name: "User",
                },
                {
                    id: 3,
                    display_name: "Guest",
                },
            ]
        });
    },
    template: '<user-role-list v-bind="$props" />',
    store: store
});

export const Default = Story.bind({});
Default.args = {
    isLoading: false,
    user: {
        id: 2,
        storybook: false,
        roles: []
    }
}

export const SameUser = Story.bind({});
SameUser.args = {
    ...Default.args,
    user: {
        ...Default.args.user,
        id: 1,
    }
}

export const IsFetching = Story.bind({});
IsFetching.args = {
    ...Default.args,
    user: {
        ...Default.args.user,
        storybook: true,
    },
    isLoading: true
}

