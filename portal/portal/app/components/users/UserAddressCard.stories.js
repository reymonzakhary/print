import Vue from 'vue';
import { mockPermissionsDecorator, mockedPermissionsMixin } from '../../.storybook/mockPermissions.js';

import UserAddressCard from './UserAddressCard.vue';

Vue.use(mockedPermissionsMixin);

export default {
    title: 'Users/Molecules/UserAddressCard',
    component: UserAddressCard,
    decorators: [mockPermissionsDecorator],
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UserAddressCard },
    template: '<user-address-card v-bind="$props" v-on="$listeners" />',
});

export const Default = Story.bind({});
Default.args = {
    address: {
        "id": 6,
        "address": "Hendrik Figeeweg",
        "number": "M1",
        "city": "Haarlem",
        "region": "Noord-Holland",
        "zip_code": "1203LK",
        "default": false,
        "country": {
            "id": 1,
            "name": "AFGHANISTAN",
            "iso2": "AF",
            "iso3": "AFG",
            "un_code": 4,
            "dial_code": "93"
        },
        "type": "home",
        "full_name": "Mellie Ibrihima",
        "company_name": "No Company",
        "phone_number": "000000000",
        "tax_nr": "000000000",
        "lat": null,
        "lng": null,
        "created_at": "2023-10-16T08:49:43.000000Z",
        "updated_at": "2023-10-16T08:49:43.000000Z"
    }
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

export const NoType = Story.bind({});
NoType.args = {
    ...Default.args,
    address: {
        ...Default.args.address,
        "type": null
    }
}
NoType.parameters = {
    ...Default.parameters
}