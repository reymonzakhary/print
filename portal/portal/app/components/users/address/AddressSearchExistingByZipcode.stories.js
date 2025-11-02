import MockAxiosAdapter from 'axios-mock-adapter';
import AddressSearchExistingByZipcode from './AddressSearchExistingByZipcode.vue';

export default {
    title: 'Users/Molecules/AddressSearchExistingByZipcode',
    component: AddressSearchExistingByZipcode,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { AddressSearchExistingByZipcode },
    template: '<div style="width: 500px;"><address-search-existing-by-zipcode v-bind="$props" /></div>',
    created() {
        const mockAxios = new MockAxiosAdapter(this.$axios);

        let data = {};

        if (this.zipCode === '0000AA') {
            data = {
                data: []
            }
        } else {
            data = {
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
                    },
                    {
                        "id": 2,
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
                    },
                    {
                        "id": 3,
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
                    },
                    {
                        "id": 4,
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
            }
        }

        mockAxios.onPost('countries/1/addresses/search').reply(200, data);
    }
});

export const Default = Story.bind({});
Default.args = {
    countryId: "1",
    zipCode: '1203LK',
}

export const NoResults = Story.bind({});
NoResults.args = {
    ...Default.args,
    zipCode: '0000AA',
}