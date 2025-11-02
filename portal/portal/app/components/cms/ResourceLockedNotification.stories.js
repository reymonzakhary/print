import ResourceLockedNotification from './ResourceLockedNotification.vue';

export default {
    title: 'CMS/Organisms/ResourceLockedNotification',
    component: ResourceLockedNotification,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { ResourceLockedNotification },
    template: `<ResourceLockedNotification v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    // props
};