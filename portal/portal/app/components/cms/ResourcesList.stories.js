import ResourcesList from './ResourcesList.vue';

export default {
    title: 'CMS/Organisms/ResourcesList',
    component: ResourcesList,
}

const Story = (args, { argTypes }) => ({
    props: Object.keys(argTypes),
    components: { ResourcesList },
    template: `
        <div class="w-[400px] mx-auto">
            <ResourcesList v-bind="$props" v-on="$listeners" />
        </div>
    `,
});

export const Default = Story.bind({});
Default.args = {
    resources: [{"id":33,"title":"Child child Services 3","folder":false,"resources":[]},{"id":29,"title":"Root Folder","folder":true,"resources":[{"id":97,"title":"Double Nested Resource","folder":false,"resources":[]},{"id":98,"title":"Nested Folder","folder":true,"resources":[{"id":99,"title":"Nested Resource","folder":false,"resources":[]},{"id":13,"title":"Landings Page - Gent","folder":false,"resources":[]}]}]},{"id":21,"title":"New Resource","folder":false,"resources":[]},{"id":17,"title":"Landings Page - Gemeente Gent","folder":false,"resources":[]}],
}

export const Selected = Story.bind({});
Selected.args = {
    ...Default.args,
    selectedResource: 98    ,
}

export const Reordering = Story.bind({});
Reordering.args = {
    ...Default.args,
    reordering: true,
}

export const isBin = Story.bind({});
isBin.args = {
    ...Default.args,
    isBin: true,
}

export const isLoading = Story.bind({});
isLoading.args = {
    ...Default.args,
    isLoading: true,
}