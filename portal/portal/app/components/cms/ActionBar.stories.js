import ActionBar from './ActionBar.vue';

export default {
    title: 'CMS/Organisms/ActionBar',
    component: ActionBar,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { ActionBar },
    template: `<div class="w-96"><ActionBar v-bind="$props" v-on="$listeners" /></div>`,
});

export const Default = Story.bind({});
Default.args = {
    // props
};