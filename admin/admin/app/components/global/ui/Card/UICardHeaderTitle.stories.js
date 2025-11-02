import UICardHeaderTitle from './UICardHeaderTitle.vue';

export default {
    title: 'Global/UI/Card/HeaderTitle',
    component: UICardHeaderTitle,
    argTypes: {
        title: {
            control: 'text',
            description: 'The title of the card header',
        },
        icon: {
            control: 'array',
            description: 'Icon that should be displayed next to the card header',
        }
    }
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UICardHeaderTitle },
    template: `
        <UICardHeaderTitle v-bind="$props" v-on="$listeners" />
    `,
});

export const WithIcon = Story.bind({});
WithIcon.args = {
    title: 'Prindustry',
    icon: ['fal', 'users']
};

export const WithoutIcon = Story.bind({});
WithoutIcon.args = {
    ...WithIcon.args,
    icon: [],
};