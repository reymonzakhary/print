import MockAxiosAdapter from 'axios-mock-adapter';
import Vue from 'vue';
import Vuex from 'vuex';

import UserFormModal from "./UserFormModal.vue";

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
    title: "Modals/Users/UserFormModal",
    component: UserFormModal,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UserFormModal },
    template: '<div id="__layout"><div><user-form-modal v-bind="$props" v-on="$listeners" /></div></div>',
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
        mockAxios.onGet('/countries').reply(200, {
            data: [
                {
                    "id": 1,
                    "name": "AFGHANISTAN",
                    "iso2": "AF",
                    "iso3": "AFG",
                    "un_code": 4,
                    "dial_code": "93",
                    "created_at": null,
                    "updated_at": null
                },
                {
                    "id": 2,
                    "name": "ALBANIA",
                    "iso2": "AL",
                    "iso3": "ALB",
                    "un_code": 8,
                    "dial_code": "355",
                    "created_at": null,
                    "updated_at": null
                }
            ]
        });
    }
});

export const Default = Story.bind({});