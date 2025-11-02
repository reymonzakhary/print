import MachineColors from './MachineColors.vue';

export default {
    title: 'MachineColors',
    component: MachineColors,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { MachineColors },
    template: `<MachineColors v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    // props
};