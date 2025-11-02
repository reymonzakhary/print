import CMSHeader from './CMSHeader.vue';

export default {
    title: 'CMS/Molecules/CMSHeader',
    component: CMSHeader,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { CMSHeader },
    template: `<CMSHeader v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    // props
};