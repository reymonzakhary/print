import ActionBarSectionTitle from './ActionBarSectionTitle.vue';

export default {
    title: 'CMS/Molecules/ActionBarSectionTitle',
    component: ActionBarSectionTitle,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { ActionBarSectionTitle },
    template: `<ActionBarSectionTitle v-bind="$props" v-on="$listeners">Title</ActionBarSectionTitle>`,
});

export const Default = Story.bind({});
Default.args = {
    icon: ['fal', 'window-maximize']
};