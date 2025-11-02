import MockAxiosAdapter from 'axios-mock-adapter';
import UICountrySelector from './UICountrySelector.vue';

export default {
    name: 'Global/UI/CountrySelector',
    component: UICountrySelector,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
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
    },
    components: { UICountrySelector },
    template: `<UICountrySelector v-bind="$props" v-on="$listeners" />`,
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