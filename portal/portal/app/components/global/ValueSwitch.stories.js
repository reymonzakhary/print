import ValueSwitch from './ValueSwitch.vue'

export default {
    component: ValueSwitch,
    title: 'global/ValueSwitch',
}

const Template = (args, { argTypes }) => ({
    components: { ValueSwitch },
    props: Object.keys(argTypes),
    template: '<ValueSwitch v-bind="$props" v-on="$listeners" />',
})

export const Default = Template.bind({})