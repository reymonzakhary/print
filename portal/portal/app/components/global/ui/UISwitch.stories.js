import UISwitch from './UISwitch.vue';

export default {
    title: 'Global/ui/UISwitch',
    component: UISwitch,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UISwitch },
    template: `<UISwitch v-bind="$props" v-on="$listeners" />`,
})

export const Unchecked = Story.bind({});
Unchecked.args = {
    value: false
}

export const Checked = Story.bind({});
Checked.args = {
    value: true
}

export const CheckedSuccess = Story.bind({});
CheckedSuccess.args = {
    value: true,
    variant: 'success'
}

export const Disabled = Story.bind({});
Disabled.args = {
    value: false,
    disabled: true
}

export const DisabledChecked = Story.bind({});
DisabledChecked.args = {
    value: true,
    disabled: true
}