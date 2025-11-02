import UICardHeaderTab from './UICardHeaderTab.vue';

export default {
    title: 'Global/ui/Card/UICardHeaderTab',
    component: UICardHeaderTab,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UICardHeaderTab },
    template: `
    <UICardHeader class="overflow-hidden" style="width: 500px;">
        <template #right>
            <div class="flex">
                <UICardHeaderTab v-bind="$props" v-on="$listeners" />
            </div>
        </template>
    </UICardHeader>
    `,
});

export const Default = Story.bind({});
Default.args = {
    active: true,
    label: 'Tab 1',
};

export const Inactive = Story.bind({});
Inactive.args = {
    active: false,
    label: 'Tab 1',
};

export const WithLongLabel = Story.bind({});
WithLongLabel.args = {
    active: true,
    label: 'Tab 1 with a really long label',
};