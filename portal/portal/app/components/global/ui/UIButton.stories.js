import UIButton from './UIButton.vue'
import UICardHeader from './Card/UICardHeader.vue'

export default {
    title: 'Global/UI/UIButton',
    component: UIButton,
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
    components: { UIButton, UICardHeader },
    template: `
        <UIButton v-bind="$props" v-on="$listeners">
            <template v-if="${'default' in args}" v-slot>${args.default}</template>
        </UIButton>
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
