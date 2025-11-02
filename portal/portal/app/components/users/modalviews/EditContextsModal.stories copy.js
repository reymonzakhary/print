import MockAxiosAdapter from 'axios-mock-adapter';
import EditContextModal from './EditContextsModal.vue';

import Vue from 'vue';
import Vuex from 'vuex';

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
    title: 'Modals/Users/EditContextModal',
    component: EditContextModal,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { EditContextModal },
    template: '<div id="__layout"><div><edit-context-modal v-bind="$props" v-on="$listeners" /></div></div>',
    created() {
        const mockAxios = new MockAxiosAdapter(this.$axios);
        mockAxios.onGet('contexts').reply(200, {
            data: [
                {
                    id: 1,
                    name: "Mgr",
                },
                {
                    id: 2,
                    name: "User",
                },
                {
                    id: 3,
                    name: "Guest",
                },
            ]
        });
    },
    store: store
})

export const Default = Story.bind({});
Default.args = {
    user: {
        id: 1,
        profile: {
            first_name: "John",
            last_name: "Doe",
        },
        ctx: [],
    }
}