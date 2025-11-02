import MockAxiosAdapter from 'axios-mock-adapter';
import Vue from 'vue';
import Vuex from 'vuex';

import EditRolesModal from './EditRolesModal.vue';

Vue.use(Vuex);

const store = {
    state: {
        auth: {
            user: {
                id: 3
            }
        }
    }
}

export default {
    title: 'Modals/Users/EditRolesModal',
    component: EditRolesModal,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { EditRolesModal },
    template: '<div id="__layout"><div><edit-roles-modal v-bind="$props" v-on="$listeners" /></div></div>',
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
    store: store,
})

export const Default = Story.bind({});
Default.args = {
    user: {
        id: 1,
        profile: {
            first_name: "John",
            last_name: "Doe",
        },
        roles: [],
    }
}