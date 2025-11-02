import UICardHeaderButton from './UICardHeaderButton.vue'
import UICardHeader from './UICardHeader.vue'

export default {
    title: 'Global/UI/Card/HeaderButton',
    component: UICardHeaderButton,
    argTypes: {
        variant: {
            control: 'select',
            options: ['default', 'neutral', 'success', 'danger',]
        },
        icon: {
            control: 'array',
            description: 'Font Awesome icon name',
            defaultValue: ['fas', 'plus']
        }
    }
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UICardHeaderButton, UICardHeader },
    template: `
        <UICardHeaderButton v-bind="$props" v-on="$listeners">
            <template v-if="${'default' in args}" v-slot>${args.default}</template>
        </UICardHeaderButton>
    `,
});

export const WithIcon = Story.bind({});
WithIcon.args = {
    icon: ['fas', 'plus'],
    variant: 'default',
    disabled: false,
    default: 'Button'
};

export const WithoutIcon = Story.bind({});
WithoutIcon.args = {
    ...WithIcon.args,
    icon: [],
};

export const OnlyIcon = Story.bind({});
OnlyIcon.args = {
    icon: ['fas', 'plus'],
    variant: 'default',
    disabled: false,
};
