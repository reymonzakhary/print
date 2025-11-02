import ResourcesListItem from './ResourcesListItem.vue'

export default {
    title: 'CMS/Molecules/ResourcesListItem',
    component: ResourcesListItem,
};

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { ResourcesListItem },
    template: `
        <div class="w-[400px] mx-auto">
            <ResourcesListItem v-bind="$props" v-on="$listeners" />
        </div>
    `,
});

export const Default = Story.bind({})
Default.args = {
    resources: [
        {
            id: '1',
            title: 'My Resources',
        }
    ],
    root: true,
}

export const Longtitle = Story.bind({})
Longtitle.args = {
    ...Default.args,
    resources: [
        {
            id: '1',
            title: 'My Resources with a really long title that will truncate',
        }
    ],
}

export const Selected = Story.bind({})
Selected.args = {
    ...Default.args,
    selectedResource: 1,
}

export const Draggable = Story.bind({})
Draggable.args = {
    ...Default.args,
    draggable: true,
}

export const isBinItem = Story.bind({})
isBinItem.args = {
    ...Default.args,
    isBinItem: true,
}

export const hasChildren = Story.bind({})
hasChildren.args = {
    ...Default.args,
    resources: [
        {
            id: 95,
            title: "Resource One",
            resources: [
                {
                    id: 65,
                    title: "Nested Nested Resource Two",
                    resources: [
                        {
                            id: 69,
                            title: "Nested Nested Resource Three",
                        },
                    ]
                },      
                {
                    id: 64,
                    title: "Nested Nested Resource Four",
                },
            ]
        },
    ],
    draggable: true
}

export const withIdsShown = Story.bind({})
withIdsShown.args = {
    ...hasChildren.args,
    showResourceIDs: true,
}