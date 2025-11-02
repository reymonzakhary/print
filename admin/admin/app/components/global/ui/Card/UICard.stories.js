import UICard from './UICard.vue'

export default {
    title: 'Global/UI/Card/Card',
    component: UICard
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UICard },
    template: `
        <article>
            <UICardHeader v-if="${args.header === true}"/>
            <UICard v-bind="$props" v-on="$listeners">
                <template v-if="${'default' in args}" v-slot>${args.default}</template>
            </UICard>
        </article>
    `,
});

export const WithHeader = Story.bind({});
WithHeader.args = {
    header: true,
    default: "<div class='p-2'>This is a card with a padding and a header</div>",
};

export const WithoutHeader = Story.bind({});
WithoutHeader.args = {
    default: "<div class='p-2'>This is a card with a padding and no header</div>",
};

export const Default = Story.bind({});
Default.args = {
    default: "This is a default card with no padding and no header",
};