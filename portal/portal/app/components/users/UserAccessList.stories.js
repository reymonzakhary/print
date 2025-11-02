import UserAccessList from './UserAccessList.vue';

export default {
    title: 'Users/Molecules/UserAccessList',
    component: UserAccessList,
    argTypes: {
        "titleIcon": {
            control: {
                type: "array",
            },
        },
        "itemsIcon": {
            control: {
                type: "array",
            },
        },
        "items": {
            control: {
                type: "array",
            },
        },
    }
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { UserAccessList },
    template: `<UserAccessList v-bind="$props" v-on="$listeners" />`,
});

export const Default = Story.bind({});
Default.args = {
    titleIcon: ['fal', 'user-tag'],
    itemsIcon: ['fal', 'check'],
    items: [
        { name: 'users-profiles-read' },
        { name: 'users-profiles-update' },
        { name: 'users-profiles-delete' },
        { name: 'users-profiles-create' },
    ],
    title: 'Permissions',
}

export const NoItems = Story.bind({});
NoItems.args = {
    ...Default.args,
    items: [],
}

export const NoTitleIcon = Story.bind({});
NoTitleIcon.args = {
    ...Default.args,
    titleIcon: null,
}

export const NoItemsIcon = Story.bind({});
NoItemsIcon.args = {
    ...Default.args,
    itemsIcon: null,
}

export const Loading = Story.bind({});
Loading.args = {
    ...Default.args,
    isLoading: true,
}