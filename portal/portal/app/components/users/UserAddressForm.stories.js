import MockAxiosAdapter from 'axios-mock-adapter';
import UserAddressForm from './UserAddressForm.vue';

export default {
    title: 'Users/Organisms/UserAddressForm',
    component: UserAddressForm,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UserAddressForm },
    template: '<user-address-form v-bind="$props" v-on="$listeners" />',
    created() {
        const mockAxios = new MockAxiosAdapter(this.$axios);
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

        mockAxios.onPost('countries/1/addresses/search').reply(200, {
            data: [
                {
                    "id": 1,
                    "address": "Hendrik Figeeweg",
                    "city": "Haarlem",
                    "region": "Noord-Holland",
                    "zip_code": "2031 BJ",
                    "default": false,
                    "type": null,
                    "full_name": null,
                    "company_name": null,
                    "phone_number": null,
                    "tax_nr": null,
                    "lat": null,
                    "lng": null,
                    "created_at": "2023-10-12T12:45:01.000000Z",
                    "updated_at": "2023-10-12T12:45:01.000000Z"
                }
            ]
        });
    },
});

export const Default = Story.bind({});
Default.args = {}

export const isEditing = Story.bind({});
isEditing.args = {
    address: {
        id: 1,
        type: "home",
        country: {
            id: 1,
        },
        full_name: "John Doe",
        isBusinessUser: true,
        company_name: "My Company",
        tax_nr: "VAT098765",
        phone_number: "0123456789",
        zip_code: "1234XK",
        number: "21",
        address: "Hendrik Figeeweg",
        city: "Haarlem",
        region: "Noord-Holland",
    }
}