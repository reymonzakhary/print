import ActionBarSection from './ActionBarSection.vue';

export default {
    title: 'CMS/Molecules/ActionBarSection',
    component: ActionBarSection,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { ActionBarSection },
    template: `<ActionBarSection v-bind="$props" v-on="$listeners">Inside of Section</ActionBarSection>`,
});

export const Default = Story.bind({});
Default.args = {
    // props
};