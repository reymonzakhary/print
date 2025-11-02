import MockAxiosAdapter from 'axios-mock-adapter';
import UIResourceSelector from './UIResourceSelector.vue';

export default {
    name: 'Global/UI/ParentSelector',
    component: UIResourceSelector,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    created() {
        const mockAxios = new MockAxiosAdapter(this.$axios);
        mockAxios.onGet('/modules/cms/tree').reply(200, {
            "data": [
                {
                    "id": 25,
                    "title": "New Resource 2",
                    "parent_id": null,
                    "resource_id": 25,
                    "language": "en",
                    "sort": 1,
                    "isfolder": false,
                    "published": true,
                    "hidden": false,
                    "hide_children_in_tree": false
                },
                {
                    "id": 33,
                    "title": "Child child Services 3",
                    "parent_id": null,
                    "resource_id": 33,
                    "language": "en",
                    "sort": 4,
                    "isfolder": false,
                    "published": true,
                    "hidden": false,
                    "hide_children_in_tree": false
                },
                {
                    "id": 17,
                    "title": "Landings Page - Gemeente Gent",
                    "parent_id": 25,
                    "resource_id": 17,
                    "language": "en",
                    "sort": 2,
                    "isfolder": true,
                    "published": true,
                    "hidden": false,
                    "hide_children_in_tree": false
                },
                {
                    "id": 29,
                    "title": "Test!",
                    "parent_id": 17,
                    "resource_id": 29,
                    "language": "en",
                    "sort": 3,
                    "isfolder": true,
                    "published": true,
                    "hidden": false,
                    "hide_children_in_tree": false
                }
            ]
        });
    },
    components: { UIResourceSelector },
    template: `<UIResourceSelector v-bind="$props" v-on="$listeners" />`,
});

export const IsFetching = Story.bind({});
IsFetching.args = {
    value: "alwaysFetching",
};

export const IsFetched = Story.bind({});
IsFetched.args = {
    value: "0",
};

export const invalid = Story.bind({});
invalid.args = {
    value: "0",
    invalid: true,
};