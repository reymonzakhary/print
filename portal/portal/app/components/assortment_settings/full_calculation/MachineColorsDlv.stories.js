import MachineColorsDlv from './MachineColorsDlv.vue';

export default {
    title: 'MachineColorsDlv',
    component: MachineColorsDlv,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { MachineColorsDlv },
    template: `<MachineColorsDlv v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    // props
};