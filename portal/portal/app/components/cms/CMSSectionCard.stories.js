import CMSSectionCard from './CMSSectionCard.vue';

export default {
    title: 'CMS/Molecules/CMSSectionCard',
    component: CMSSectionCard,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { CMSSectionCard },
    template: `<CMSSectionCard v-bind="$props" v-on="$listeners">
                   <template v-if="${'default' in args}" #default>${args.default}</template>
               </CMSSectionCard>`,
});

export const Default = Story.bind({});
Default.args = {
    default: `This title is used in the manager <UIInputText />`,
};