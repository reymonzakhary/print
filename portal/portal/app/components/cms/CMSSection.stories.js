import CMSSection from './CMSSection.vue';

export default {
    title: 'CMS/Molecules/CMSSection',
    component: CMSSection,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { CMSSection },
    template: `<CMSSection v-bind="$props" v-on="$listeners">
                    <template v-if="${'default' in args}" #default>${args.default}</template>
               </CMSSection>
                `,
});

export const Default = Story.bind({});
Default.args = {
    title: 'Default Section',
    default: `
    <div class="grid grid-cols-[1fr_1fr_2fr] gap-4">
        <CMSSectionCard>
            This title is used in the manager
            <UIInputText />
        </CMSSectionCard>

        <CMSSectionCard>
            This title is used in the manager
            <UIInputText />
        </CMSSectionCard>

        <CMSSectionCard>
            This title is used in the manager
            <UIInputText />
        </CMSSectionCard>
    </div>
    `
};

export const Empty = Story.bind({});
Empty.args = {
    title: 'Empty Section',
};