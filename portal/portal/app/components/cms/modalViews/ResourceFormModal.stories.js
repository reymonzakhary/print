import Vue from 'vue';
import { mockPermissionsDecorator, mockedPermissionsMixin } from '../../../.storybook/mockPermissions.js';
import MockAxiosAdapter from 'axios-mock-adapter';

import ResourceFormModal from './ResourceFormModal.vue'

Vue.use(mockedPermissionsMixin)

export default {
    title: "Modals/Resources/ResourceFormModal",
    component: ResourceFormModal,
    decorators: [mockPermissionsDecorator],
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    created() {
            const mockAxios = new MockAxiosAdapter(this.$axios);
            mockAxios.onGet('/modules/cms/templates').reply(200, {
                "data": [
                    {
                        "id": 3,
                        "name": "Landings Page",
                        "folder": {
                            "id": 2,
                            "name": "Test",
                            "description": null,
                            "parent_id": null,
                            "folder_id": null,
                            "child": []
                        },
                        "type": null,
                        "locked": false,
                        "static": false
                    },
                    {
                        "id": 4,
                        "name": "Nog een page",
                        "folder": null,
                        "type": null,
                        "locked": false,
                        "static": false
                    }
                ],
                "status": 200,
                "message": null
            })
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
            })
    },
    components: { ResourceFormModal },
    template: `<div id="__layout"><div><ResourceFormModal v-bind="$props" v-on="$listeners" :key="${args.pleaseMockMeDaddy}"/></div></div>`,
})

export const Default = Story.bind({})
Default.parameters = {
    permissions: ['provider-templates-create']
}

export const NoResource = Story.bind({})
NoResource.args = {
    ...Default.args,
    forceNoResources: true
}
NoResource.parameters = {
    permissions: ['provider-templates-create']
}

export const NoTemplate = Story.bind({})
NoTemplate.args = {
    ...Default.args,
    forceNoTemplates: true,
}
NoTemplate.parameters = {
    permissions: ['provider-templates-create']
}

export const NoTemplateAndNoPermissions = Story.bind({})
NoTemplateAndNoPermissions.args = {
    ...NoTemplate.args,
}
NoTemplateAndNoPermissions.parameters = {
    permissions: []
}