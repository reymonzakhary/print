import ResourceGroupsModal from './ResourceGroupsModal.vue';

export default {
    title: 'Modals/Resources/ResourceGroupsModal',
    component: ResourceGroupsModal,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { ResourceGroupsModal },
    template: `<div id="__layout"><div><ResourceGroupsModal v-bind="$props" v-on="$listeners" /></div></div>`,
});

export const Default = Story.bind({});
Default.args = {
    // props
};