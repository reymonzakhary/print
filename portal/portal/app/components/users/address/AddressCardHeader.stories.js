import Vue from 'vue';
import { mockPermissionsDecorator, mockedPermissionsMixin } from '../../../.storybook/mockPermissions.js';

import AddressCardHeader from './AddressCardHeader.vue';

Vue.use(mockedPermissionsMixin);

export default {
    title: 'Users/Molecules/AddressCardHeader',
    component: AddressCardHeader,
    decorators: [mockPermissionsDecorator],
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { AddressCardHeader },
    template: '<address-card-header v-bind="$props" />',
});

export const Default = Story.bind({});
Default.args = {
    type: "Home"
}
Default.parameters = {
    permissions: ['users-addresses-update', 'users-addresses-delete']
}

export const NoPermissions = Story.bind({});
NoPermissions.args = {
    ...Default.args,
}
NoPermissions.parameters = {
    permissions: []
}