import SEOSectionTitle from './SEOSectionTitle.vue';

export default {
    title: 'CMS/SEO/SEOSectionTitle',
    component: SEOSectionTitle,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { SEOSectionTitle },
    template: `<SEOSectionTitle v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    title: 'Google',
    icon: ['fab', 'google'],
};