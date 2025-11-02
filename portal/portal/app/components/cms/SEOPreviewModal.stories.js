import SEOPreviewModal from './SEOPreviewModal.vue';

export default {
    title: 'Modals/Resources/SEOPreviewModal',
    component: SEOPreviewModal,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { SEOPreviewModal },
    template: `<SEOPreviewModal v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    // props
};