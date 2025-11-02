import AddressList from './AddressList.vue';

export default {
    title: 'Users/Organisms/AddressList',
    component: AddressList,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { AddressList },
    template: '<address-list v-bind="$props" v-on="$listeners" />',
})

export const Default = Story.bind({});
Default.args = {
    user: {
        id: 1,
        profile: {
            first_name: "John",
            last_name: "Doe",
        }
    },
    addresses: [
        {
            "id": 1,
            "type": "home",
            "address": "Hendrik Figeeweg",
            "city": "Haarlem",
            "region": "Noord-Holland",
            "zip_code": "2031 BJ",
            "default": false,
            "full_name": null,
            "company_name": null,
            "phone_number": null,
            "tax_nr": null,
            "lat": null,
            "lng": null,
            "created_at": "2023-10-12T12:45:01.000000Z",
            "updated_at": "2023-10-12T12:45:01.000000Z"
        },
        {
            "id": 2,
            "type": "home",
            "address": "Second Street",
            "city": "Amsterdam",
            "region": "Noord-Holland",
            "zip_code": "2031 BJ",
            "default": false,
            "full_name": null,
            "company_name": null,
            "phone_number": null,
            "tax_nr": null,
            "lat": null,
            "lng": null,
            "created_at": "2023-10-12T12:45:01.000000Z",
            "updated_at": "2023-10-12T12:45:01.000000Z"
        },
        {
            "id": 3,
            "type": "home",
            "address": "Third Avenue",
            "city": "Rotterdam",
            "region": "Zuid-Holland",
            "zip_code": "2031 BJ",
            "default": false,
            "full_name": null,
            "company_name": null,
            "phone_number": null,
            "tax_nr": null,
            "lat": null,
            "lng": null,
            "created_at": "2023-10-12T12:45:01.000000Z",
            "updated_at": "2023-10-12T12:45:01.000000Z"
        },
        {
            "id": 4,
            "type": "home",
            "address": "Fourth Boulevard",
            "city": "Utrecht",
            "region": "Utrecht",
            "zip_code": "2031 BJ",
            "default": false,
            "full_name": null,
            "company_name": null,
            "phone_number": null,
            "tax_nr": null,
            "lat": null,
            "lng": null,
            "created_at": "2023-10-12T12:45:01.000000Z",
            "updated_at": "2023-10-12T12:45:01.000000Z"
        },
    ]
}

export const isEmpty = Story.bind({});
isEmpty.args = {
    user: {
        id: 1,
        profile: {
            first_name: "John",
            last_name: "Doe",
        }
    },
    addresses: []
}

export const isLoading = Story.bind({});
isLoading.args = {
    user: {
        id: 1,
        profile: {
            first_name: "John",
            last_name: "Doe",
        }
    },
    addresses: [],
    isLoading: true,
}