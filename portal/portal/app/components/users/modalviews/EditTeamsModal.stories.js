import MockAxiosAdapter from 'axios-mock-adapter';

import EditTeamsModal from './EditTeamsModal.vue';

export default {
    title: 'Modals/Users/EditTeamsModal',
    component: EditTeamsModal,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { EditTeamsModal },
    template: '<div id="__layout"><div><edit-teams-modal v-bind="$props" v-on="$listeners" /></div></div>',
    created() {
        const mockAxios = new MockAxiosAdapter(this.$axios);
        mockAxios.onGet('teams?per_page=99999').reply(200, {
            data: [
                {
                    id: 1,
                    name: "Mgr",
                    admin: false,
                    authorizer: false,
                },
                {
                    id: 2,
                    name: "User",
                    admin: false,
                    authorizer: false,
                },
                {
                    id: 3,
                    name: "Guest",
                    admin: false,
                    authorizer: false,
                },
            ]
        });
    },
})

export const Default = Story.bind({});
Default.args = {
    user: {
        id: 0,
        profile: {
            first_name: "John",
            last_name: "Doe",
        }
    }
}